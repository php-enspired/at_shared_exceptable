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
namespace at\peekaboo;

use at\exceptable\ {
  EnumeratesFaults,
  Fault
};

/** @phan-suppress PhanInvalidConstantExpression */
enum MessageFault : string implements Fault {
  use EnumeratesFaults;

  case UnknownError = "unknown error";
  case NoMessages = "no messages provided for {class}";
  case NoSuchMessage = "no message format string found for {bundle}:{key}";
  case NotAMessage = "value at {bundle}:{key} is not a message format string";
  case FormatFailed = "error formatting message: ({error_code}) {error_message}\n" .
    "locale: {locale}\n" .
    "format: {format}\n" .
    "context: {context}";
  case BadMessages = "MakesMessages::MESSAGES must be an array of message formats; {type} declared";
  case IncompleteFormattingContext = "provided context does not contain values for all formatting tokens";
}
