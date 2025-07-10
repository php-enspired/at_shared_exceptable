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

namespace at\exceptable\Spl;

use BadFunctionCallException as SplBadFunctionCallException;

use at\exceptable\ {
  Exceptable,
  IsExceptable,
  Spl\SplFault
};

/**
 * Exceptable implementation of Spl's BadFunctionCallException.
 * @see https://php.net/BadFunctionCallException
 */
class BadFunctionCallException extends SplBadFunctionCallException implements Exceptable {
  use IsExceptable;

  public const DEFAULT_ERROR = SplFault::BadFunctionCall;
}
