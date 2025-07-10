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

namespace at\exceptable\Handler;

use at\peekaboo\ {
  HasMessages,
  MessageEnum
};

/** Messages for internal usage. */
enum DebugMessages : string implements HasMessages {
  use MessageEnum;

  case Ignoring = "ignored result: {result}";
  case Collected = "collected error ({result}) as {fault}";
}
