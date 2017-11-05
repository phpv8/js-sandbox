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


namespace Pinepain\JsSandbox\Tests\Specs\Builder;


use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Pinepain\JsSandbox\Specs\Builder\FunctionSpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\FunctionSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\ParameterSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Specs\Parameters\ParameterSpecInterface;
use Pinepain\JsSandbox\Specs\ReturnSpec\AnyReturnSpec;
use Pinepain\JsSandbox\Specs\ReturnSpec\VoidReturnSpec;


class FunctionSpecBuilderTest extends TestCase
{
    /**
     * @var FunctionSpecBuilderInterface
     */
    protected $builder;

    /**
     * @var ParameterSpecBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $parameters_builder;

    public function setUp()
    {
        $this->parameters_builder = $this->getMockForAbstractClass(ParameterSpecBuilderInterface::class);

        $this->builder = new FunctionSpecBuilder($this->parameters_builder);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\FunctionSpecBuilderException
     * @expectedExceptionMessage Definition must be non-empty string
     */
    public function testBuildingFromEmptyStringShouldThrow()
    {
        $this->builder->build('');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\FunctionSpecBuilderException
     * @expectedExceptionMessage Unable to parse definition: 'invalid'
     */
    public function testBuildingFromInvalidStringShouldThrow()
    {
        $this->builder->build('invalid');
    }

    public function testBuildEmptySpec()
    {
        $spec = $this->builder->build('()');
        $this->assertFalse($spec->needsExecutionContext());
        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);
    }

    public function testBuildSpecThatNeedsExecutionContext()
    {
        $spec = $this->builder->build('!()');
        $this->assertTrue($spec->needsExecutionContext());
        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);
    }


    // Test return type
    public function testBuildingSpecWithVoidReturnType()
    {
        $spec = $this->builder->build('void ()');

        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);
        $this->assertFalse($spec->needsExecutionContext());
        $this->assertInstanceOf(VoidReturnSpec::class, $spec->getReturn());
    }

    public function testBuildingSpecWithAnyReturnType()
    {
        $spec = $this->builder->build('any ()');

        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);
        $this->assertFalse($spec->needsExecutionContext());
        $this->assertInstanceOf(AnyReturnSpec::class, $spec->getReturn());
    }

    public function testBuildingSpecWithNoReturnTypeIsTheSameAsWithAnyReturnType()
    {
        $spec = $this->builder->build('()');

        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);
        $this->assertFalse($spec->needsExecutionContext());
        $this->assertInstanceOf(AnyReturnSpec::class, $spec->getReturn());
    }

    public function testBuildSpecWithParams()
    {
        $this->parameterSpecBuilderShouldBuildOn('one: param', 'two = "default": param', '...params: rest');

        $spec = $this->builder->build('(one: param, two = "default": param, ...params: rest)');

        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);
        $this->assertFalse($spec->needsExecutionContext());
        $this->assertContainsOnlyInstancesOf(ParameterSpecInterface::class, $spec->getParameters()->getParameters());
        $this->assertCount(3, $spec->getParameters()->getParameters());
    }

    public function testBuildSpecWithNullableParams()
    {
        $this->parameterSpecBuilderShouldBuildOn('one: param', 'two?: param');

        $spec = $this->builder->build('(one: param, two?: param)');

        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);
        $this->assertFalse($spec->needsExecutionContext());
        $this->assertContainsOnlyInstancesOf(ParameterSpecInterface::class, $spec->getParameters()->getParameters());
        $this->assertCount(2, $spec->getParameters()->getParameters());
    }

    // Test throws spec
    public function testBuildingSpecWithoutThrows()
    {
        $spec = $this->builder->build('()');

        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);

        $this->assertFalse($spec->needsExecutionContext());
        $this->assertEmpty($spec->getExceptions()->getThrowSpecs());
    }

    public function testBuildingSpecWithSingleThrows()
    {
        $spec = $this->builder->build('() Test');

        $this->assertInstanceOf(FunctionSpecInterface::class, $spec);
        $this->assertFalse($spec->needsExecutionContext());
        $this->assertCount(1, $spec->getExceptions()->getThrowSpecs());
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\FunctionSpecBuilderException
     * @expectedExceptionMessage Invalid return type: 'invalid'
     */
    public function testBuildingSpecWithInvalidReturnTypeStringShouldThrow()
    {
        $this->builder->build('invalid ()');
    }

    protected function parameterSpecBuilderShouldBuildOn(string ...$definitions)
    {
        $map = [];

        foreach ($definitions as $definition) {
            $spec = $this->getMockForAbstractClass(ParameterSpecInterface::class);

            $map[] = [$definition, $spec];
        }

        $this->parameters_builder->method('build')
                                 ->willReturnMap($map);
    }
}
