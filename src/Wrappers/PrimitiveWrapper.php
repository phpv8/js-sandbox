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


use V8\BooleanValue;
use V8\Context;
use V8\Isolate;
use V8\NullValue;
use V8\NumberValue;
use V8\StringValue;
use function is_bool;
use function is_float;
use function is_int;
use function is_null;
use function is_string;


class PrimitiveWrapper implements WrapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function wrap(Isolate $isolate, Context $context, $value)
    {
        if (is_null($value)) {
            return new NullValue($isolate);
        }

        if (is_bool($value)) {
            return new BooleanValue($isolate, $value);
        }

        if (is_string($value)) {
            return new StringValue($isolate, $value);
        }

        if (is_int($value) || is_float($value)) {
            return new NumberValue($isolate, $value);
        }

        throw new WrapperException('Vale type ' . gettype($value) . ' is not supported');
    }
}
