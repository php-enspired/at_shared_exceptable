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

use ResourceBundle;

use at\peekaboo\ {
  HasMessages,
  MessageMapper,
  MessageRegistry
};

use at\peekaboo\tests\TestCase;

/** Tests for the MakesMessages trait. */
class MakesMessagesTest extends TestCase {

  protected const MESSAGE_EXPECTATIONS = [
    [
      "top-level-key",
      [],
      "hello, world",
      "hello, world"
    ],
    [
      "nested.key",
      [],
      "hello again, world",
      "hello again, world"
    ],
    [
      "missing-from-intl-bundle",
      [],
      "hello, world",
      "hello, world"
    ],
    [
      "simple-replacement",
      ["name" => "world"],
      "hello, world",
      "hello, world"
    ],
    [
      "escaped-characters",
      [],
      "this isn't {obvious}",
      "this isn't {obvious}"
    ],
    [
      "predefined-styles.date-medium",
      ["footprint" => -14241600],
      "one small step for man on Jul 20, 1969",
      "one small step for man on -14241600"
    ],
    [
      "predefined-styles.number-currency",
      ["price" => 20],
      "that will set you back about $20",
      "that will set you back about 20"
    ],
    [
      "predefined-styles.number-integer-width",
      ["id" => 7],
      "agent 007",
      "agent 7"
    ]
  ];

  protected static HasMessages $instance;
  protected static ResourceBundle $bundle;

  public static function setUpBeforeClass() : void {
    self::$instance = new class() implements HasMessages {
      use MessageMapper;

      public const array MESSAGES = [
        "top-level-key" => "hello, world",
        "nested" => ["key" => "hello again, world"],
        "missing-from-intl-bundle" => "hello, world",
        "simple-replacement" => "hello, {name}",
        "escaped-characters" => "this isn''t '{obvious}'",
        "predefined-styles" => [
          "date-medium" => "one small step for man on {footprint}",
          "number-currency" => "that will set you back about {price}",
          "number-integer-width" => "agent {id}"
        ]
      ];
    };

    MessageRegistry::$defaultLocale = "en_US";

    if (extension_loaded("intl")) {
      self::$bundle = new ResourceBundle("root", __DIR__ . "/resources");
    }
  }

  /** @dataProvider messageFormattingProvider */
  public function testMessageFormatting(
    string $key,
    array $context,
    string $expectedIntl,
    string $expectedFallback
  ) : void {
    $this->assertSame(
      $expectedFallback,
      self::$instance->makeMessage($key, $context),
      "fallback message"
    );

    if (extension_loaded("intl")) {
      MessageRegistry::register(self::$bundle);

      $this->assertSame(
        $expectedIntl,
        self::$instance->makeMessage($key, $context),
        "intl message"
      );

      MessageRegistry::unregister(self::$bundle);
    }
  }

  public static function messageFormattingProvider() : array {
    return self::MESSAGE_EXPECTATIONS;
  }
}
