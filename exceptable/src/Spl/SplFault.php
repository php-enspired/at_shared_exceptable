<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2025
 * @license    MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */
declare(strict_types = 1);

namespace at\exceptable\Spl;

use at\exceptable\ {
  Fault,
  IsFault,
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
  Spl\UnderflowException,
  Spl\UnexpectedValueException
};

/**
 * Faults corresponding to the Spl Exception types.
 *
 * @todo phan-suppress PhanInvalidConstantExpression
 */
enum SplFault implements Fault {
  use IsFault;

  case BadFunctionCall;
  case BadMethodCall;
  case DomainError;
  case InvalidArgument;
  case LengthError;
  case LogicError;
  case OutOfBounds;
  case OutOfRange;
  case Overflow;
  case RangeError;
  case RuntimeError;
  case Underflow;
  case UnexpectedValue;

  /** @see Error::MESSAGES */
  public const MESSAGES = [
    self::BadFunctionCall->name => "{__rootMessage__}",
    self::BadMethodCall->name => "{__rootMessage__}",
    self::DomainError->name => "{__rootMessage__}",
    self::InvalidArgument->name => "{__rootMessage__}",
    self::LengthError->name => "{__rootMessage__}",
    self::LogicError->name => "{__rootMessage__}",
    self::OutOfBounds->name => "{__rootMessage__}",
    self::OutOfRange->name => "{__rootMessage__}",
    self::Overflow->name => "{__rootMessage__}",
    self::RangeError->name => "{__rootMessage__}",
    self::RuntimeError->name => "{__rootMessage__}",
    self::Underflow->name => "{__rootMessage__}",
    self::UnexpectedValue->name => "{__rootMessage__}"
  ];

  /** @see Error::exceptable() */
  protected function exceptableType() : string {
    assert($this instanceof Fault);
    return match ($this) {
      self::BadFunctionCall => BadFunctionCallException::class,
      self::BadMethodCall => BadMethodCallException::class,
      self::DomainError => DomainException::class,
      self::InvalidArgument => InvalidArgumentException::class,
      self::LengthError => LengthException::class,
      self::LogicError => LogicException::class,
      self::OutOfBounds => OutOfBoundsException::class,
      self::OutOfRange => OutOfRangeException::class,
      self::Overflow => OverflowException::class,
      self::RangeError => RangeException::class,
      self::RuntimeError => RuntimeException::class,
      self::Underflow => UnderflowException::class,
      self::UnexpectedValue => UnexpectedValueException::class,
      default => RuntimeException::class
    };
  }
}
