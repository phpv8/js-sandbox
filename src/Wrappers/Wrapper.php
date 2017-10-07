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


use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunction;
use V8\Context;
use V8\Isolate;
use V8\Value;


class Wrapper implements WrapperInterface
{
    /**
     * @var WrapperInterface
     */
    private $primitive_wrapper;
    /**
     * @var WrapperInterface
     */
    private $function_wrapper;
    /**
     * @var WrapperInterface
     */
    private $object_wrapper;
    /**
     * @var WrapperInterface
     */
    private $array_wrapper;

    /**
     * {@inheritdoc}
     */
    public function wrap(Isolate $isolate, Context $context, $value)
    {
        if (null === $value || is_scalar($value)) {
            return $this->primitive_wrapper->wrap($isolate, $context, $value);
        }

        if (is_object($value)) {
            // Some functions may return V8 native value
            if ($value instanceof Value) {
                return $value;
            }

            if ($value instanceof RuntimeFunction) {
                return $this->function_wrapper->wrap($isolate, $context, $value);
            }

            return $this->object_wrapper->wrap($isolate, $context, $value);
        }

        if (is_array($value)) {
            return $this->array_wrapper->wrap($isolate, $context, $value);
        }

        throw new WrapperException('Vale type ' . gettype($value) . ' is not supported');
    }

    /**
     * @param WrapperInterface $primitive_wrapper
     */
    public function setPrimitiveWrapper(WrapperInterface $primitive_wrapper)
    {
        $this->primitive_wrapper = $primitive_wrapper;
    }

    /**
     * @param WrapperInterface $function_wrapper
     */
    public function setFunctionWrapper(WrapperInterface $function_wrapper)
    {
        $this->function_wrapper = $function_wrapper;
    }

    /**
     * @param WrapperInterface $object_wrapper
     */
    public function setObjectWrapper(WrapperInterface $object_wrapper)
    {
        $this->object_wrapper = $object_wrapper;
    }

    /**
     * @param WrapperInterface $array_wrapper
     */
    public function setArrayWrapper(WrapperInterface $array_wrapper)
    {
        $this->array_wrapper = $array_wrapper;
    }
}
