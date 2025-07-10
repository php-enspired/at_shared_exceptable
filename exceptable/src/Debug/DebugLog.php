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

use IteratorAggregate,
  Override,
  Throwable,
  Traversable;
use at\exceptable\ {
  Debug\LogEntry,
  Debug\DebugLogEntry,
  Debug\ErrorLogEntry,
  Debug\FaultLogEntry,
  Debug\ThrowableLogEntry,
  Fault
};
use Psr\Log\ {
  LoggerInterface as Logger,
  LogLevel
};

class DebugLog implements IteratorAggregate {

  public bool $debug = false;

  public array $log = [];

  public ? Logger $logger = null;

  /** Adds a log entry, also logging it if a logger is configured. */
  public function add(LogEntry $entry) : void {
    $this->log[] = $entry;
    if (isset($this->logger)) {
      $level = match (true) {
        $entry instanceof DebugLogEntry => LogLevel::DEBUG,
        $entry instanceof FaultLogEntry => LogLevel::NOTICE,
        $entry instanceof ErrorLogEntry => LogLevel::WARNING,
        $entry instanceof ThrowableLogEntry => LogLevel::ERROR,
      };
      $this->logger->log($level, $entry->message, $entry->context);
    }
  }

  /** Builds and adds a log entry from the given message, also logging it if a logger is configured. */
  public function addFrom($message, array $context = []) : void {
    $this->add(LogEntry::from($message, $context));
  }

  #[Override]
  public function getIterator() : Traversable {
    yield from $this->log;
  }
}
