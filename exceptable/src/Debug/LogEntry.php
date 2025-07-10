<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2025
 * @license    MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */
declare(strict_types = 1);

namespace at\exceptable\Debug;

use Throwable;

use at\exceptable\ {
  Debug\DebugLogEntry,
  Debug\ErrorLogEntry,
  Debug\FaultLogEntry,
  Debug\ThrowableLogEntry,
  ExceptableFault,
  Fault
};
use at\peekaboo\EnumeratesMessages;
use at\peekaboo\MessageRegistry;

/** Base class for error log entries. */
abstract class LogEntry {

  /**
   * Factory: dispatches to an appropriate `from*()` method.
   *
   * Note this method accepts _any_ value of `$from`, but valid log entries will only be produced from:
   * - `Fault`
   * - `Throwable`
   * - `string` or `EnumeratesMessages` enum
   * - `array` (of error info) or `null`
   *
   * Other values will produce an `UnacceptableLogMessage` entry, and, importantly, will not throw.
   * This allows the mistake to be noticed without hiding whatever original problem was meant to be logged.
   */
  public static function from($from, array $context = []) : LogEntry {
    return match (true) {
      $from instanceof Fault => static::fromFault($from, $context),
      $from instanceof Throwable => static::fromThrowable($from, $context),
      $from instanceof EnumeratesMessages, is_string($from) =>  static::fromMessage($from, $context),
      is_array($from), $from == null => static::fromError($from, $context),
      default =>
        // normally we'd throw but since our job is to log we'll not bury the original problem
        static::fromFault(
          ExceptableFault::UnacceptableLogMessage,
          ["type" => get_debug_type($from), "from" => $from, "context" => $context]
        )
    };
  }

  /**
   * Factory: builds a log entry from an array of error details (as returned by `error_get_last()`).
   *
   * Omit `$error` to build an entry from `error_get_last()`.
   */
  public static function fromError(? array $error = null, array $context = []) : ErrorLogEntry {
    if (empty($error)) {
      $error = error_get_last();
    }
    return new ErrorLogEntry(
      code: $error["type"] ?? 0,
      file: $error["file"] ?? $context["__file__"] ?? null,
      line: $error["line"] ?? $context["__line__"] ?? null,
      message: $error["message"] ??
        ExceptableFault::UnknownError->message(["error_get_last" => error_get_last()], $error + $context),
      context: $context,
      handled: $context["__handled__"] ?? false
    );
  }

  /** Factory: builds a log entry from a Fault. */
  public static function fromFault(Fault $fault, array $context) : FaultLogEntry {
    return new FaultLogEntry(
      fault: $fault,
      file: $context["__file__"] ?? null,
      line: $context["__line__"] ?? null,
      message: $fault->message($context),
      context: $context,
      handled: $context["__handled__"] ?? false
    );
  }

  /** Factory: builds a log entry from a message. */
  public static function fromMessage(EnumeratesMessages | string $message, array $context) : DebugLogEntry {
    return new DebugLogEntry(
      file: $context["__file__"] ?? null,
      line: $context["__line__"] ?? null,
      message: ($message instanceof EnumeratesMessages) ?
        $message->message($context) :
        MessageRegistry::formatMessage($message, $context),
      context: $context,
      handled: $context["__handled__"] ?? false
    );
  }

  /** Factory: builds a log entry from an exception. */
  public static function fromThrowable(Throwable $e, array $context) : ThrowableLogEntry {
    return new ThrowableLogEntry(
      exception: $e,
      file: $e->getFile(),
      line: $e->getLine(),
      message: $e->getMessage(),
      context: $context,
      handled: $context["__handled__"] ?? false
    );
  }

  /** Contextual information for the log entry. */
  public readonly array $context;

  /** Microtime the log entry was created. */
  public readonly float $time;

  public function __construct(
    public readonly ? string $file = null,
    public readonly ? int $line = null,
    public readonly ? string $message = null,
    array $context = [],
    public readonly bool $handled = false
  ) {
    $this->time = microtime(true);
    $this->context = $this->augmentContext($context);
  }

  /**
   * Casts log entry to array (for use with PSR-3)
   *
   * @return array
   */
  public function toArray() : array {
    return (array) $this;
  }

  /** Adds relevent details to context so they are available to the logger. */
  protected function augmentContext(array $context) : array {
    $context = [
      "__file__" => $this->file,
      "__line__" => $this->line,
      "__time__" => $this->time
    ] + $context;
    ksort($context);
    return $context;
  }
}
