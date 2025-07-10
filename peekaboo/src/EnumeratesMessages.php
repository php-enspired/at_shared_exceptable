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
namespace at\peekaboo;

use BackedEnum;
use at\peekaboo\MessageFault;

/** For classes that build ICU messages. */
interface EnumeratesMessages extends BackedEnum, HasMessages {

  /**
   * Builds this case's message using the given context.
   *
   * @param array $context Contextual information for message replacements
   * @return string Formatted message on success; MessageFault on failure
   */
  public function message(array $context = []) : string | MessageFault;
}
