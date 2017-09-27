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
use V8\IntegerValue;
use V8\Isolate;
use V8\ObjectValue;


class ArrayWrapper implements WrapperInterface, WrapperAwareInterface
{
    use WrapperAwareTrait;

    /**
     * @param Isolate $isolate
     * @param Context $context
     * @param array   $value
     *
     * @return ArrayObject|ObjectValue
     * @throws WrapperException
     */
    public function wrap(Isolate $isolate, Context $context, $value)
    {
        // NOTE: js arrays, unlike php, will have gaps if indices are not monotonically increasing from 0.
        //       Also, non-integer keys does not affect array length as they become properties of array object,
        //       so if we have associative array, we will wrap it as object.
        if ($this->isAssoc($value)) {
            return $this->wrapAsObject($isolate, $context, $value);
        } else {
            return $this->wrapAsArray($isolate, $context, $value);
        }
    }

    private function isAssoc(array $value)
    {
        if (!$value) {
            return false;
        }

        sort($value);

        return array_keys($value) != range(0, count($value) - 1);
    }

    private function wrapAsArray(Isolate $isolate, Context $context, array $value): ArrayObject
    {
        $ret = new ArrayObject($context);
        // TODO: write test to ensure items have the same order, sort() was breaking that order
        $key = 0;
        foreach ($value as $val) {
            $js_val = $this->wrapper->wrap($isolate, $context, $val);
            $js_key = new IntegerValue($isolate, $key++);
            $ret->set($context, $js_key, $js_val);
        }

        return $ret;
    }

    private function wrapAsObject(Isolate $isolate, Context $context, array $value): ObjectValue
    {
        $ret = new ObjectValue($context);

        foreach ($value as $key => $val) {
            $js_val = $this->wrapper->wrap($isolate, $context, $val);
            $js_key = $this->wrapper->wrap($isolate, $context, $key);
            $ret->set($context, $js_key, $js_val);
        }

        return $ret;
    }
}
