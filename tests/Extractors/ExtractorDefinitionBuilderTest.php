<?php declare(strict_types=1);


namespace Pinepain\JsSandbox\Tests\Extractors;


use Pinepain\JsSandbox\Extractors\Definition\PlainExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\VariableExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilder;
use PHPUnit\Framework\TestCase;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderInterface;


class ExtractorDefinitionBuilderTest extends TestCase
{
    /**
     * @var ExtractorDefinitionBuilderInterface
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new ExtractorDefinitionBuilder();
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderException
     * @expectedExceptionMessage Definition must be non-empty string
     */
    public function testBuildingFromEmptyStringShouldThrowException()
    {
        $this->builder->build('');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderException
     * @expectedExceptionMessage Unable to parse definition: '!invalid!'
     */
    public function testBuildingFromInvalidStringShouldThrowException()
    {
        $this->builder->build('!invalid!');
    }

    public function testBuildingPlainDefinition()
    {
        $definition = $this->builder->build('test');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('test', $definition->getName());
        $this->assertNull($definition->getNext());
        $this->assertCount(1, $definition->getVariations());
        $this->assertSame($definition, $definition->getVariations()[0]);
    }

    public function testBuildingPlainDefinitionWithEmptyNestedDefinition()
    {
        $definition = $this->builder->build('test()');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('test', $definition->getName());
        $this->assertNull($definition->getNext());
        $this->assertCount(1, $definition->getVariations());
        $this->assertSame($definition, $definition->getVariations()[0]);
    }

    public function testBuildingPlainDefinitionWithPlainNestedDefinition()
    {
        $definition = $this->builder->build('test(nested)');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('test', $definition->getName());
        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition->getNext());
        $this->assertCount(1, $definition->getVariations());
        $this->assertSame($definition, $definition->getVariations()[0]);

        $next = $definition->getNext();

        $this->assertSame('nested', $next->getName());
        $this->assertNull($next->getNext());
        $this->assertCount(1, $next->getVariations());
        $this->assertSame($next, $next->getVariations()[0]);
    }

    public function testBuildingPlainDefinitionWithMultiplePlainNestedDefinition()
    {
        $definition = $this->builder->build('test(nested(definition))');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('test', $definition->getName());
        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition->getNext());
        $this->assertCount(1, $definition->getVariations());
        $this->assertSame($definition, $definition->getVariations()[0]);

        $next = $definition->getNext();

        $this->assertSame('nested', $next->getName());
        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition->getNext());
        $this->assertCount(1, $next->getVariations());
        $this->assertSame($next, $next->getVariations()[0]);

        $deep = $next->getNext();
        $this->assertSame('definition', $deep->getName());
        $this->assertNull($deep->getNext());
        $this->assertCount(1, $deep->getVariations());
        $this->assertSame($deep, $deep->getVariations()[0]);
    }

    public function testBuildingVariableDefinition()
    {
        $definition = $this->builder->build('test|alternative|definition');

        $this->assertInstanceOf(VariableExtractorDefinitionInterface::class, $definition);

        $this->assertNull($definition->getName());
        $this->assertNull($definition->getNext());
        $this->assertCount(3, $definition->getVariations());
        $this->assertContainsOnlyInstancesOf(PlainExtractorDefinitionInterface::class, $definition->getVariations());

        $variation = $definition->getVariations()[0];
        $this->assertSame('test', $variation->getName());
        $this->assertNull($variation->getNext());
        $this->assertCount(1, $variation->getVariations());
        $this->assertSame($variation, $variation->getVariations()[0]);

        $variation = $definition->getVariations()[1];
        $this->assertSame('alternative', $variation->getName());
        $this->assertNull($variation->getNext());
        $this->assertCount(1, $variation->getVariations());
        $this->assertSame($variation, $variation->getVariations()[0]);

        $variation = $definition->getVariations()[2];
        $this->assertSame('definition', $variation->getName());
        $this->assertNull($variation->getNext());
        $this->assertCount(1, $variation->getVariations());
        $this->assertSame($variation, $variation->getVariations()[0]);
    }


    public function testBuildingVariableDefinitionWithNestedDefinition()
    {
        $definition = $this->builder->build('test(nested_test) | alternative(nested-alternative | with-variations ) | definition');

        $this->assertInstanceOf(VariableExtractorDefinitionInterface::class, $definition);

        $this->assertNull($definition->getName());
        $this->assertNull($definition->getNext());
        $this->assertCount(3, $definition->getVariations());
        $this->assertContainsOnlyInstancesOf(PlainExtractorDefinitionInterface::class, $definition->getVariations());


        $variation = $definition->getVariations()[0];
        $this->assertSame('test', $variation->getName());
        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $variation->getNext());
        $this->assertCount(1, $variation->getVariations());
        $this->assertSame($variation, $variation->getVariations()[0]);

        $next = $variation->getNext();
        $this->assertSame('nested_test', $next->getName());
        $this->assertNull($next->getNext());
        $this->assertCount(1, $next->getVariations());
        $this->assertSame($next, $next->getVariations()[0]);


        $variation = $definition->getVariations()[1];
        $this->assertSame('alternative', $variation->getName());
        $this->assertInstanceOf(VariableExtractorDefinitionInterface::class, $variation->getNext());
        $this->assertCount(1, $variation->getVariations());
        $this->assertSame($variation, $variation->getVariations()[0]);

        $next = $variation->getNext();
        $this->assertNull($next->getName());
        $this->assertNull($next->getNext());
        $this->assertCount(2, $next->getVariations());
        $this->assertContainsOnlyInstancesOf(PlainExtractorDefinitionInterface::class, $next->getVariations());
        $this->assertSame('nested-alternative', $next->getVariations()[0]->getName());
        $this->assertSame('with-variations', $next->getVariations()[1]->getName());


        $variation = $definition->getVariations()[2];
        $this->assertSame('definition', $variation->getName());
        $this->assertNull($variation->getNext());
        $this->assertCount(1, $variation->getVariations());
        $this->assertSame($variation, $variation->getVariations()[0]);
    }
}
