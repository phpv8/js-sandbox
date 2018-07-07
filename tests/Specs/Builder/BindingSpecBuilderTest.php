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
use Pinepain\JsSandbox\Specs\Builder\BindingSpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\BindingSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\FunctionSpecBuilderException;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException;
use Pinepain\JsSandbox\Specs\Builder\FunctionSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\PropertySpecBuilderInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Specs\PropertySpecInterface;


class BindingSpecBuilderTest extends TestCase
{
    /**
     * @var BindingSpecBuilderInterface
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
     *
     */
    public function setUp()
    {
        $this->property_spec_builder = $this->getMockForAbstractClass(PropertySpecBuilderInterface::class);
        $this->function_spec_builder = $this->getMockForAbstractClass(FunctionSpecBuilderInterface::class);

        $this->builder = new BindingSpecBuilder($this->property_spec_builder, $this->function_spec_builder);
    }

    public function testBuildPropertySpec()
    {
        $this->function_spec_builder->method('build')
                                    ->with('property-spec-build')
                                    ->willThrowException(new PropertySpecBuilderException('FunctionSpecBuilder exception for testing'));

        $this->property_spec_builder->method('build')
                                    ->with('property-spec-build')
                                    ->willReturn($this->getMockForAbstractClass(PropertySpecInterface::class));

        $spec = $this->builder->build('alias => property-spec-build');

        $this->assertInstanceOf(BindingSpecInterface::class, $spec);

        $this->assertSame('alias', $spec->getName());
        $this->assertInstanceOf(PropertySpecInterface::class, $spec->getSpec());
    }

    public function testBuildFunctionSpec()
    {
        $this->property_spec_builder->method('build')
                                    ->with('function-spec-build')
                                    ->willThrowException(new FunctionSpecBuilderException('PropertySpecBuilder exception for testing'));

        $this->function_spec_builder->method('build')
                                    ->with('function-spec-build')
                                    ->willReturn($this->getMockForAbstractClass(FunctionSpecInterface::class));

        $spec = $this->builder->build('alias => function-spec-build');

        $this->assertInstanceOf(BindingSpecInterface::class, $spec);

        $this->assertSame('alias', $spec->getName());
        $this->assertInstanceOf(FunctionSpecInterface::class, $spec->getSpec());
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\BindingSpecBuilderException
     * @expectedExceptionMessage Definition must be non-empty string
     */
    public function testBuildingFromEmptyStringShouldThrow()
    {
        $this->builder->build('');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\BindingSpecBuilderException
     * @expectedExceptionMessage Unable to parse definition: 'invalid definition'
     */
    public function testBuildingFromInvalidStringShouldThrow()
    {
        $this->builder->build('invalid definition');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\BindingSpecBuilderException
     * @expectedExceptionMessage Unable to extract spec from definition: 'alias => unknown-spec-build'
     */
    public function testBuildFunctionSpecFailWhenNoSpecVariationFound()
    {
        $this->property_spec_builder->method('build')
                                    ->with('unknown-spec-build')
                                    ->willThrowException(new PropertySpecBuilderException('PropertySpecBuilder exception for testing'));

        $this->function_spec_builder->method('build')
                                    ->with('unknown-spec-build')
                                    ->willThrowException(new FunctionSpecBuilderException('FunctionSpecBuilder exception for testing'));

        $this->builder->build('alias => unknown-spec-build');
    }
}
