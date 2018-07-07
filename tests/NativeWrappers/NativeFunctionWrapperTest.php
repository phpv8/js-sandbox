<?php declare(strict_types=1);

/*
 * This file is part of the pinepain/js-sandbox PHP library.
 *
 * Copyright (c) 2016-2017 Bogdan Padalko <thepinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace PhpV8\JsSandbox\Tests\NativeWrappers;


use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use PhpV8\JsSandbox\NativeWrappers\NativeFunctionWrapper;
use PhpV8\JsSandbox\Wrappers\WrapperInterface;
use V8\Context;
use V8\FunctionCallbackInfo;
use V8\FunctionObject;
use V8\Isolate;
use V8\ObjectValue;
use V8\RegExpObject;
use V8\StringValue;


class NativeFunctionWrapperTest extends TestCase
{
    /**
     * @var Isolate
     */
    private $isolate;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var FunctionObject
     */
    private $target;
    /**
     * @var WrapperInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $wrapper;
    /**
     * @var ObjectValue
     */
    private $recv;
    /**
     * @var int
     */
    private $invoked;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->isolate = new Isolate();
        $this->context = new Context($this->isolate);
        $this->target  = new FunctionObject($this->context, function (FunctionCallbackInfo $args) {
            $this->invoked = $args->arguments();
        });

        $this->wrapper = $this->createPartialMock(WrapperInterface::class, ['wrap']);
        $this->recv    = $this->context->globalObject();
    }

    public function testCall()
    {
        $obj = new NativeFunctionWrapper($this->isolate, $this->context, $this->target, $this->wrapper, $this->recv);

        $this->assertNull($this->invoked);
        $obj->call();
        $this->assertSame([], $this->invoked);

        $args    = ['foo', 'bar'];
        $wrapped = [
            new ObjectValue($this->context),
            new RegExpObject($this->context, new StringValue($this->isolate, 'foo.+bar')),
        ];

        $this->wrapper->expects($this->exactly(2))
                      ->method('wrap')
                      ->withConsecutive([$this->isolate, $this->context, $args[0]], [$this->isolate, $this->context, $args[1]])
                      ->willReturnOnConsecutiveCalls($wrapped[0], $wrapped[1]);


        $obj->call(...$args);

        $this->assertSame($wrapped, $this->invoked);
    }
}
