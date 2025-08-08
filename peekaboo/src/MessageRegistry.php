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

use MessageFormatter,
  ResourceBundle;
require_once __DIR__ . "/../stubs/intl.php";

use at\peekaboo\MessageFault;

/** Global shorthand for MessageRegistry::message(). */
function _(
  string $key,
  array $context,
  ? string $locale = null,
  string $group = ""
) : string | MessageFault {
  return MessageRegistry::message($key, $context, $locale, $group);
}

/**
 * Wraps ICU message bundles and formatting process;
 *  serves as a centralized place to register and access message bundles.
 *
 * Use of intl features will fail gracefully if the extension is not enabled.
 */
class MessageRegistry {

  public const ROOT_LOCALE = "root";

  /** The default locale to use for message lookups/formatting. */
  public static ? string $defaultLocale = null;

  private static array $bundles = [];

  /**
   * Formats a message.
   *
   * @param string $format Message formatting string
   * @param array $context Contextual replacements
   * @param ?string $locale Target message locale
   * @param bool $onlyIf Fail if context does not provide replacements for all formatting tokens?
   * @return string|MessageFault Formatted message on success; MessageFault on error
   */
  final public static function formatMessage(
    string $format,
    array $context,
    ? string $locale = null,
    bool $onlyIf = false
  ) : string | MessageFault {
    $message = static::messageFormatter($format, $locale)->format(static::prepFormattingContext($context));
    if ($message === false) {
      return MessageFault::FormatFailed;
    }
    if ($onlyIf) {
      preg_match_all("(\{(\w+)\})", $format, $matches);
      if ($message !== self::formatMessage($format, $context + array_flip($matches[1]))) {
        return MessageFault::IncompleteFormattingContext;
      }
    }
    return $message;
  }

  /**
   * Looks up and formats the message identified by key, using the given locale and context.
   *
   * @param string $key Dot-delimited key path to target message
   * @param array $context Contextual replacements
   * @param ?string $locale Target message locale
   * @param string $group Prefered message bundle to use
   * @param bool $onlyIf Fail if context does not provide replacements for all formatting tokens?
   * @return string|MessageFault Formatted message on success; MessageFault on error
   */
  final public static function message(
    string $key,
    array $context,
    ? string $locale = null,
    string $group = "",
    bool $onlyIf = false
  ) : string | MessageFault {
    return is_string($format = static::findFormat($key, $group)) ?
      static::formatMessage($format, $context, $locale, $onlyIf) :
      MessageFault::NoMessages;
  }

  /**
   * Looks up and formats the message identified by key, using the given bundle, locale, and context.
   *
   * @param ResourceBundle $messages The messages bundle to look up from
   * @param string $key Dot-delimited key path to target message
   * @param array $context Contextual replacements
   * @param ?string $locale Target message locale
   * @param bool $onlyIf Fail if context does not provide replacements for all formatting tokens?
   * @return string|MessageFault Formatted message on success; MessageFault on error
   */
  final public static function messageFrom(
    ResourceBundle $messages,
    string $key,
    array $context,
    ? string $locale = null,
    bool $onlyIf = false
  ) : string | MessageFault {
    return is_string($format = static::findFormatIn($messages, $key)) ?
      static::formatMessage($format, $context, $locale, $onlyIf) :
      MessageFault::NoMessages;
  }

  /**
   * Adds a resource bundle to the registry.
   *
   * @param ResourceBundle $messages Message format patterns
   * @param string $name An label for grouping these messages
   */
  final public static function register(ResourceBundle $messages, string $name = "") : void {
    static::$bundles[$name][] = $messages;
  }

  /**
   * Removes a resource bundle from the registry.
   *
   * @param ResourceBundle $messages Message format patterns
   * @param string $name grouping label to remove messages from
   */
  final public static function unregister(ResourceBundle $messages, string $name = "") : void {
    if (isset(static::$bundles[$name])) {
      $index = array_search($messages, static::$bundles[$name], true);
      if ($index !== false) {
        unset(static::$bundles[$name][$index]);
      }
    }
  }

  /**
   * Finds a message format string for the given locale.
   * Falls back on the root locale if needed.
   *
   * @param string $key Dot-delimited key path to target message
   * @param string $group Prefered message group
   * @throws MessageException MessageError::NotAMessage if key exists but is not a formatting string
   * @return string|null Formatting message if found; null otherwise
   */
  private static function findFormat(string $key, string $group) : ? string {
    $groups = (empty($group)) ? [$group] : [$group, ""];
    foreach ($groups as $name) {
      if (! empty(static::$bundles[$name])) {
        foreach (static::$bundles[$name] ?? reset(static::$bundles) as $messages) {
          $format = static::findFormatIn($messages, $key);
          if (is_string($format)) {
            return $format;
          }
        }
      }
    }

    return null;
  }

  private static function findFormatIn(ResourceBundle $messages, string $key) : string | MessageFault {
    $key = strtr($key, ["\\" =>"_"]); // supports classnames as keys
    $message = $messages;
    foreach (explode(".", $key) as $next) {
      // @todo: should we support using the entire $key as a key and fall back on segmenting off?

      // more keys but no more message bundles means not found
      if (! $message instanceof ResourceBundle) {
        return MessageFault::NotAMessage;
      }

      $message = $message->get($next);
    }

    if (is_string($message)) {
      return $message;
    }
    return ($message === null) ? MessageFault::NoSuchMessage : MessageFault::NotAMessage;
  }

  private static function messageFormatter(string $format, ? string $locale = null) : MessageFormatter {
    $locale ??= static::$defaultLocale ?? static::ROOT_LOCALE;
    return new MessageFormatter($locale, $format);
  }

  private static function prepFormattingContext(array $context) : array {
    $formattingContext = [];
    foreach ($context as $key => $value) {
      $formattingContext[$key] = static::toFormattingValue($value);
    }

    return $formattingContext;
  }

  private static function toFormattingValue($value) : ? string {
    return match (gettype($value)) {
      "string" => $value,
      "integer", "double" => (string) $value,
      "object" => $value::class . ":" . (
        method_exists($value, "__toString") ?
          $value->__toString() :
          json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
      ),
      "array", "boolean", "null" =>
        json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
      "resource", "resource (closed)" => get_resource_type($value) . "#" . get_resource_id($value),
      default => get_debug_type($value)
    };
  }
}
