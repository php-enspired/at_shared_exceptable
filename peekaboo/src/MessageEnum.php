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
use at\exceptable\Handler\Handler;
use at\peekaboo\ {
  EnumeratesMessages,
  MessageFault
};

/** Implementation for `EnumeratesMessages`. */
trait MessageEnum {
  use MessageMapper {
    MessageMapper::makeMessage as _makeMessage;
  }

  public function message(array $context = []) : string | MessageFault {
    assert($this instanceof EnumeratesMessages && $this instanceof BackedEnum && is_string($this->value));
    $locale = defined("static::MESSAGES_LOCALE") ? constant("static::MESSAGES_LOCALE") : EnumeratesMessages::MESSAGES_LOCALE;
    return $this->messageRegistry()::formatMessage($this->value, $context, $locale, true);
  }

  /** Calls `MakesMessages->makeMessage()`, falling back on `EnumeratesMessages->message()`. */
  public function makeMessage(string $key, array $context = [], ? string $locale = null, bool $onlyIf = false) : string | MessageFault {
    assert($this instanceof EnumeratesMessages);
    return new Handler()->ignore(MessageFault::NoSuchMessage)->try($this->_makeMessage(...), $key, $context, $locale, $onlyIf) ??
      $this->message($context);
  }
}
