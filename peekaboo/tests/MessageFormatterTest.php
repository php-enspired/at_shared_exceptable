<?php
/**
 * @package    at.peekaboo
 * @subpackage tests
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2023
 * @license    MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */
declare(strict_types = 1);
namespace at\peekaboo\tests;

use at\peekaboo\MessageFormatter;
use at\peekaboo\tests\TestCase;

require_once __DIR__ . "/../stubs/MessageFormatter.php";

/** Tests for the MessageFormatter class. */
class MessageFormatterTest extends TestCase {

  /** @dataProvider formatTestProvider */
  public function testFormat(string $format, array $context, string $expected) {
    $this->assertSame(
      $expected,
      (new MessageFormatter("root", $format))->format($context)
    );
  }

  /**
   * @return array[]
   *  - string $0 Message format
   *  - string[] $1 Contextual replacements
   *  - string $2 Expected result
   */
  public static function formatTestProvider() : array {
    return [
      "simple token" => [
        "hello, {token}!",
        ["token" => "world"],
        "hello, world!"
      ],
      "intl token" => [
        "hello, {token, with {{intl} junk}}!",
        ["token" => "world"],
        "hello, world!"
      ],
      "token with whitespace" => [
        "a malformed { token} appears",
        ["token" => "world"],
        "a malformed world appears"
      ],
      "escaped single quote" => [
        "well that wasn''t expected",
        [],
        "well that wasn't expected"
      ],
      "escaped braces" => [
        "this is not a '{token}'!",
        ["token" => "world"],
        "this is not a {token}!"
      ]
    ];
  }
}
