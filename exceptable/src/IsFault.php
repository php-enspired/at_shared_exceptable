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
namespace at\exceptable;

use Override,
  ResourceBundle,
  Throwable,
  UnitEnum;
use at\exceptable\ {
  Fault,
  Exceptable,
  Spl\RuntimeException
};
use at\peekaboo\MessageMapper;

/**
 * Default implementation for Fault classes.
 *
 * This trait may be used on an enum type, or on a regular class.
 * If used on a non-enum class, you MUST assign to `$this->name` on construct.
 *
 * The default `toExceptable()` method produces `RuntimeException` instances.
 * To change this, override `exceptableType()` to provide the desired classname.
 *
 * If you do not set up a message bundle (@see MakesMessages), Messages will include the Fault's name() only.
 *
 * The message key is assumed to be mapped from the Fault's fully qualified classname -
 *  e.g., the message for `your\example\Fault::SomeProblem` is expected to be `your.example.Fault.SomeProblem`.
 * If your messages are organized differently, you will need to override the `messageKey()` method.
 *
 * If you wish to declare faults as an enum with messages provided as backing values,
 *  use `EnumeratesFaults` instead.
 */
trait IsFault {
  use MessageMapper;

  /** @see UnitEnum->name */
  public readonly string $name;

  #[Override]
  public function __invoke(array $context = [], ? Throwable $previous = null) : Exceptable {
    return $this->toExceptable(_ThrowableContext::addThrowableContext($context, previous: $previous), $previous);
  }

  #[Override]
  final public function name() : string {
    return static::class . ".{$this->name}";
  }

  #[Override]
  public function message(array $context = []) : string {
    $error = $this->name();
    $message = $this->makeMessage($this->messageKey(), _ThrowableContext::addThrowableContext($context), onlyIf: true);
    return is_string($message) ? "{$error}: {$message}" : $error;
  }

  #[Override]
  public function toExceptable(array $context = [], ? Throwable $previous = null) : Exceptable {
    if (isset($previous)) {
      $context = _ThrowableContext::addThrowableContext($context, previous: $previous);
    }
    $x = $this->exceptableType();
    assert(is_a($x, Exceptable::class, true));

    return $this->adjustExceptable(new $x($this, $context, $previous));
  }

  /**
   * The concrete Exceptable type to throw this Fault as.
   *
   * This can/should be overridden by the implementing class to specify the most appropriate Exceptable type.
   * The type may be static, or determined dynamically (e.g., based on which Fault case it's being called on):
   * ```php
   * return match($this) {
   *   case MyFault::X -> MyXExceptable::class,
   *   case MyFault::Y -> MyYExceptable::class,
   *   . . .
   * }
   * ```
   *
   * Note, the value this method returns MUST be a valid, fully qualified classname that implements `Exceptable`.
   * While this _is_ checked via `assert()`, this is only a sanity check for convenience during development
   *  (as assertions can, and generally _should_, be disabled in production environments).
   */
  protected function exceptableType() : string {
    return RuntimeException::class;
  }

  /**
   * The key to look up this Fault's message.
   *
   * @return string A dot-delimited path for this Fault's message.
   */
  protected function messageKey() : string {
    return $this->name;
    //return strtr($this->name(), ["\\" => "_"]);
  }

  private function adjustExceptable(Exceptable $x) : Exceptable {
    (function () use ($x) {
      foreach ($x->getTrace() as $frame) {
        if ($frame["file"] !== __FILE__) {
          // @phan-suppress-next-line PhanUndeclaredProperty
          $x->file = $frame["file"];
          // @phan-suppress-next-line PhanUndeclaredProperty
          $x->line = $frame["line"];
          return;
        }
      }
    })->call($x, $x);

    return $x;
  }
}
