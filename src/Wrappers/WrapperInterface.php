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


namespace Pinepain\JsSandbox\Wrappers;


use V8\ArrayObject;
use V8\Context;
use V8\FunctionObject;
use V8\Isolate;
use V8\ObjectValue;
use V8\PrimitiveValue;
use V8\Value;


interface WrapperInterface
{
    /**
     * @param Isolate $isolate
     * @param Context $context
     * @param         $value
     *
     * @return Value|PrimitiveValue|ObjectValue|ArrayObject|FunctionObject
     *
     * @throws WrapperException
     */
    public function wrap(Isolate $isolate, Context $context, $value);
}
