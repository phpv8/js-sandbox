<?php declare(strict_types=1);

/**
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


namespace Pinepain\JsSandbox\Tests\Wrappers\FunctionComponents\Runtime;


use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Pinepain\JsSandbox\NativeWrappers\NativeFunctionWrapperInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\Runtime\ExecutionContext;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunctionInterface;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\Context;
use V8\FunctionCallbackInfo;
use V8\FunctionObject;
use V8\Isolate;
use V8\ObjectValue;


class ExecutionContextTest extends TestCase
{
    /**
     * @var WrapperInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $wrapper;
    /**
     * @var RuntimeFunctionInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $runtime_function;
    /**
     * @var FunctionCallbackInfo|PHPUnit_Framework_MockObject_MockObject
     */
    private $args;
    /**
     * @var FunctionSpecInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $spec;
    /**
     * @var Isolate
     */
    private $isolate;
    /**
     * @var Context
     */
    private $context;

    public function setUp()
    {
        $this->wrapper          = $this->getMockBuilder(WrapperInterface::class)->getMockForAbstractClass();
        $this->runtime_function = $this->getMockBuilder(RuntimeFunctionInterface::class)->getMockForAbstractClass();
        $this->args             = $this->getMockBuilder(FunctionCallbackInfo::class)
                                       ->setMethods(['getIsolate', 'getContext'])
                                       ->getMockForAbstractClass();

        $this->isolate = new Isolate();
        $this->context = new Context($this->isolate);

        $this->args->expects($this->any())
                   ->method('getIsolate')
                   ->willReturn($this->isolate);

        $this->args->expects($this->any())
                   ->method('getContext')
                   ->willReturn($this->context);

        $this->spec = $this->getMockBuilder(FunctionSpecInterface::class)->getMockForAbstractClass();
    }

    public function testDelegatedGetters()
    {
        $isolate  = new Isolate();
        $context  = new Context($isolate);
        $this_obj = new ObjectValue($context);

        $this->args = $this->getMockBuilder(FunctionCallbackInfo::class)
                           ->setMethods(['getIsolate', 'getContext', 'this'])
                           ->getMockForAbstractClass();

        $this->args->expects($this->any())
                   ->method('getIsolate')
                   ->willReturn($isolate);

        $this->args->expects($this->any())
                   ->method('getContext')
                   ->willReturn($context);

        $this->args->expects($this->any())
                   ->method('this')
                   ->willReturn($this_obj);

        $exec = new ExecutionContext($this->wrapper, $this->runtime_function, $this->args, $this->spec);

        $this->assertSame($isolate, $exec->getIsolate());
        $this->assertSame($context, $exec->getContext());
        $this->assertSame($this_obj, $exec->getThis());
    }

    public function testGetters()
    {
        $exec = new ExecutionContext($this->wrapper, $this->runtime_function, $this->args, $this->spec);

        $this->assertSame($this->wrapper, $exec->getWrapper());
        $this->assertSame($this->runtime_function, $exec->getRuntimeFunction());
        $this->assertSame($this->args, $exec->getFunctionCallbackInfo());
        $this->assertSame($this->spec, $exec->getFunctionSpec());
    }

    public function testWrapping()
    {
        $value   = 'test string';
        $wrapped = $this->getMockBuilder(FunctionObject::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->wrapper = $this->getMockBuilder(WrapperInterface::class)
                              ->setMethods(['wrap'])
                              ->getMockForAbstractClass();

        $this->wrapper->expects($this->once())
                      ->method('wrap')
                      ->with($this->isolate, $this->context, $value)
                      ->willReturn($wrapped);

        $exec = new ExecutionContext($this->wrapper, $this->runtime_function, $this->args, $this->spec);

        $this->assertSame($wrapped, $exec->wrap($value));


        $this->wrapper = $this->getMockBuilder(WrapperInterface::class)
                              ->setMethods(['wrap'])
                              ->getMockForAbstractClass();

        $this->wrapper->expects($this->once())
                      ->method('wrap')
                      ->with($this->isolate, $this->context, $this->runtime_function)
                      ->willReturn($wrapped);

        $exec = new ExecutionContext($this->wrapper, $this->runtime_function, $this->args, $this->spec);

        $this->assertSame($wrapped, $exec->getFunctionObject());
    }

    public function testWrapNativeFunction()
    {
        /** @var ObjectValue|PHPUnit_Framework_MockObject_MockObject $recv */
        $recv = $this->getMockBuilder(ObjectValue::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        /** @var FunctionObject|PHPUnit_Framework_MockObject_MockObject $target */
        $target = $this->getMockBuilder(FunctionObject::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $exec = new ExecutionContext($this->wrapper, $this->runtime_function, $this->args, $this->spec);

        $wrapper = $exec->wrapNativeFunction($recv, $target);

        $this->assertInstanceOf(NativeFunctionWrapperInterface::class, $wrapper);
    }
}
