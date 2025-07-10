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

use Override,
  Throwable;

use at\exceptable\ {
  Fault,
  Exceptable
};

/**
 * Base implementation for Exceptable interface, including contexted message construction.
 *
 * This trait is intended for use only by Exceptable implementations.
 * This expectation is sanity-checked via assertions -
 *  this is a convenience for development, and should not be relied upon in production
 *  (as assertions can, and generally should, be disabled in production).
 */
trait IsExceptable {

  public readonly ? Throwable $root;
  public readonly array $context;

  #[Override]
  public function __construct(
    public readonly Fault $fault,
    array $context = [],
    public readonly ? Throwable $previous = null
  ) {
    assert($this instanceof Exceptable);

    $this->root = isset($previous) ?
      $this->findRoot($previous) :
      $this;

    $this->context = [
      "__exception__" => $this,
      "__previous__" => $this->previous,
      "__root__" => $this->root
    ] + $context;
    // false positive: we're an Exceptable, and Exceptables extend from Throwable
    // @phan-suppress-next-line PhanTraitParentReference
    parent::__construct($this->fault->message($this->context, $this->previous), 0, $previous);
  }

  #[Override]
  public function has(Fault $fault) : bool {
    $t = $this;
    while ($t instanceof Throwable) {
      if ($t instanceof Exceptable && $t->fault === $fault) {
        return true;
      }

      $t = $t->getPrevious();
    }

    return false;
  }

  #[Override]
  public function is(Fault $fault) : bool {
    return $this->fault === $fault;
  }

  private function findRoot(Throwable $t) : Throwable {
    $root = $t;
    while (($previous = $root->getPrevious()) instanceof Throwable) {
      $root = $previous;
    }

    return $root;
  }
}
