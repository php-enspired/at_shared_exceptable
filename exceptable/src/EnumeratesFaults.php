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

use Override;
use at\exceptable\_ThrowableContext;
use at\peekaboo\MessageEnum;

/** Implements faults as enum cases and messages as their backing values. */
trait EnumeratesFaults {
  use IsFault, MessageEnum {
    MessageEnum::message as _message;
    IsFault::makeMessage insteadof MessageEnum;
  }

  #[Override]
  public static function from(string $name) : static {
    // false positive: we check the return type
    // @phan-suppress-next-line PhanTypeMismatchReturn
    return static::tryFrom($name) ??
      throw (ExceptableFault::UnknownFault)(["name" => $name]);
  }

  #[Override]
  public static function tryFrom(string $name) : ? static {
    return (defined($name) && ($fault = constant($name)) instanceof static) ?
      // false positive: we check the return type
      // @phan-suppress-next-line PhanTypeMismatchReturn
      $fault :
      null;
  }

  #[Override]
  public function message(array $context = []) : string {
    $error = $this->name();
    $message = $this->_message(_ThrowableContext::addThrowableContext($context));
    return is_string($message) ? "{$error}: {$message}" : $error;
  }
}
