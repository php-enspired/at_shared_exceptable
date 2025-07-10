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
  Spl\BadFunctionCallException,
  Spl\BadMethodCallException,
  Spl\DomainException,
  Spl\InvalidArgumentException,
  Spl\LengthException,
  Spl\LogicException,
  Spl\OutOfBoundsException,
  Spl\OutOfRangeException,
  Spl\OverflowException,
  Spl\RangeException,
  Spl\RuntimeException,
  Spl\SplFault,
  Spl\UnderflowException,
  Spl\UnexpectedValueException,
  tests\FaultTestCase
};

/**
 * Basic tests for the default Fault implementations.
 *
 * @covers at\exceptable\IsFault
 * @covers at\exceptable\Spl\SplFault
 *
 * Base class to test implementations of Fault.
 *  - override error() to provide the Fault to test
 *  - override *Provider() methods to provide appropriate input and expectations
 */
class SplFaultTest extends FaultTestCase {

  public static function exceptableTypeProvider() : array {
    return [
      [SplFault::BadFunctionCall, BadFunctionCallException::class],
      [SplFault::BadMethodCall, BadMethodCallException::class],
      [SplFault::DomainError, DomainException::class],
      [SplFault::InvalidArgument, InvalidArgumentException::class],
      [SplFault::LengthError, LengthException::class],
      [SplFault::LogicError, LogicException::class],
      [SplFault::OutOfBounds, OutOfBoundsException::class],
      [SplFault::OutOfRange, OutOfRangeException::class],
      [SplFault::Overflow, OverflowException::class],
      [SplFault::RangeError, RangeException::class],
      [SplFault::RuntimeError, RuntimeException::class],
      [SplFault::Underflow, UnderflowException::class],
      [SplFault::UnexpectedValue, UnexpectedValueException::class]
    ];
  }

  public static function localizedMessageProvider() : array {
    $context = ["__rootMessage__" => "hello, world"];
    return [
      ["en_US", SplFault::BadFunctionCall, $context, "hello, world", true],
      ["en_US", SplFault::BadMethodCall, $context, "hello, world", true],
      ["en_US", SplFault::DomainError, $context, "hello, world", true],
      ["en_US", SplFault::InvalidArgument, $context, "hello, world", true],
      ["en_US", SplFault::LengthError, $context, "hello, world", true],
      ["en_US", SplFault::LogicError, $context, "hello, world", true],
      ["en_US", SplFault::OutOfBounds, $context, "hello, world", true],
      ["en_US", SplFault::OutOfRange, $context, "hello, world", true],
      ["en_US", SplFault::Overflow, $context, "hello, world", true],
      ["en_US", SplFault::RangeError, $context, "hello, world", true],
      ["en_US", SplFault::RuntimeError, $context, "hello, world", true],
      ["en_US", SplFault::Underflow, $context, "hello, world", true],
      ["en_US", SplFault::UnexpectedValue, $context, "hello, world", true]
    ];
  }

  public static function messageProvider() : array {
    $context = ["__rootMessage__" => "hello, world"];
    return [
      [SplFault::BadFunctionCall, $context, "hello, world", true],
      [SplFault::BadMethodCall, $context, "hello, world", true],
      [SplFault::DomainError, $context, "hello, world", true],
      [SplFault::InvalidArgument, $context, "hello, world", true],
      [SplFault::LengthError, $context, "hello, world", true],
      [SplFault::LogicError, $context, "hello, world", true],
      [SplFault::OutOfBounds, $context, "hello, world", true],
      [SplFault::OutOfRange, $context, "hello, world", true],
      [SplFault::Overflow, $context, "hello, world", true],
      [SplFault::RangeError, $context, "hello, world", true],
      [SplFault::RuntimeError, $context, "hello, world", true],
      [SplFault::Underflow, $context, "hello, world", true],
      [SplFault::UnexpectedValue, $context, "hello, world", true]
    ];
  }

  public static function newExceptableProvider() : array {
    $context = ["this" => "is only a test"];
    $previous = new Exception("this is the root exception");
    return [
      [
        SplFault::BadFunctionCall,
        $context,
        $previous,
        new BadFunctionCallException(SplFault::BadFunctionCall, $context, $previous)
      ],
      [
        SplFault::BadMethodCall,
        $context,
        $previous,
        new BadMethodCallException(SplFault::BadMethodCall, $context, $previous)
      ],
      [
        SplFault::DomainError,
        $context,
        $previous,
        new DomainException(SplFault::DomainError, $context, $previous)
      ],
      [
        SplFault::InvalidArgument,
        $context,
        $previous,
        new InvalidArgumentException(SplFault::InvalidArgument, $context, $previous)
      ],
      [
        SplFault::LengthError,
        $context,
        $previous,
        new LengthException(SplFault::LengthError, $context, $previous)
      ],
      [
        SplFault::LogicError,
        $context,
        $previous,
        new LogicException(SplFault::LogicError, $context, $previous)
      ],
      [
        SplFault::OutOfBounds,
        $context,
        $previous,
        new OutOfBoundsException(SplFault::OutOfBounds, $context, $previous)
      ],
      [
        SplFault::OutOfRange,
        $context,
        $previous,
        new OutOfRangeException(SplFault::OutOfRange, $context, $previous)
      ],
      [
        SplFault::Overflow,
        $context,
        $previous,
        new OverflowException(SplFault::Overflow, $context, $previous)
      ],
      [
        SplFault::RangeError,
        $context,
        $previous,
        new RangeException(SplFault::RangeError, $context, $previous)
      ],
      [
        SplFault::RuntimeError,
        $context,
        $previous,
        new RuntimeException(SplFault::RuntimeError, $context, $previous)
      ],
      [
        SplFault::Underflow,
        $context,
        $previous,
        new UnderflowException(SplFault::Underflow, $context, $previous)
      ],
      [
        SplFault::UnexpectedValue,
        $context,
        $previous,
        new UnexpectedValueException(SplFault::UnexpectedValue, $context, $previous)
      ]
    ];
  }

  protected static function faultType() : string {
    return SplFault::class;
  }
}
