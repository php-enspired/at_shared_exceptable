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

namespace at\exceptable\Debug;

use Override;
use at\exceptable\Debug\LogEntry;

/** Log entry for errors. */
final class ErrorLogEntry extends LogEntry {

  /** @var bool Was this error suppressed by the error control operator? */
  public readonly bool $controlled;

  public function __construct( public readonly int $code = 0, ...$logEntryProperties) {
    $this->controlled = (error_reporting() === 0);
    parent::__construct(...$logEntryProperties);
  }

  #[Override]
  protected function augmentContext(array $context) : array {
    return parent::augmentContext(["__code__" => $this->code, "__controlled__" => $this->controlled] + $context);
  }
}
