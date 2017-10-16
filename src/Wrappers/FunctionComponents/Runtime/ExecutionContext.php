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


namespace Pinepain\JsSandbox\Wrappers\FunctionComponents\Runtime;


use Pinepain\JsSandbox\NativeWrappers\NativeFunctionWrapper;
use Pinepain\JsSandbox\NativeWrappers\NativeFunctionWrapperInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunctionInterface;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\Context;
use V8\FunctionCallbackInfo;
use V8\FunctionObject;
use V8\Isolate;
use V8\ObjectValue;


class ExecutionContext implements ExecutionContextInterface
{
    /**
     * @var WrapperInterface
     */
    private $wrapper;
    /**
     * @var RuntimeFunctionInterface
     */
    private $runtime_function;
    /**
     * @var FunctionCallbackInfo
     */
    private $args;
    /**
     * @var FunctionSpecInterface
     */
    private $spec;

    public function __construct(WrapperInterface $wrapper, RuntimeFunctionInterface $runtime_function, FunctionCallbackInfo $args, FunctionSpecInterface $spec)
    {
        $this->wrapper          = $wrapper;
        $this->runtime_function = $runtime_function;
        $this->args             = $args;
        $this->spec             = $spec;
    }

    public function getIsolate(): Isolate
    {
        return $this->args->getIsolate();
    }

    public function getContext(): Context
    {
        return $this->args->getContext();
    }

    public function getThis(): ObjectValue
    {
        return $this->args->this();
    }

    public function getWrapper(): WrapperInterface
    {
        return $this->wrapper;
    }

    public function getRuntimeFunction(): RuntimeFunctionInterface
    {
        return $this->runtime_function;
    }

    public function getFunctionCallbackInfo(): FunctionCallbackInfo
    {
        return $this->args;
    }

    public function getFunctionSpec(): FunctionSpecInterface
    {
        return $this->spec;
    }

    public function getFunctionObject(): FunctionObject
    {
        // At this time we should always have request RuntimeFunction be in cache
        return $this->wrap($this->getRuntimeFunction());
    }

    public function wrap($value)
    {
        return $this->wrapper->wrap($this->getIsolate(), $this->getContext(), $value);
    }

    public function wrapNativeFunction(ObjectValue $recv, FunctionObject $function_object): NativeFunctionWrapperInterface
    {
        return new NativeFunctionWrapper($this->getIsolate(), $this->getContext(), $function_object, $this->getWrapper(), $recv);
    }
}
