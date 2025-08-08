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

/** Defines fault cases for specific error conditions. */
interface Fault {

  /** @property-read string $name */

  /** @see Fault::toExceptable - these methods MUST behave identically. */
  public function __invoke(array $context = [], ? Throwable $previous = null) : Exceptable;

  /** The error message for this Fault, using the given context if applicable. */
  public function message(array $context = []) : string;

  /** A human-readable name for this Fault. */
  public function name() : string;

  /** Creates an Exceptable from this Fault. */
  public function toExceptable(array $context = [], ? Throwable $previous = null) : Exceptable;
}
