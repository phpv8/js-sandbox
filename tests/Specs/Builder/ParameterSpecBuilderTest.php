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


namespace PhpV8\JsSandbox\Tests\Specs\Builder;


use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use PhpV8\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use PhpV8\JsSandbox\Extractors\ExtractorDefinitionBuilderException;
use PhpV8\JsSandbox\Extractors\ExtractorDefinitionBuilderInterface;
use PhpV8\JsSandbox\Specs\Builder\ArgumentValueBuilderInterface;
use PhpV8\JsSandbox\Specs\Builder\Exceptions\ArgumentValueBuilderException;
use PhpV8\JsSandbox\Specs\Builder\ParameterSpecBuilder;
use PhpV8\JsSandbox\Specs\Builder\ParameterSpecBuilderInterface;
use PhpV8\JsSandbox\Specs\Parameters\MandatoryParameterSpec;
use PhpV8\JsSandbox\Specs\Parameters\OptionalParameterSpec;
use PhpV8\JsSandbox\Specs\Parameters\VariadicParameterSpec;


class ParameterSpecBuilderTest extends TestCase
{
    /**
     * @var ParameterSpecBuilderInterface
     */
    protected $builder;

    /**
     * @var ArgumentValueBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $argument_builder;

    /**
     * @var ExtractorDefinitionBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $definition_builder;

    public function setUp()
    {
        $this->argument_builder   = $this->getMockForAbstractClass(ArgumentValueBuilderInterface::class);
        $this->definition_builder = $this->getMockForAbstractClass(ExtractorDefinitionBuilderInterface::class);

        $this->builder = new ParameterSpecBuilder($this->definition_builder, $this->argument_builder);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Definition must be non-empty string
     */
    public function testBuildingFromEmptyStringShouldThrow()
    {
        $this->builder->build('');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Unable to parse definition: '!invalid!'
     */
    public function testBuildingFromInvalidStringShouldThrow()
    {
        $this->builder->build('!invalid!');
    }

    public function testBuildingMandatoryParameterWithoutType()
    {
        $this->extractorDefinitionShouldBuildOn('any');

        $spec = $this->builder->build('param');

        $this->assertInstanceOf(MandatoryParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingMandatoryParameter()
    {
        $this->extractorDefinitionShouldBuildOn('type');

        $spec = $this->builder->build('param: type');

        $this->assertInstanceOf(MandatoryParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingMandatoryParameterWithDashedType()
    {
        $this->extractorDefinitionShouldBuildOn('type-dash');

        $spec = $this->builder->build('param: type-dash');

        $this->assertInstanceOf(MandatoryParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingMandatoryParameterWithComplexType()
    {
        $this->extractorDefinitionShouldBuildOn('instance( Some\Class )');

        $spec = $this->builder->build('param : instance( Some\Class )');

        $this->assertInstanceOf(MandatoryParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingMandatoryParameterWithVaryingType()
    {
        $this->extractorDefinitionShouldBuildOn('foo|bar');

        $spec = $this->builder->build('param: foo|bar');

        $this->assertInstanceOf(MandatoryParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingOptionalParameter()
    {
        $this->argumentDefinitionShouldBuildOn('"default"');

        $spec = $this->builder->build('param = "default"');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame('"default"', $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Unknown or unsupported default value format '"throw"'
     */
    public function testBuildingOptionalParameterShouldThrowOnInvalidArgumentValue()
    {
        $this->argumentDefinitionShouldThrowOn('"throw"');

        $this->builder->build('param = "throw"');
    }


    public function testBuildingVariadicParameter()
    {
        $this->extractorDefinitionShouldBuildOn('type');

        $spec = $this->builder->build('...param: type');

        $this->assertInstanceOf(VariadicParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Variadic parameter could have no default value
     */
    public function testBuildingVariadicParameterWithDefaultValueShouldThrowException()
    {
        $this->builder->build('...param = []: type');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Variadic parameter could not be nullable
     */
    public function testBuildingVariadicParameterWithNullableShouldThrowException()
    {
        $this->builder->build('...param?: type');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Nullable parameter could not have default value
     */
    public function testBuildingNullableParameterWithDefaultValueShouldThrowException()
    {
        $this->builder->build('param? = "default": type');
    }

    public function testBuildingNullableParameter()
    {
        $this->extractorDefinitionShouldBuildOn('null|type');

        $spec = $this->builder->build('param? : type');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertNull($spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingParameterWithArrayTypeGuessing()
    {
        $this->argumentDefinitionShouldBuildOn('[]');
        $this->extractorDefinitionShouldBuildOn('[]');

        $spec = $this->builder->build('param = []');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame([], $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingParameterWithBoolTrueTypeGuessing()
    {
        $this->argumentDefinitionShouldBuildOn('true');
        $this->extractorDefinitionShouldBuildOn('bool');

        $spec = $this->builder->build('param = true');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame(true, $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingParameterWithBoolFalseTypeGuessing()
    {
        $this->argumentDefinitionShouldBuildOn('false');
        $this->extractorDefinitionShouldBuildOn('bool');

        $spec = $this->builder->build('param = false');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame(false, $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingParameterWithNullableTypeGuessing()
    {
        $this->argumentDefinitionShouldBuildOn('null');
        $this->extractorDefinitionShouldBuildOn('null|any');

        $spec = $this->builder->build('param?');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame(null, $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingParameterWithDefaultNullTypeGuessing()
    {
        $this->argumentDefinitionShouldBuildOn('null');
        $this->extractorDefinitionShouldBuildOn('any');

        $spec = $this->builder->build('param = null');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame(null, $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingParameterWithDefaultIntNumberTypeGuessing()
    {
        $this->argumentDefinitionShouldBuildOn('123');
        $this->extractorDefinitionShouldBuildOn('number');

        $spec = $this->builder->build('param = 123');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertEquals(123, $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingParameterWithDefaultFloatNumberTypeGuessing()
    {
        $this->argumentDefinitionShouldBuildOn('123.42');
        $this->extractorDefinitionShouldBuildOn('number');

        $spec = $this->builder->build('param = 123.42');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame(123.42, $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingParameterWithDefaultStringTypeGuessing()
    {
        $this->argumentDefinitionShouldBuildOn('"test"');
        $this->extractorDefinitionShouldBuildOn('string');

        $spec = $this->builder->build('param = "test"');

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame('"test"', $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Unable to parse definition because of extractor failure: ExtractorDefinitionBuilder exception for testing
     */
    public function testBuildingWhenExtractorFailsShouldAlsoFail()
    {
        $this->extractorDefinitionShouldThrowOn('fail');

        $this->builder->build('param :fail');
    }

    protected function argumentDefinitionShouldBuildOn($name)
    {
        $retval = $name;

        if ('[]' == $name) {
            $retval = [];
        }

        if ('true' === $name) {
            $retval = true;
        }

        if ('false' === $name) {
            $retval = false;
        }

        if (is_numeric($name)) {
            $retval = is_int($name) ? (int)$name : (float) $name;
        }

        if ('null' === $name) {
            $retval = null;
        }

        $this->argument_builder->method('build')
                               ->with($name, false)
                               ->willReturn($retval);
    }

    protected function argumentDefinitionShouldThrowOn($name)
    {
        $this->argument_builder->method('build')
                               ->with($name, false)
                               ->willThrowException(new ArgumentValueBuilderException('ArgumentValueBuilderException exception for testing'));
    }

    protected function extractorDefinitionShouldBuildOn($name)
    {
        $this->definition_builder->method('build')
                                 ->with($name)
                                 ->willReturn($this->getMockForAbstractClass(ExtractorDefinitionInterface::class));
    }

    protected function extractorDefinitionShouldThrowOn($name)
    {
        $this->definition_builder->method('build')
                                 ->with($name)
                                 ->willThrowException(new ExtractorDefinitionBuilderException('ExtractorDefinitionBuilder exception for testing'));
    }

}
