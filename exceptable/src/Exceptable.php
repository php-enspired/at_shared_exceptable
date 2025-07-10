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
namespace at\exceptable;

use Throwable;

use at\exceptable\Fault;

/**
 * Exceptional exception interface.
 *
 * @todo phan-suppress PhanCommentObjectInClassConstantType
 */
interface Exceptable extends Throwable {

  /** @property-read Fault $fault */
  /** @property-read array $context */
  /** @property-read Throwable $previous */
  /** @property-read Throwable $root */

  /** Standardized constructor. */
  public function __construct(Fault $fault, array $context = [], ? Throwable $previous = null);

  /** Does this Exceptable contain the given Fault in its exception chain? */
  public function has(Fault $fault) : bool;

  /** Does this Exceptable match the given Fault? */
  public function is(Fault $fault) : bool;
}
