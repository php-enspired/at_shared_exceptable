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

/**
 * @internal
 * @see https://php.net/MessageFormatter
 *
 * This is a stub/fallback for internal usage when ext/intl is not loaded.
 * MessageFormatter->format() uses basic string substitution
 *  (locale and complex format instructions are ignored).
 * If your project uses complex formatting, then you should ensure intl is available.
 *
 * MessageFormatter->getErrorCode() and ->getErrorMessage() always tell you "unknown error."
 * Other methods are not emulated.
 *
 * Note, this class is not available to the autoloader.
 */
class MessageFormatter {

  protected string $format = "";

  public function __construct(string $locale, string $format) {
    // escape our escape sequence
    $format = strtr($format, ["\\"=>"\\\\"]);
    $length = strlen($format);
    $escaped = false;
    $nesting = 0;
    $hungry = false;
    $skip = false;
    for ($i = 0; $i < $length; $i++) {
      switch ($format[$i]) {
        case "'":
          // two in a row are a literal '
          if ($format[$i + 1] === "'") {
            $this->format .= "'";
            $i++;
            break;
          }
          // leaving ICU escaping context
          if ($escaped) {
            $escaped = false;
            break;
          }
          // entering ICU escaping context
          $escaped = true;
          break;
        case "{":
          if ($escaped) {
            $this->format .= "{\\";
            break;
          }
          // entering token
          if ($nesting === 0) {
            $hungry = true;
            $this->format .= "{";
          }
          $nesting++;
          break;
        case "}":
          // this is an unmatched closing brace; don't go negative
          if ($nesting > 0) {
            $nesting--;
          }
          // leaving token
          if ($nesting === 0) {
            $this->format .= "}";
            $skip = false;
          }
          break;
        default:
          // inside a token, eat whitespace between opening curly and name
          if ($hungry && preg_match("(\s)", $format[$i])) {
            break;
          }
          // inside a token, once the first word is done,
          if ($nesting > 0 && ! preg_match("(\w)", $format[$i])) {
            $skip = true;
          }
          // we're going to skip to the end
          if ($skip) {
            break;
          }
          // else we're building
          $hungry = false;
          $this->format .= $format[$i];
          break;
      }
    }
  }

  public function format(array $context) : string|false {
    $findAndReplace = [];
    foreach ($context as $find => $replace) {
      $findAndReplace["{{$find}}"] = $replace;
    }

    // do replacements and then unescape our escape sequences
    return strtr(
      strtr($this->format, $findAndReplace),
      ["\\\\" => "\\", "{\\" => "{"]
    );
  }

  public function getErrorCode() : int {
    return 1;
  }

  public function getErrorMessage() : string {
    return "UNKNOWN_ERROR";
  }
}
