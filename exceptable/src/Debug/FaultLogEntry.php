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
use at\exceptable\ {
  Fault,
  Debug\LogEntry
};

/** Log entry for faults. */
final class FaultLogEntry extends LogEntry {

  public function __construct( public readonly Fault $fault, ...$logEntryProperties) {
    parent::__construct(...$logEntryProperties);
  }

  #[Override]
  protected function augmentContext(array $context) : array {
    return parent::augmentContext(["__fault__" => $this->fault] + $context);
  }
}
