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

namespace at\exceptable\Spl;

use at\exceptable\ {
  IsFault,
  Fault,
  Spl\LogicException,
  Spl\RuntimeException
};

/** Faults for native php error types. */
enum ErrorFault : int implements Fault {
  use IsFault;

  case RecoverableError = E_RECOVERABLE_ERROR;
  case Warning = E_WARNING;
  case Notice = E_NOTICE;
  case Strict = E_STRICT;
  case Deprecated = E_DEPRECATED;
  case UserError = E_USER_ERROR;
  case UserWarning = E_USER_WARNING;
  case UserNotice = E_USER_NOTICE;
  case UserDeprecated = E_USER_DEPRECATED;

  /** @see Error::exceptable() */
  public function exceptableType() : string {
    return match ($this) {
      self::Warning, self::Notice, self::UserError, self::UserWarning, self::UserNotice => RuntimeException::class,
      self::Strict, self::RecoverableError, self::Deprecated, self::UserDeprecated => LogicException::class
    };
  }
}
