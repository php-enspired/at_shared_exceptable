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
namespace at\peekaboo;

use at\exceptable\Spl\RuntimeException;
require_once __DIR__ . "/../stubs/exceptable.php";

class MessageException extends RuntimeException {}
