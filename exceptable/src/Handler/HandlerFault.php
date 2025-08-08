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

use at\exceptable\ {
  EnumeratesFaults,
  Fault,
  Spl\LogicException,
  Spl\RuntimeException
};

/** Faults for Handlers. */
enum HandlerFault : string implements Fault {
  use EnumeratesFaults;

  case SuccessHandlerFailed = "onSuccess({result}) failed: {__rootMessage__}";
  case FailureHandlerFailed = "onFailure({result}) failed: {__rootMessage__}";

  /** @see Error::exceptable() */
  public function exceptableType() : string {
    return match ($this) {
      self::SuccessHandlerFailed, self::FailureHandlerFailed => LogicException::class,
      default => RuntimeException::class
    };
  }
}
