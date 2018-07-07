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
use PhpV8\JsSandbox\Specs\Builder\PropertySpecBuilder;
use PhpV8\JsSandbox\Specs\Builder\PropertySpecBuilderInterface;
use PhpV8\JsSandbox\Specs\PropertySpecInterface;


class PropertySpecBuilderTest extends TestCase
{
    /**
     * @var PropertySpecBuilderInterface
     */
    protected $builder;

    /**
     * @var ExtractorDefinitionBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $definition_builder;

    public function setUp()
    {
        $this->definition_builder = $this->getMockForAbstractClass(ExtractorDefinitionBuilderInterface::class);

        $this->builder = new PropertySpecBuilder($this->definition_builder);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException
     * @expectedExceptionMessage Definition must be non-empty string
     */
    public function testBuildingFromEmptyDefinitionShouldFail()
    {
        $this->builder->build('');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException
     * @expectedExceptionMessage Unable to parse definition: 'invalid('
     */
    public function testBuildingFromInvalidDefinitionShouldFail()
    {
        $this->builder->build('invalid(');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException
     * @expectedExceptionMessage Failed to build definition from 'fail': ExtractorDefinitionBuilder exception for testing
     */
    public function testBuildingTypedFailsWhenExtractorDefinitionFail()
    {
        $this->extractorDefinitionShouldThrowOn('fail');
        $this->builder->build('fail');
    }

    public function testBuildingTyped()
    {
        $this->extractorDefinitionShouldBuildOn('test');

        $spec = $this->builder->build('test');

        $this->assertInstanceOf(PropertySpecInterface::class, $spec);

        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
        $this->assertFalse($spec->isReadonly());
        $this->assertNull($spec->getGetterName());
        $this->assertNull($spec->getSetterName());
    }

    public function testBuildingTypedArray()
    {
        $this->extractorDefinitionShouldBuildOn('[]');

        $spec = $this->builder->build('[]');

        $this->assertInstanceOf(PropertySpecInterface::class, $spec);

        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
        $this->assertFalse($spec->isReadonly());
        $this->assertNull($spec->getGetterName());
        $this->assertNull($spec->getSetterName());
    }

    public function testBuildingTypedAndGroupedArray()
    {
        $this->extractorDefinitionShouldBuildOn('(foo|bar[])[]');

        $spec = $this->builder->build('(foo|bar[])[]');

        $this->assertInstanceOf(PropertySpecInterface::class, $spec);

        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
        $this->assertFalse($spec->isReadonly());
        $this->assertNull($spec->getGetterName());
        $this->assertNull($spec->getSetterName());
    }

    public function testBuildingReadonlyTyped()
    {
        $this->extractorDefinitionShouldBuildOn('test');

        $spec = $this->builder->build('readonly test');

        $this->assertInstanceOf(PropertySpecInterface::class, $spec);

        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
        $this->assertTrue($spec->isReadonly());
        $this->assertNull($spec->getGetterName());
        $this->assertNull($spec->getSetterName());
    }

    public function testBuildingFromGetter()
    {
        $spec = $this->builder->build('get: getTest()');

        $this->assertInstanceOf(PropertySpecInterface::class, $spec);

        $this->assertNull($spec->getExtractorDefinition());
        $this->assertTrue($spec->isReadonly());
        $this->assertSame('getTest', $spec->getGetterName());
        $this->assertNull($spec->getSetterName());
    }

    public function testBuildingFromGetterAndSetter()
    {
        $this->extractorDefinitionShouldBuildOn('test');

        $spec = $this->builder->build('get: getTest() set: setTest(test)');

        $this->assertInstanceOf(PropertySpecInterface::class, $spec);

        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
        $this->assertFalse($spec->isReadonly());
        $this->assertSame('getTest', $spec->getGetterName());
        $this->assertSame('setTest', $spec->getSetterName());
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException
     * @expectedExceptionMessage Setter type is missed from definition: 'get: getTest() set: setTest()'
     */
    public function testBuildingFromGetterAndSetterWithoutSetterTypeShouldFail()
    {
        $this->builder->build('get: getTest() set: setTest()');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException
     * @expectedExceptionMessage Failed to build definition from 'get: getTest() set: setTest(fail)': ExtractorDefinitionBuilder exception for testing
     */
    public function testBuildingFromGetterAndSetterShouldFailWhenDefinitionBuilderFails()
    {
        $this->extractorDefinitionShouldThrowOn('fail');

        $this->builder->build('get: getTest() set: setTest(fail)');
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
