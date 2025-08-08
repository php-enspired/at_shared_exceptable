<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2024
 * @license    MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */
declare(strict_types = 1);
namespace at\exceptable\Handler;

use Closure,
  Throwable;
use at\exceptable\ {
  ExceptableFault,
  Debug\DebugLog,
  Handler\Options,
  Fault
};
use at\peekaboo\HasMessages;

/** Default Handler Options instance. */
const DefaultOptions = new Options();

/**
 * For building reusable error handling strategies.
 *
 * Handlers are _immutable_ - when you call any of the configuring methods (`collect()`, `ignore()`, etc.),
 *  a new instance is returned.
 * If you are not familiar with immutable objects, be aware that things might not work as you expect.
 * Remember to operate on/assign the return value, not on the original object var:
 * ```php
 * $h = $h->ignore(. . .); $h->try(. . .);
 * // or
 * $h->ignore(. . .)->try(. . .);
 * ```
 *  and not
 * ```php
 * $h->ignore(. . .);
 * $h->try(. . .);
 * // try() as you might, $h doesn't ignore
 * ```
 *
 * The code to handle is provided as a callable to `try()`:
 * - if `options->throw` is configured, registered, unsilenced error types are converted to `ErrorException`.
 * - if the callable throws or returns a Fault, the Handler's registered strategies are tried in order:
 *   - (re)throws according to types registered with `throw()`
 *   - converts to Fault according to types registered with `collect()`, in the order they were registered
 *   - converts to `null` according to types registered with `ignore()`, in the order they were registered
 *   - converts to default values according to types registered with `default()`
 *   - converts unhandled exceptions to `ExceptableFault::UncaughtException`
 * - "successful" (not a Fault) values are passed to any `onSuccess()` handlers, in the order they were registered.
 * - "failure" (Fault) values are passed to any `onFailure()` handlers, in ther order they were registered.
 *
 * `tryIgnoring()` works the same way, but converts unhandled Faults and exceptions to `null` instead.
 */
class Handler {

  /** Log of debugging messages. */
  public readonly DebugLog $debugLog;

  private array $collect = []; // [{fault name} => [{fault to return}, ...{faults|throwables to collect}], . . .]
  private array $default = []; // [{fault name} | null => {value to return}]
  private array $ignore = []; // [...{faults|throwables to ignore}]
  private bool $ignoring = false;
  private ? Closure $onSuccess = null; // fn ($value) : mixed
  private ? Closure $onFailure = null; // fn (Fault $value) : mixed
  private array $retries = []; // [{retries} => [...{faults|throwables to retry}], . . .]
  private array $throw = []; // [...{faults|throwables to rethrow}]

  public function __clone() {
    $this->options = clone $this->options;
    $this->debugLog = clone $this->debugLog;
  }

  public function __construct( private Options $options = DefaultOptions ) {
    $this->debugLog = new DebugLog($this->options->debug, $this->options->logger);
  }

  /**
   * Binds a callback to this Handler's registered error strategies.
   *
   * Bound callables are intended for use with `Handler->tryPipe()` (or, in php 8.5+, with the `|>` operator).
   * The callback is invoked only if the input `$value` is not a Fault; otherise, the Fault is returned.
   */
  final public function bind(callable $if) : Closure {
    return function ($value) use ($if) {
      return ($value instanceof Fault) ? $value : $this->try($if, $value);
    };
  }

  /**
   * Specifies error cases this Handler should collect and return as the given Fault.
   *
   * @param Fault $as The Fault to collect error cases as
   * @param Fault|string ...$errorCases The Faults and/or fully qualified Throwable classnames to collect
   */
  final public function collect(Fault $as, Fault | string ...$errorCases) : static {
    $handler = clone $this;
    $index = $as->name();
    $handler->collect[$index] ??= [];
    array_push($handler->collect[$index], $as, ...$errorCases);
    return $handler;
  }

  /**
   * Specifies a default value this Handler should return instead of the given error cases
   *  (omit cases, or pass `null`, to specify a default for a null return value).
   */
  final public function default($to, Fault | string | null ...$errorCases) : static {
    $handler = clone $this;
    if (empty($errorCases)) {
      $errorCases[] = null;
    }
    foreach ($errorCases as $errorCase) {
      $handler->default[($errorCase instanceof Fault) ? $errorCase->name() : $errorCase] = $to;
    }
    return $handler;
  }

  /** Specifies error cases this Handler should ignore and return as null.
   *
   * @param Fault|string ...$errorCases The Faults and/or fully qualified Throwable classnames to collect
   */
  final public function ignore(Fault | string ...$errorCases) : static {
    $handler = clone $this;
    array_push($handler->ignore, ...$errorCases);
    return $handler;
  }

  /**
   * Logs a fault or exception to this handler, or any message in debug mode.
   *
   * An array passed as `$e` is expected to be an array of error info, such as is returned from `error_get_last()`.
   * Passing `null` will log the last triggered error, if any.
   */
  public function logIf(
    Fault | Throwable | HasMessages | array | string | null $e,
    array $context = []
  ) : void {
    if ($this->options->debug || ($this->options->logger && ($e instanceof Fault || $e instanceof Throwable))) {
      $this->debugLog->addFrom($e, $context);
    }
  }

  /**
   * Specifies a callback for processing return values on failure.
   *
   * @param callable $failure fn (Fault $value) : mixed
   */
  final public function onFailure(callable $failure) : static {
    $handler = clone $this;
    $handler->onFailure = ($failure instanceof Closure) ? $failure : $failure(...);
    return $handler;
  }

  /**
   * Specifies a callback for processing return values on success.
   *
   * @param callable $success fn ($value) : mixed
   */
  final public function onSuccess(callable $success) : static {
    $handler = clone $this;
    $handler->onSuccess = ($success instanceof Closure) ? $success : $success(...);
    return $handler;
  }

  /** Sets new options (omit any value(s) to inherit existing options). */
  final public function options(Options $options) : static {
    $handler = clone $this;
    $handler->options = $options;
    // intentional
    // @phan-suppress-next-line PhanTypeSuspiciousNonTraversableForeach
    foreach ($this->options as $option => $value) {
      if (! isset($handler->options->$option)) {
        $handler->options->$option = $value;
      }
    }
    $this->debugLog->debug = $this->options->debug;
    $this->debugLog->logger = $this->options->logger;
    return $handler;
  }

  /** Specifies the number of times processing should be retried on the given error cases. */
  final public function retry(int $attempts, Fault | string ...$errorCases) : static {
    $handler = clone $this;
    $handler->retries[$attempts] ??= [];
    array_push($handler->retries[$attempts], ...$errorCases);
    return $handler;
  }

  /**
   * Specifies error cases this Handler should (re)throw without modification.
   *
   * @param Fault|string ...$errorCases The Faults and/or fully qualified Throwable classnames to collect
   */
  final public function throw(Fault | string ...$errorCases) : static {
    $handler = clone $this;
    array_push($handler->throw, ...$errorCases);
    return $handler;
  }

  /**
   * Invokes the given callable using any registered error strategies.
   *
   * @throws Throwable if so configured and the callable throws
   * @return mixed|Fault The return value of the callback on success; a Fault (possibly null) on failure
   */
  final public function try(callable $c, ...$args) : mixed {
    $e = null;
    try {
      $result = $c(...$args);
    } catch (Throwable $e) {
      $result = ExceptableFault::UncaughtException;
    } finally {
      $context = ["tried" => $c, "args" => $args, "result" => $result];
      if (isset($e) || $result instanceof Fault) {
        $context["__exception__"] = $e;
        $context["__fault__"] = $result;
        $this->logIf($e ?? $result, $context);
        $this->rethrowIf($e ?? $result, $context);
        $result = $this->collectIf($result);
        if (isset($result) && $this->shouldIgnore($result)) {
          $this->logIf(DebugMessages::Ignoring, $context);
          $result = null;
        }
        $result = $this->defaultIf($result);
      }
      if ($result instanceof Fault) {
        return $this->doFailure($result);
      }
      return $this->doSuccess($result);
    }
  }

  /**
   * Invokes the given callable using any registered error strategies, ignoring any failures.
   *
   * @return mixed The return value of the callback on success; null on failure
   */
  final public function tryIgnoring(callable $c, ...$args) : mixed {
    try {
      $this->ignoring = true;
      $result = $this->try($c, ...$args);
      return ($result instanceof Fault) ? null : $result;
    } finally {
      $this->ignoring = false;
    }
  }

  /**
   * Invokes the given callables in turn, passing the return value of each as the argument for the next.
   *
   * If a Fault is encountered, the process is aborted.
   *
   * If desired, individual callable steps can be bound to specific Handlers via `Handler->bind()`.
   * The process as a whole is wrapped with this Handler's registered error strategies.
   */
  final public function tryPipe($initial, callable ...$functions) : mixed {
    // @todo
    return $this->try(
      function ($result) use ($functions) {
        foreach ($functions as $function) {
          if ($result instanceof Fault) {
            return $result;
          }
          $result = $function($result);
        }
        return $result;
      },
      $initial
    );
  }

  private function collectIf(Fault | Throwable $e) : ? Fault {
    $collected = null;
    foreach ($this->collect as $errorCases) {
      $collectAs = array_shift($errorCases);
      if (! $collectAs instanceof Fault) {
        throw (ExceptableFault::UnacceptableFault)(["type" => get_debug_type($collectAs)]);
      }
      foreach ($errorCases as $collectable) {
        $collectableIsFqcn = is_string($collectable);
        if ($collectableIsFqcn ? is_a($e, $collectable) : $e instanceof $collectable) {
          if (! isset($collected)) {
            $collected = $collectAs;
            $context = ["result" => $e, "fault" => $collected];
            $this->logIf(DebugMessages::Collected, $context);
          }
          if ($collectableIsFqcn ? get_class($e) === $collectable : $e === $collectable) {
            return $collected;
          }
        }
      }
    }
    return $collected;
  }

  private function defaultIf($result) {
    if ($result === null && isset($this->default[""])) {
      return $this->default[""];
    }
    if ($result instanceof Fault && isset($this->default[$result->name()])) {
      return $this->default[$result->name()];
    }
    return $result;
  }

  private function doFailure(Fault $result) {
    try {
      return isset($this->onFailure) ?
        ($this->onFailure)($result) :
        $result;
    } catch (Throwable $e) {
      throw (HandlerFault::FailureHandlerFailed)(["result" => $result], $e);
    }
  }

  private function doSuccess($result) {
    try {
      return isset($this->onSuccess) ?
        ($this->onSuccess)($result) :
        $result;
    } catch (Throwable $e) {
      throw (HandlerFault::SuccessHandlerFailed)(["result" => $result], $e);
    }
  }

  private function rethrowIf(Fault | Throwable $e, array $context) {
    foreach ($this->throw as $rethrowable) {
      if (is_string($rethrowable) ? is_a($e, $rethrowable) : $e instanceof $rethrowable) {
        throw ($e instanceof Fault) ? $e($context) : $e;
      }
    }
  }

  private function shouldIgnore(Fault | Throwable $e) : bool {
    if ($this->ignoring) {
      return true;
    }
    if ($e instanceof Fault) {
      foreach ($this->ignore as $ignorable) {
        if ($ignorable === $e) {
          return true;
        }
      }
    } else {
      foreach ($this->ignore as $ignorable) {
        if (is_string($ignorable) && is_a($e, $ignorable)) {
          return true;
        }
      }
    }
    return false;
  }
}
