<?php declare(strict_types=1);
/**
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


namespace Pinepain\JsSandbox\Extractors\PlainExtractors;


use DateTime;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\PlainExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\ExtractorInterface;
use Pinepain\JsSandbox\Extractors\ObjectComponents\ExtractorsObjectStoreInterface;
use UnexpectedValueException;
use V8\Context;
use V8\Isolate;
use V8\NumberValue;
use V8\ObjectValue;


class NativeObjectExtractorTest extends TestCase
{
    /**
     * @var ExtractorsObjectStoreInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $object_store;
    /**
     * @var Isolate
     */
    private $isolate;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->object_store = $this->getMockForAbstractClass(ExtractorsObjectStoreInterface::class);
        $this->extractor    = $this->getMockForAbstractClass(ExtractorInterface::class);

        $this->isolate = new Isolate();
        $this->context = new Context($this->isolate);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorException
     * @expectedExceptionMessage Value must be of the type object to be able to find instance, number given
     */
    public function testNonObject()
    {
        $e = new NativeObjectExtractor($this->object_store);

        $scalar = new NumberValue($this->isolate, 42);

        $definition = $this->getMockForAbstractClass(PlainExtractorDefinitionInterface::class);

        $e->extract($this->context, $scalar, $definition, $this->extractor);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorException
     * @expectedExceptionMessage Unable to find bound native object
     */
    public function testNotMatching()
    {
        $obj        = new ObjectValue($this->context);
        $definition = $this->getMockForAbstractClass(PlainExtractorDefinitionInterface::class);

        $this->object_store->expects($this->any())
                           ->method('get')
                           ->willThrowException(new UnexpectedValueException());

        $e = new NativeObjectExtractor($this->object_store);

        $e->extract($this->context, $obj, $definition, $this->extractor);
    }

    public function testWithoutNext()
    {
        $obj   = new ObjectValue($this->context);
        $proto = $this->createMock(DateTime::class);

        $this->object_store->expects($this->any())
                           ->method('get')
                           ->willReturn($proto);

        $e = new NativeObjectExtractor($this->object_store);

        $definition = $this->getMockForAbstractClass(PlainExtractorDefinitionInterface::class);

        $definition->expects($this->any())
                   ->method('getNext')
                   ->willReturn(null);

        $res = $e->extract($this->context, $obj, $definition, $this->extractor);

        $this->assertSame($proto, $res);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorException
     * @expectedExceptionMessage Native object value constraint failed: value is not an instance of given classes/interfaces
     */
    public function testNoVariations()
    {
        $obj   = new ObjectValue($this->context);
        $proto = $this->createMock(DateTime::class);

        $this->object_store->expects($this->any())
                           ->method('get')
                           ->willReturn($proto);

        $e = new NativeObjectExtractor($this->object_store);

        $next = $this->getMockForAbstractClass(ExtractorDefinitionInterface::class);
        $next->expects($this->any())
             ->method('getVariations')
             ->willReturn([]);

        $definition = $this->getMockForAbstractClass(PlainExtractorDefinitionInterface::class);

        $definition->expects($this->any())
                   ->method('getNext')
                   ->willReturn($next);

        $e->extract($this->context, $obj, $definition, $this->extractor);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorException
     * @expectedExceptionMessage Native object value constraint failed: value is not an instance of given classes/interfaces
     */
    public function testWithoutMatchingVariations()
    {
        $obj   = new ObjectValue($this->context);
        $proto = $this->createMock(DateTime::class);

        $this->object_store->expects($this->any())
                           ->method('get')
                           ->willReturn($proto);

        $e = new NativeObjectExtractor($this->object_store);

        $variation = $this->getMockForAbstractClass(ExtractorDefinitionInterface::class);
        $variation->method('getName')
                  ->willReturn(self::class);

        $next = $this->getMockForAbstractClass(ExtractorDefinitionInterface::class);
        $next->method('getVariations')
             ->willReturn([$variation]);

        $definition = $this->getMockForAbstractClass(PlainExtractorDefinitionInterface::class);

        $definition->method('getNext')
                   ->willReturn($next);

        $res = $e->extract($this->context, $obj, $definition, $this->extractor);
        $this->assertSame($proto, $res);
    }


    public function testWithVariations()
    {
        $obj   = new ObjectValue($this->context);
        $proto = $this->createMock(DateTime::class);

        $this->object_store->expects($this->any())
                           ->method('get')
                           ->willReturn($proto);

        $e = new NativeObjectExtractor($this->object_store);

        $variation = $this->getMockForAbstractClass(ExtractorDefinitionInterface::class);
        $variation->method('getName')
                  ->willReturn(DateTime::class);

        $next = $this->getMockForAbstractClass(ExtractorDefinitionInterface::class);
        $next->method('getVariations')
             ->willReturn([$variation]);

        $definition = $this->getMockForAbstractClass(PlainExtractorDefinitionInterface::class);

        $definition->method('getNext')
                   ->willReturn($next);

        $res = $e->extract($this->context, $obj, $definition, $this->extractor);
        $this->assertSame($proto, $res);
    }

}
