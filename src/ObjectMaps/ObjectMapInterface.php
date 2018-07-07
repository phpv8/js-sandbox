<?php declare(strict_types=1);

/*
 * This file is part of the pinepain/php-object-maps PHP library.
 *
 * Copyright (c) 2016-2017 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit http://opensource.org/licenses/MIT
 */


namespace PhpV8\ObjectMaps;

use Countable;
use PhpV8\ObjectMaps\Exceptions\OutOfBoundsException;
use PhpV8\ObjectMaps\Exceptions\OverflowException;


interface ObjectMapInterface extends Countable
{
    const DEFAULT = 0;
    const WEAK_KEY = 1 << 0;
    const WEAK_VALUE = 1 << 1;
    const WEAK_KEY_VALUE = self::WEAK_KEY | self::WEAK_VALUE;

    /**
     * @param object $key
     * @param object $value
     *
     * @throws OverflowException
     * @return void
     */
    public function put(object $key, object $value);

    /**
     * @param object $key
     *
     * @throws OutOfBoundsException
     *
     * @return object
     */
    public function get(object $key);

    /**
     * @param object $key
     *
     * @return bool
     */
    public function has(object $key): bool;

    /**
     * @param object $key
     *
     * @throws OutOfBoundsException
     *
     * @return object
     */
    public function remove(object $key);

    /**
     * @return void
     */
    public function clear();
}
