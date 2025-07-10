<?php
/**
 * @package    at.exceptable
 * @subpackage tests
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2025
 * @license    GPL-3.0 (only)
 *
 *  This program is free software: you can redistribute it and/or modify it
 *  under the terms of the GNU General Public License, version 3.
 *  The right to apply the terms of later versions of the GPL is RESERVED.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program.
 *  If not, see <http://www.gnu.org/licenses/gpl-3.0.txt>.
 */
declare(strict_types = 1);

namespace at\exceptable\tests;

use Exception;

use at\exceptable\ {
  ExceptableFault,
  Spl\InvalidArgumentException,
  Spl\LogicException,
  Spl\RuntimeException,
  tests\FaultTestCase
};

class ExceptableFaultTest extends FaultTestCase {

  public static function exceptableTypeProvider() : array {
    return [
      [ExceptableFault::UnknownFault, LogicException::class],
      [ExceptableFault::UnacceptableFault, LogicException::class],
      [ExceptableFault::UncaughtException, RuntimeException::class],
      [ExceptableFault::UnacceptableLogMessage, InvalidArgumentException::class],
      [ExceptableFault::UnknownError, InvalidArgumentException::class]
    ];
  }

  public static function localizedMessageProvider() : array {
    return [
      [
        "en_US",
        ExceptableFault::UnknownFault,
        ["__rootMessage__" => "hello, world"],
        "hello, world",
        true
      ],
      [
        "en_US",
        ExceptableFault::UnacceptableFault,
        ["type" => "Foo"],
        "Invalid Fault type 'Foo' (expected implementation of at\\exceptable\\Fault)",
        true
      ],
      [
        "en_US",
        ExceptableFault::UncaughtException,
        ["__rootType__" => "FooException", "__rootMessage__" => "hello, world"],
        "Uncaught Exception (FooException): hello, world",
        true
      ],
      [
        "en_US",
        ExceptableFault::UnacceptableLogMessage,
        ["type" => "boolean", "from" => true],
        "Invalid log entry message: boolean (true)",
        true
      ],
      [
        "en_US",
        ExceptableFault::UnknownError,
        ["error_get_last" => null],
        "Unknown error (message is missing). Last error: null",
        true
      ]
    ];
  }

  public static function messageProvider() : array {
    return [
      [ExceptableFault::UnknownFault, ["__rootMessage__" => "hello, world"], "hello, world", true],
      [
        ExceptableFault::UnacceptableFault,
        ["type" => "Foo"],
        "Invalid Fault type 'Foo' (expected implementation of at\\exceptable\\Fault)",
        true
      ],
      [
        ExceptableFault::UncaughtException,
        ["__rootType__" => "FooException", "__rootMessage__" => "hello, world"],
        "Uncaught Exception (FooException): hello, world",
        true
      ],
      [
        ExceptableFault::UnacceptableLogMessage,
        ["type" => "boolean", "from" => true],
        "Invalid log entry message: boolean (true)",
        true
      ],
      [
        ExceptableFault::UnknownError,
        ["error_get_last" => null],
        "Unknown error (message is missing). Last error: null",
        true
      ]
    ];
  }

  public static function newExceptableProvider() : array {
    $t = new Exception("hello, world");
    return [
      [
        ExceptableFault::UnknownFault,
        [],
        $t,
        new LogicException(ExceptableFault::UnknownFault, [], $t)
      ],
      [
        ExceptableFault::UnacceptableFault,
        ["type" => "Foo"],
        null,
        new LogicException(ExceptableFault::UnacceptableFault, ["type" => "Foo"])
      ],
      [
        ExceptableFault::UncaughtException,
        [],
        $t,
        new RuntimeException(ExceptableFault::UncaughtException, [], $t)
      ],
      [
        ExceptableFault::UnacceptableLogMessage,
        ["type" => "boolean", "from" => true],
        $t,
        new InvalidArgumentException(ExceptableFault::UnacceptableLogMessage, ["type" => "boolean", "from" => true], $t)
      ],
      [
        ExceptableFault::UnknownError,
        ["error_get_last" => null],
        $t,
        new InvalidArgumentException(ExceptableFault::UnknownError, ["error_get_last" => null], $t)
      ]
    ];
  }

  protected static function faultType() : string {
    return ExceptableFault::class;
  }
}
