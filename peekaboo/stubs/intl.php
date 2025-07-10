<?php
/**
 * @package    at.peekaboo
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2023
 * @license    MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */
declare(strict_types = 1);

// if intl is loaded, these are unneeded.
if (extension_loaded("intl")) {
  return;
}

use at\peekaboo\MessageFormatter as PeekabooMessageFormatter;
require_once __DIR__ . "/MessageFormatter.php";

/**
 * @internal
 * @see https://php.net/ResourceBundle
 *
 * This is a stub/fallback for internal usage when ext/intl is not loaded.
 * ResourceBundle->getErrorCode() and ->getErrorMessage() always tell you "no error."
 * Other methods are not emulated.
 *
 * Only defined if ext/intl is not loaded.
 * @phan-suppress PhanRedefineClassInternal
 */
abstract class ResourceBundle {

  public function getErrorCode() : int {
    return 0;
  }

  public function getErrorMessage() : string {
    return "U_ZERO_ERROR";
  }

  abstract public function count() : int;
  abstract public function get($key, bool $fallback = true) : mixed;
}

/**
 * @internal
 *
 * This aliases our stub/fallback for internal usage when ext/intl is not loaded.
 *
 * @phan-suppress PhanAccessClassInternal
 * @phan-suppress PhanRedefineClassInternal
 */
class MessageFormatter extends PeekabooMessageFormatter {}
