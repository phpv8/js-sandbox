<?php declare(strict_types=1);


namespace Pinepain\JsSandbox\Tests\Specs\Builder;


use PHPUnit_Framework_MockObject_MockObject;
use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderException;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\ParameterSpecBuilder;
use PHPUnit\Framework\TestCase;
use Pinepain\JsSandbox\Specs\Builder\ParameterSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Parameters\MandatoryParameterSpec;
use Pinepain\JsSandbox\Specs\Parameters\OptionalParameterSpec;
use Pinepain\JsSandbox\Specs\Parameters\VariadicParameterSpec;


class ParameterSpecBuilderTest extends TestCase
{
    /**
     * @var ParameterSpecBuilderInterface
     */
    protected $builder;

    /**
     * @var ExtractorDefinitionBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $definition_builder;

    public function setUp()
    {
        $this->definition_builder = $this->getMockForAbstractClass(ExtractorDefinitionBuilderInterface::class);

        $this->builder = new ParameterSpecBuilder($this->definition_builder);
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

    public function testBuildingMandatoryParameter()
    {
        $this->extractorDefinitionShouldBuildOn('type');

        $spec = $this->builder->build('type param');

        $this->assertInstanceOf(MandatoryParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingMandatoryParameterWithComplexType()
    {
        $this->extractorDefinitionShouldBuildOn('instance( Some\Class )');

        $spec = $this->builder->build('instance( Some\Class ) param');

        $this->assertInstanceOf(MandatoryParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingMandatoryParameterWithVaryingType()
    {
        $this->extractorDefinitionShouldBuildOn('foo|bar');

        $spec = $this->builder->build('foo|bar param');

        $this->assertInstanceOf(MandatoryParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    /**
     * @param string $raw_default
     * @param $expected_default
     *
     * @dataProvider provideValidDefaultValues
     */
    public function testBuildingOptionalParameter(string $raw_default, $expected_default)
    {
        $this->extractorDefinitionShouldBuildOn('type');

        $spec = $this->builder->build('type param = ' . $raw_default);

        $this->assertInstanceOf(OptionalParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertSame($expected_default, $spec->getDefaultValue());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    public function testBuildingVariadicParameter()
    {
        $this->extractorDefinitionShouldBuildOn('type');

        $spec = $this->builder->build('...type param');

        $this->assertInstanceOf(VariadicParameterSpec::class, $spec);

        $this->assertSame('param', $spec->getName());
        $this->assertInstanceOf(ExtractorDefinitionInterface::class, $spec->getExtractorDefinition());
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Variadic parameter should have no default value
     */
    public function testBuildingVariadicParameterWithDefaultValueShouldThrowException()
    {
        $this->builder->build('...type param = []');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException
     * @expectedExceptionMessage Unable to parse definition because of extractor failure: ExtractorDefinitionBuilder exception for testing
     */
    public function testBuildingWhenExtractorFailsShouldAlsoFail()
    {
        $this->extractorDefinitionShouldThrowOn('fail');

        $this->builder->build('fail param');
    }


    public function provideValidDefaultValues()
    {
        return [
            ['42', 42],
            ['-1', -1],
            ['-1.0', -1.0],
            ['1.2345', 1.2345],
            ['[]', []],
            ['[ ]', []],
            ['null', null],
            ['Null', null],
            ['NulL', null],
            ['true', true],
            ['True', true],
            ['TruE', true],
            ['false', false],
            ['False', false],
            ['FalsE', false],
            ['"string"', 'string'],
            ['"StrInG"', 'StrInG'],
            ["'string'", 'string'],
            ["'StrInG'", 'StrInG'],
            ['"str\'ing"', 'str\'ing'],
            ["'str\"ing'", "str\"ing"],
            ["' string '", ' string '],
            ['" string "', ' string '],
            ["''", ''],
            ['""', ''],
            ["'123'", '123'],
            ['"123"', '123'],
            ["'-123.456'", '-123.456'],
            ['"-123.456"', '-123.456'],
        ];
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
