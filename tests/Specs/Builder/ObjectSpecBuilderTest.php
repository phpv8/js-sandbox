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


namespace Pinepain\JsSandbox\Tests\Specs\Builder;


use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Pinepain\JsSandbox\Specs\BindingSpecInterface;
use Pinepain\JsSandbox\Specs\Builder\BindingSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\BindingSpecBuilderException;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\FunctionSpecBuilderException;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException;
use Pinepain\JsSandbox\Specs\Builder\FunctionSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\ObjectSpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\ObjectSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\PropertySpecBuilderInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Specs\PropertySpecInterface;


class ObjectSpecBuilderTest extends TestCase
{
    /**
     * @var ObjectSpecBuilderInterface
     */
    protected $builder;

    /**
     * @var PropertySpecBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $property_spec_builder;
    /**
     * @var FunctionSpecBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $function_spec_builder;
    /**
     * @var BindingSpecBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $binding_spec_builder;

    /**
     *
     */
    public function setUp()
    {
        $this->property_spec_builder = $this->getMockForAbstractClass(PropertySpecBuilderInterface::class);
        $this->function_spec_builder = $this->getMockForAbstractClass(FunctionSpecBuilderInterface::class);
        $this->binding_spec_builder  = $this->getMockForAbstractClass(BindingSpecBuilderInterface::class);

        $this->builder = new ObjectSpecBuilder($this->property_spec_builder, $this->function_spec_builder, $this->binding_spec_builder);
    }

    public function testShouldSkipNonStringDefinitions()
    {
        $props = $this->builder->build(['test' => []]);

        $this->assertCount(1, $props);
        $this->assertArrayHasKey('test', $props);
        $this->assertSame([], $props['test']);
    }

    public function testBuildingProperty()
    {
        $this->propertySpecShouldBuildOn('test prop');

        $props = $this->builder->build(['test' => 'test prop']);

        $this->assertCount(1, $props);
        $this->assertArrayHasKey('test', $props);
        $this->assertInstanceOf(PropertySpecInterface::class, $props['test']);
    }

    public function testBuildingFunction()
    {
        $this->propertySpecShouldThrowOn('(test param)');
        $this->functionSpecShouldBuildOn('(test param)');

        $props = $this->builder->build(['test' => '(test param)']);

        $this->assertCount(1, $props);
        $this->assertArrayHasKey('test', $props);
        $this->assertInstanceOf(FunctionSpecInterface::class, $props['test']);
    }

    public function testBuildingBinding()
    {
        $this->propertySpecShouldThrowOn('binding => to param');
        $this->functionSpecShouldThrowOn('binding => to param');
        $this->bindingSpecShouldBuildOn('binding => to param');

        $props = $this->builder->build(['test' => 'binding => to param']);

        $this->assertCount(1, $props);
        $this->assertArrayHasKey('test', $props);
        $this->assertInstanceOf(BindingSpecInterface::class, $props['test']);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ObjectSpecBuilderException
     * @expectedExceptionMessage Unable to build spec for 'test' from definition 'unknown'
     */
    public function testBuildingUnknown()
    {
        $this->propertySpecShouldThrowOn('unknown');
        $this->functionSpecShouldThrowOn('unknown');
        $this->bindingSpecShouldThrowOn('unknown');

        $this->builder->build(['test' => 'unknown']);
    }


    protected function propertySpecShouldBuildOn($name)
    {
        $this->property_spec_builder->method('build')
                                    ->with($name)
                                    ->willReturn($this->getMockForAbstractClass(PropertySpecInterface::class));
    }

    protected function propertySpecShouldThrowOn($name)
    {
        $this->property_spec_builder->method('build')
                                    ->with($name)
                                    ->willThrowException(new PropertySpecBuilderException('PropertySpecBuilder exception for testing'));
    }

    protected function functionSpecShouldBuildOn($name)
    {
        $this->function_spec_builder->method('build')
                                    ->with($name)
                                    ->willReturn($this->getMockForAbstractClass(FunctionSpecInterface::class));
    }

    protected function functionSpecShouldThrowOn($name)
    {
        $this->function_spec_builder->method('build')
                                    ->with($name)
                                    ->willThrowException(new FunctionSpecBuilderException('FunctionSpecBuilder exception for testing'));
    }

    protected function bindingSpecShouldBuildOn($name)
    {
        $this->binding_spec_builder->method('build')
                                   ->with($name)
                                   ->willReturn($this->getMockForAbstractClass(BindingSpecInterface::class));
    }

    protected function bindingSpecShouldThrowOn($name)
    {
        $this->binding_spec_builder->method('build')
                                   ->with($name)
                                   ->willThrowException(new BindingSpecBuilderException('BindingSpecBuilder exception for testing'));
    }

}
