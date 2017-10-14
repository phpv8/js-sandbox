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


use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\Context;
use V8\FunctionObject;
use V8\Isolate;
use V8\ObjectValue;
use V8\StringValue;
use V8\Value;


class NativeObjectWrapper implements NativeObjectWrapperInterface
{
    /**
     * @var Isolate
     */
    protected $isolate;
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var ObjectValue|FunctionObject
     */
    protected $target;
    /**
     * @var WrapperInterface
     */
    protected $wrapper;

    /**
     * @inheritDoc
     */
    public function __construct(Isolate $isolate, Context $context, ObjectValue $target, WrapperInterface $wrapper)
    {
        $this->isolate = $isolate;
        $this->context = $context;
        $this->target  = $target;
        $this->wrapper = $wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value): void
    {
        $wrapped_key   = $this->wrapKey($key);
        $wrapped_value = $this->wrapper->wrap($this->isolate, $this->context, $value);

        $this->target->set($this->context, $wrapped_key, $wrapped_value);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key): Value
    {
        $wrapped_key = $this->wrapKey($key);

        $value = $this->target->get($this->context, $wrapped_key);

        // TODO: could we extract PHP value from the native v8 value?
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $wrapped_key = $this->wrapKey($key);

        return $this->target->has($this->context, $wrapped_key);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        $wrapped_key = $this->wrapKey($key);

        return $this->target->delete($this->context, $wrapped_key);
    }

    protected function wrapKey(string $key): StringValue
    {
        return new StringValue($this->isolate, $key);
    }
}
