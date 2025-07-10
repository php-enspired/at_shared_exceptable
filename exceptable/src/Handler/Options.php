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

namespace at\exceptable\Handler;

use Psr\Log\LoggerInterface as Logger;

/** Options for the Handler class. */
class Options {

  /** @var bool Enable debug mode? */
  public bool $debug = false;

  /** @var Logger Default logger instance to use. */
  public ? Logger $logger = null;

  /** @var bool Ignore the error control operator? */
  public bool $scream = false;

  /**
   * @var int Error types (disjunction of `E_*` constants) which should be thrown as ErrorExceptions.
   *  Use `-1` to throw all error types; or `0` to throw none.
   */
  public int $throwErrorExceptions = 0;

  /**
   * @var int Error types (disjunction of `E_*` constants) which should be returned as Faults.
   *  Use `-1` to return all error types; or `0` to return none.
   */
  public int $returnErrors = 0;
}
