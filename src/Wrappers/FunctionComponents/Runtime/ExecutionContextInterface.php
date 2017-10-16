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


use Pinepain\JsSandbox\NativeWrappers\NativeFunctionWrapperInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunctionInterface;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\Context;
use V8\FunctionCallbackInfo;
use V8\FunctionObject;
use V8\Isolate;
use V8\ObjectValue;


interface ExecutionContextInterface
{
    public function getIsolate(): Isolate;

    public function getContext(): Context;

    public function getThis(): ObjectValue;

    public function getWrapper(): WrapperInterface;

    public function getRuntimeFunction(): RuntimeFunctionInterface;

    public function getFunctionCallbackInfo(): FunctionCallbackInfo;

    public function getFunctionSpec(): FunctionSpecInterface;

    public function getFunctionObject(): FunctionObject;

    public function wrap($value);

    public function wrapNativeFunction(ObjectValue $recv, FunctionObject $function_object): NativeFunctionWrapperInterface;
}




