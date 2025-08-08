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

use Override, Throwable;
use at\exceptable\ {
  Fault,
  Exceptable,
  Debug\LogEntry
};

/** Log entry for exceptions. */
final class ThrowableLogEntry extends LogEntry {

  public readonly ? Fault $fault;

  public function __construct(
    public readonly Throwable $exception,
    ...$logEntryProperties
  ) {
    // false positive: Exceptable->fault is defined
    // @phan-suppress-next-line PhanUndeclaredProperty
    $this->fault = ($exception instanceof Exceptable) ? $exception->fault : null;
    parent::__construct(...$logEntryProperties);
  }

  #[Override]
  protected function augmentContext(array $context) : array {
    return parent::augmentContext(["__exception__" => $this->exception, "__fault__" => $this->fault] + $context);
  }
}
