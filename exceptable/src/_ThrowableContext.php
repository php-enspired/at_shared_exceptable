<?php
/**
 * @package    at.exceptable
 * @author     Adrian <adrian@enspi.red>
 * @copyright  2014 - 2024
 * @license    MPL-2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 */
declare(strict_types = 1);
namespace at\exceptable;

use Throwable;
use at\exceptable\Exceptable;

/** @internal */
enum _ThrowableContext : string {

  case Exception = "exception";
  case Previous = "previous";
  case Root = "root";

  public static function addThrowableContext(array $context, ? Throwable ...$throwables) : array {
    foreach (_ThrowableContext::cases() as $type) {
      $throwable = $throwables[$type->value] ?? $context["__{$type->value}__"] ?? null;
      if (isset($throwable)) {
        $context += self::throwableContextFrom($throwable, $type);
      } else {
        $context["__{$type->value}__"] = null;
      }
    }
    if (! isset($context["__root__"])) {
      $exceptable = $context["__exception__"] ?? $context["__previous__"] ?? null;
      if ($exceptable instanceof Exceptable) {
        // false positive: Exceptable->root
        // @phan-suppress-next-line PhanUndeclaredProperty
        $context += self::throwableContextFrom($exceptable->root, self::Root);
      }
    }
    return $context;
  }

  private static function throwableContextFrom(Throwable $throwable, _ThrowableContext $type) : array {
    return [
      "__{$type->value}__" => $throwable,
      "__{$type->value}Message__" => $throwable->getMessage(),
      "__{$type->value}Code__" => $throwable->getCode(),
      "__{$type->value}File__" => $throwable->getFile(),
      "__{$type->value}Line__" => $throwable->getLine(),
        // false positive: Exceptable->fault
        // @phan-suppress-next-line PhanUndeclaredProperty
      "__{$type->value}Fault__" => ($throwable instanceof Exceptable) ? $throwable->fault : null
    ];
  }
}
