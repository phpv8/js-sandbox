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


namespace Pinepain\JsSandbox\Extractors\ObjectComponents;


use OverflowException;
use UnexpectedValueException;
use V8\ObjectValue;


interface ExtractorsObjectStoreInterface
{
    /**
     * @param ObjectValue $object
     *
     * @return bool
     */
    public function has(ObjectValue $object): bool;

    /**
     * @param ObjectValue $object
     *
     * @return object
     * @throws UnexpectedValueException
     */
    public function get(ObjectValue $object);

    /**
     * @param ObjectValue $object
     * @param             $value
     *
     * @return void
     * @throws OverflowException
     */
    public function put(ObjectValue $object, $value);
}
