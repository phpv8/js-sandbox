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


namespace Pinepain\JsSandbox\Tests\NativeWrappers;


use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Pinepain\JsSandbox\NativeWrappers\NativeObjectWrapper;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\Context;
use V8\Isolate;
use V8\ObjectValue;
use V8\StringValue;
use V8\UndefinedValue;


class NativeObjectWrapperTest extends TestCase
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
     * @var ObjectValue
     */
    private $target;
    /**
     * @var WrapperInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $wrapper;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->isolate = new Isolate();
        $this->context = new Context($this->isolate);
        $this->target  = new ObjectValue($this->context);

        $this->wrapper = $this->createPartialMock(WrapperInterface::class, ['wrap']);
    }


    public function testGetSetHasDelete()
    {
        $value   = 'test value';
        $wrapped = new StringValue($this->isolate, 'wrapped ' . $value);

        $this->wrapper->expects($this->once())
                      ->method('wrap')
                      ->with($this->isolate, $this->context, $value)
                      ->willReturn($wrapped);

        $obj = new NativeObjectWrapper($this->isolate, $this->context, $this->target, $this->wrapper);

        $key = 'test key';

        $this->assertFalse($obj->has($key));
        $obj->set($key, $value);
        $this->assertTrue($obj->has($key));

        $this->assertInstanceOf(StringValue::class, $obj->get($key));

        $this->assertSame($wrapped->value(), $obj->get($key)->value());

        $this->assertTrue($obj->delete($key));
        $this->assertFalse($obj->has($key));
        $this->assertInstanceOf(UndefinedValue::class, $obj->get($key));
    }
}
