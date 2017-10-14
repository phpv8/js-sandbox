<?php declare(strict_types=1);

/*
 * This file is part of the pinepain/js-sandbox PHP library.
 *
 * Copyright (c) 2016-2017 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace Pinepain\JsSandbox\NativeWrappers;


use V8\ObjectValue;
use V8\PrimitiveValue;
use V8\Value;


interface NativeObjectWrapperInterface
{
    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value): void;

    /**
     *
     * @param string $key
     *
     * @return Value|PrimitiveValue|ObjectValue
     */
    public function get(string $key): Value;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool;
}
