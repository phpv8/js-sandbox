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


namespace Pinepain\JsSandbox\Wrappers\ObjectComponents;


use OverflowException;
use UnexpectedValueException;
use V8\ObjectValue;


interface WrappersObjectStoreInterface
{
    /**
     * @param object $object
     *
     * @return bool
     */
    public function has($object): bool;

    /**
     * @param object $object
     *
     * @return ObjectValue
     * @throws UnexpectedValueException
     */
    public function get($object): ObjectValue;

    /**
     * @param object      $object
     * @param ObjectValue $value
     *
     * @return void
     * @throws OverflowException
     */
    public function put($object, ObjectValue $value);
}
