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

use at\exceptable\ {
  EnumeratesFaults,
  Fault,
  Spl\InvalidArgumentException,
  Spl\LogicException,
  Spl\RuntimeException
};

/** Faults for internal usage. */
enum ExceptableFault : string implements Fault {
  use EnumeratesFaults;

  case UnknownFault = "{__rootMessage__}";
  // false positive: Fault
  // @phan-suppress-next-line PhanUndeclaredClassReference
  case UnacceptableFault = "Invalid Fault type ''{type}'' (expected implementation of " . Fault::class . ")";
  case UncaughtException = "Uncaught Exception ({__rootType__}): {__rootMessage__}";
  case UnacceptableLogMessage = "Invalid log entry message: {type} ({from})";
  case UnknownError = "Unknown error (message is missing). Last error: {error_get_last}";

  /** @see Error::exceptable() */
  public function exceptableType() : string {
    return match ($this) {
      self::UnacceptableFault, self::UnknownFault => LogicException::class,
      self::UncaughtException => RuntimeException::class,
      self::UnacceptableLogMessage, self::UnknownError => InvalidArgumentException::class
    };
  }
}
