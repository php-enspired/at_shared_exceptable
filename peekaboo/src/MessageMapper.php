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

require_once __DIR__ . "/../stubs/intl.php";

use at\exceptable\Handler\Handler;
use at\peekaboo\ {
  HasMessages,
  MessageBundle,
  MessageFault,
  MessageRegistry
};

/**
 * Implementation for `HasMessages`.
 *
 * ICU message bundles may be set up in one of the following ways:
 * - registering your own `ResourceBundle` with the `MessageRegistry`.
 * - defining a class const `MESSAGES` with a map of formatting strings.
 * - overriding `messageBundle()` with your own implementation.
 */
trait MessageMapper {

  /**
   * The ResourceBundle for this class.
   *
   * We check before referencing (it's defined on the interface):
   * @phan-suppress PhanUndeclaredConstantOfClass
   *
   * @throws MessageException MessageError::BadMessages when attempting to use `MESSAGES` but it is not an array
   */
  protected static function messageBundle() : MessageBundle {
    if (defined(static::class . "::MESSAGES")) {
      if (! is_array(static::MESSAGES)) {
        throw (MessageFault::BadMessages)(["type" => get_debug_type(static::MESSAGES)]);
      }
      return new MessageBundle(static::MESSAGES);
    }
  }

  public function makeMessage(
    string $key,
    array $context = [],
    ? string $locale = null,
    bool $onlyIf = false
  ) : string | MessageFault {
    assert($this instanceof HasMessages);
    $registry = $this->messageRegistry();
    $handler = new Handler()->ignore(MessageFault::NoSuchMessage);
    return $handler->try($registry::message(...), $key, $context, $locale, "", $onlyIf) ??
      $handler->try(
        fn () => (($messages = static::messageBundle()) instanceof MessageBundle) ?
          $registry::messageFrom($messages, $key, $context, $locale, $onlyIf) :
          null
      ) ??
      MessageFault::NoSuchMessage;
  }

  /**
   * Gets the message registry class to use for making messages.
   *
   * Override this method to substitute a different registry.
   *
   * @return string FQCN of the MessageRegistry to use
   */
  protected function messageRegistry() : string {
    return MessageRegistry::class;
  }
}
