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

use at\peekaboo\ {
  MessageFault,
  MessageRegistry
};

/**
 * For classes that build ICU messages.
 *
 * Implementations may override `MESSAGES` to provide fallback format strings for messages used by the class.
 * These will be used as a fallback if no matching message is found on the registry.
 * Override `MESSAGES_LOCALE` to define the locale for these messages.
 */
interface HasMessages {

  /** @internal */
  public const array MESSAGES = [];

  /** @internal */
  public const string MESSAGES_LOCALE = MessageRegistry::ROOT_LOCALE;

  /**
   * Finds and builds a message with the given key and context, if one exists.
   *
   * @param string $key Message identifier
   * @param array $context Contextual information for message replacements
   * @param ?string $locale The locale to use for formatting
   * @param bool $onlyIf Fail if context does not provide replacements for all formatting tokens?
   * @return string Formatted message on success; MessageFault on failure
   */
  public function makeMessage(
    string $key,
    array $context = [],
    ? string $locale = null,
    bool $onlyIf = false
  ) : string | MessageFault;
}
