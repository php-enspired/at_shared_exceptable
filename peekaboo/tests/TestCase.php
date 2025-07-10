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

use BadMethodCallException,
  ReflectionClass,
  ReflectionObject;

use at\peekaboo\ {
  MessageFault,
  MessageErrorCase
};

use PHPUnit\Framework\TestCase as PhpunitTestCase;

/** Base Test Case. */
abstract class TestCase extends PhpunitTestCase {

  /**
   * Sets phpunit's expectException*() methods from an example Error.
   *
   * @param MessageFault $e the Error expected to be thrown
   */
  public function expectError(MessageFault $case) : void {
    $this->expectException(MessageException::class);
    $this->expectExceptionCode($case->value);
  }

  /**
   * Gets the value of a nonpublic property of an object under test.
   *
   * @param object $object The object to inspect
   * @param string $property The property to access
   * @return mixed Property value on success
   */
  protected function getNonpublicProperty(object $object, string $property) {
    $ro = new ReflectionObject($object);
    if (! $ro->hasProperty($property)) {
      throw new BadMethodCallException("Object [" . $object::class . "] has no property '{$property}'");
    }

    $rp = $ro->getProperty($property);
    $rp->setAccessible(true);
    return $rp->getValue($object);
  }

  /**
   * Gets the value of a nonpublic static property of class under test.
   *
   * @param string $fqcn FQCN of the class to modify
   * @param string $property The property to access
   * @return mixed Property value on success
   */
  protected function getNonpublicStaticProperty(string $fqcn, string $property) {
    $rc = new ReflectionClass($fqcn);
    if (! $rc->hasProperty($property)) {
      throw new BadMethodCallException("Class {$fqcn} has no property '{$property}'");
    }

    $rp = $rc->getProperty($property);
    $rp->setAccessible(true);
    return $rp->getValue();
  }

  /**
   * Invokes a nonpublic method on an object under test.
   *
   * Note, it's VERY EASY to BREAK EVERYTHING using this method.
   *
   * @param object $object The object under test
   * @param string $method The method to invoke
   * @param mixed ...$args Argument(s) to use for invocation
   * @return mixed The return value from the method invocation
   */
  protected function invokeNonpublicMethod(object $object, string $method, ...$args) {
    $ro = new ReflectionObject($object);
    if (! $ro->hasMethod($method)) {
      throw new BadMethodCallException("Object [" . $object::class . "] has no method '{$method}'");
    }

    $rm = $ro->getMethod($method);
    $rm->setAccessible(true);
    return $rm->invoke($object, ...$args);
  }

  /**
   * Sets the value of a nonpublic property of an object under test.
   *
   * Note, it's VERY EASY to BREAK EVERYTHING using this method.
   *
   * @param object $object Object to modify
   * @param string $property Property to set
   * @param mixed $value Value to set
   * @return void
   */
  protected function setNonpublicProperty(object $object, string $property, $value) : void {
    $ro = new ReflectionObject($object);
    if (! $ro->hasProperty($property)) {
      throw new BadMethodCallException("Object [" . $object::class . "] has no property '{$property}'");
    }

    $rp = $ro->getProperty($property);
    $rp->setAccessible(true);
    $rp->setValue($object, $value);
  }

  /**
   * Sets the value of a nonpublic static property of a class under test.
   *
   * Note, it's VERY EASY to BREAK EVERYTHING using this method.
   *
   * @param string $fqcn FQCN of class to modify
   * @param string $property Property to set
   * @param mixed $value Value to set
   * @return void
   */
  protected function setNonpublicStaticProperty(string $fcqn, string $property, $value) : void {
    $rc = new ReflectionClass($fcqn);
    if (! $rc->hasProperty($property)) {
      throw new BadMethodCallException("Class {$fqcn} has no property '{$property}'");
    }

    $rp = $rc->getProperty($property);
    $rp->setAccessible(true);
    $rp->setValue($value);
  }
}
