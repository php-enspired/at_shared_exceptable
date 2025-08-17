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

use BackedEnum,
  Exception,
  ResourceBundle,
  Throwable;

use at\exceptable\ {
  Fault,
  Exceptable,
  IsExceptable,
  tests\TestCase
};

use at\peekaboo\MessageRegistry;

/**
 * Basic tests for Fault implementations.
 *
 * @covers at\exceptable\IsFault
 *
 * Extend this class to test your Fault.
 *  - override fault() to provide the Fault to test
 *  - override *Provider() methods to provide appropriate input and expectations
 */
abstract class FaultTestCase extends TestCase {

  /** @return array[] @see FaultTestCase::testExceptableType() */
  abstract public static function exceptableTypeProvider() : array;

  /** @return array[] @see FaultTestCase::testMessage() */
  abstract public static function messageProvider() : array;

  /** @return array[] @see FaultTestCase::testNewExceptable() */
  abstract public static function newExceptableProvider() : array;

  /** @return string Fully qualified classname of Fault under test. */
  abstract protected static function faultType() : string;

  public static function errorProvider() : array {
    return array_map(
      fn ($fault) => [$fault],
      static::faultType()::cases()
    );
  }

  /**
   * @dataProvider errorProvider
   *
   * @todo This test assumes the Fault is implemented as an enum. You MUST override this test if your Fault is not an enum!
   */
  public function testFaultName(Fault $fault) : void {
    $expected = $fault::class . ".{$fault->name}";
    $this->assertSame($expected, $fault->name(), "Fault does not report expected name '{$expected}' (saw '{$fault->name()}')");
  }

  /** @dataProvider exceptableTypeProvider */
  public function testExceptableType(Fault $fault, string $expected) : void {
    $this->assertSame($expected, get_class($fault->toExceptable()));
  }

  /**
   * @dataProvider messageProvider
   *
   * @param Fault $fault The Fault instance to test
   * @param array $context Contextual information for the message
   * @param string $expected The expected message (pass an empty array if no context is required)
   * @param string $locale The locale to use (omit if using `EnumeratesFaults`)
   * @param ResourceBundle $bundle The bundle to use for messages (omit if using `EnumeratesFaults`)
   */
  public function testMessage(
    Fault $fault,
    array $context,
    string $expected,
    ? string $locale = null,
    ? ResourceBundle $bundle = null
  ) {
    try {
      MessageRegistry::$defaultLocale = $locale;

      $faultName = $fault->name();
      $this->assertSame(
        "{$faultName}: {$expected}",
        $fault->message($context),
        "Fault does not return expected message with context"
      );

      // no bundle means we're done with this test
      if (! isset($bundle)) {
        return;
      }

      if (! extension_loaded("intl")) {
        $this->markTestIncomplete("php:intl extension is not loaded");
      }

      try {
        MessageRegistry::register($bundle, $fault::class);

        $faultName = $fault->name();

        $this->assertSame(
          empty($expected) ? $faultName : "{$faultName}: {$expected}",
          $fault->message($context),
          "Fault does not return expected localized message with context"
        );
      } finally {
        MessageRegistry::unregister($bundle, $fault::class);
      }
    } finally {
      MessageRegistry::$defaultLocale = null;
    }
  }

  /** @dataProvider newExceptableProvider */
  public function testNewExceptable(
    Fault $fault,
    ? array $context,
    ? Throwable $previous,
    Exceptable $expected
  ) {
    // we're testing both __invoke() and toExceptable() - both should behave identically
    foreach ([$fault, $fault->toExceptable(...)] as $method) {
      $line = __LINE__ + 1;
      $actual = $method($context, $previous);

      $this->assertExceptableIsExceptable($actual, get_class($expected));
      $this->assertExceptableOrigination($actual, __FILE__, $line);
      $this->assertExceptableHasCode($actual, $expected->getCode());
      $this->assertExceptableHasMessage($actual, $expected->getMessage());
      $this->assertExceptableIsFault($actual, $fault);
      $this->assertExceptableHasFault($actual, $fault);
      if ($previous instanceof Exceptable) {
        $this->assertExceptableHasFault($actual, $previous->fault);
      }
      $this->assertExceptableHasContext($actual, $context);
      $this->assertExceptableHasPrevious($actual, $expected->getPrevious());
      $this->assertExceptableHasRoot($actual, $expected->getPrevious() ?? $actual);
    }
  }

  /** Asserts test subject is an instance of Exceptable and of the given FQCN. */
  protected function assertExceptableIsExceptable($actual, string $fqcn) : void {
    $this->assertInstanceOf(Exceptable::class, $actual, "Exceptable is not Exceptable");
    $this->assertInstanceOf($fqcn, $actual, "Exceptable is not an instance of {$fqcn}");
  }

  /** Asserts test subject has the expected origin file and line number. */
  protected function assertExceptableOrigination(Exceptable $actual, string $file, int $line) : void {
    $this->assertSame(
      $file,
      $actualFile = $actual->getFile(),
      "Exceptable does not report expected filename '{$file}' (saw '{$actualFile}')"
    );
    $this->assertSame(
      $line,
      $actualLine = $actual->getLine(),
      "Exceptable does not report expected line number '{$line}' (saw '{$actualLine}')"
    );
  }

  /** Asserts test subject has the expected code. */
  protected function assertExceptableHasCode(Exceptable $actual, int $code) : void {
    $this->assertSame(
      $code,
      $actualCode = $actual->getCode(),
      "Exceptable does not report expected code '{$code}' (saw '{$actualCode}')"
    );
  }

  /** Asserts test subject has the expected (possibly formatted) message. */
  protected function assertExceptableHasMessage(Exceptable $actual, string $message) : void {
    $this->assertSame(
      $message,
      $actualMessage = $actual->getMessage(),
      "Exceptable does not report expected message '{$message}' (saw '{$actualMessage}')"
    );
  }

  /** Asserts test subject has the expected contextual information. */
  protected function assertExceptableHasContext(Exceptable $actual, ? array $context) : void {
    $actualContext = $actual->context;

    $this->assertArrayHasKey("__exception__", $actualContext, "context[__exception__] is missing");
    $this->assertInstanceOf(
      Exceptable::class,
      $actualContext["__exception__"], "context[__exception__] is not an Exceptable"
    );
    $this->assertArrayHasKey("__previous__", $actualContext, "context[__previous__] is missing");
    if (isset($context["__previous__"])) {
      $this->assertInstanceOf(
        Throwable::class,
        $actualContext["__previous__"], "context[__previous__] is not a Throwable"
      );
    }
    $this->assertArrayHasKey("__root__", $actualContext, "context[__root__] is missing");
    $this->assertInstanceOf(
      Throwable::class,
      $actualContext["__root__"], "context[__root__] is not a Throwable"
    );

    if (isset($context)) {
      foreach ($context as $key => $value) {
        $this->assertArrayHasKey($key, $actualContext, "context[{$key}] is missing");

        $this->assertSame(
          $value,
          $actualValue = $actualContext[$key],
          "context[{$key}] does not hold expected value '{$this->asString($value)}' (saw '{$actualValue}')"
        );
      }
    }
  }

  /** Asserts test subject has the expected previous Exception. */
  protected function assertExceptableHasPrevious(Exceptable $actual, ?Throwable $previous) : void {
    $message = isset($previous) ?
      "getPrevious() does not report expected exception '" . get_class($previous) . "'" :
      "getPrevious() reports a previous exception but none was expected";
    $this->assertSame($previous, $actual->getPrevious(), $message);
  }

  /** Asserts test subject has the expected root Exception. */
  protected function assertExceptableHasRoot(Exceptable $actual, Throwable $root) : void {
    $fqcn = get_class($root);
    $this->assertSame(
      $root,
      $actual->root,
      "getPrevious() does not report expected root exception '{$fqcn}'"
    );
  }

  /** Asserts test subject has the given Fault case. */
  protected function assertExceptableHasFault(Exceptable $actual, Fault $fault) : void {
    $this->assertTrue(
      $actual->has($fault),
      "Exceptable->has() does not have expected Fault '{$fault->name()}'"
    );
  }

  /** Asserts test subject matches the expected Fault case. */
  protected function assertExceptableIsFault(Exceptable $actual, Fault $fault) : void {
    $this->assertSame(
      $actual->fault,
      $fault,
      "Exceptable does not match expected Fault '{$fault->name()}'"
    );
    $this->assertTrue(
      $actual->is($fault),
      "Exceptable->is() does not match expected Fault '{$fault->name()}'"
    );
  }
}
