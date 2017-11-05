<?php declare(strict_types=1);


namespace Pinepain\JsSandbox\Tests\Extractors;


use PHPUnit\Framework\TestCase;
use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\PlainExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\VariableExtractorDefinition;
use Pinepain\JsSandbox\Extractors\Definition\VariableExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilder;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderInterface;
use UnexpectedValueException;


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

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderException
     * @expectedExceptionMessage Unable to parse definition: '()'
     */
    public function testBuildingFromEmptyGroupShouldThrowException()
    {
        $this->builder->build('()');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderException
     * @expectedExceptionMessage Unable to parse definition: '()[]'
     */
    public function testBuildingEmptyGroupArrayedDefinitionShouldFail()
    {
        $this->builder->build('()[]');
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderException
     * @expectedExceptionMessage Unable to parse definition: '((()))[]'
     */
    public function testBuildingEmptyGroupArrayedDefinitionWithNestedGroups()
    {
        $this->builder->build('((()))[]');
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

    public function testBuildingEmptyArrayDefinition()
    {
        $definition = $this->builder->build('[]');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('[]', $definition->getName());
        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition->getNext());
        $this->assertSame('any', $definition->getNext()->getName());
        $this->assertCount(1, $definition->getVariations());
        $this->assertSame($definition, $definition->getVariations()[0]);
    }

    public function testBuildingEmptyArrayWithNestedEmptyArrayDefinition()
    {
        $definition = $this->builder->build('[][]');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('[]', $definition->getName());
        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition->getNext());
        $this->assertCount(1, $definition->getVariations());
        $this->assertSame($definition, $definition->getVariations()[0]);

        $next = $definition->getNext();

        $this->assertSame('[]', $next->getName());
        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $next->getNext());
        $this->assertSame('any', $next->getNext()->getName());
        $this->assertCount(1, $next->getVariations());
        $this->assertSame($next, $next->getVariations()[0]);
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

    public function testBuildingPlainDefinitionArrayed()
    {
        $definition = $this->builder->build('test[]');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('[]', $definition->getName());
        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition->getNext());
        $this->assertCount(1, $definition->getVariations());
        $this->assertSame($definition, $definition->getVariations()[0]);

        $next = $definition->getNext();

        $this->assertSame('test', $next->getName());
        $this->assertNull($next->getNext());
        $this->assertCount(1, $next->getVariations());
        $this->assertSame($next, $next->getVariations()[0]);
    }

    public function testBuildingPlainGroupedDefinition()
    {
        $definition = $this->builder->build('(test)');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('test', $definition->getName());
        $this->assertNull($definition->getNext());
        $this->assertCount(1, $definition->getVariations());
    }

    public function testBuildingVariableDefinitionGrouped()
    {
        $definition = $this->builder->build('(test|alternative|definition)');

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

    public function testBuildingVariableDefinitionGroupedAndArrayed()
    {
        $definition = $this->builder->build('(test|alternative|definition)[]');

        $this->assertInstanceOf(PlainExtractorDefinitionInterface::class, $definition);

        $this->assertSame('[]', $definition->getName());
        $this->assertInstanceOf(VariableExtractorDefinition::class, $definition->getNext());
        $this->assertCount(1, $definition->getVariations());

        $definition = $definition->getNext();

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

    public function testBuildingGroupedAndNested()
    {
        $definition = $this->builder->build('(foo(bar))');

        $str = $this->stringifyDefinition($definition);

        $this->assertSame('foo(bar)', $str);
    }

    public function testBuildingGroupedAndNestedAndVariableComplex()
    {
        $definition = $this->builder->build('(foo(bar)|(bar(baz)|baz(bar(foo))))');

        $str = $this->stringifyDefinition($definition);

        $this->assertSame('foo(bar)|bar(baz)|baz(bar(foo))', $str);
    }

    public function testBuildingGroupedAndNestedAndVariableComplexArrayed()
    {
        $definition = $this->builder->build('(foo(bar)|(bar(baz)|baz(bar(foo))[]))');

        $str = $this->stringifyDefinition($definition);

        $this->assertSame('foo(bar)|bar(baz)|baz(bar(foo))[]', $str);
    }


    protected function stringifyDefinition(ExtractorDefinitionInterface $definition)
    {
        if ($definition instanceof PlainExtractorDefinitionInterface) {
            // build plain

            $name = $definition->getName();

            if ('[]' == $name) {
                return $this->stringifyDefinition($definition->getNext()) . $name;
            } elseif ($definition->getNext()) {
                return $name . '(' . $this->stringifyDefinition($definition->getNext()) . ')';
            }

            return $name;
        } elseif ($definition instanceof VariableExtractorDefinitionInterface) {
            $variations = [];
            foreach ($definition->getVariations() as $v) {
                $variations[] = $this->stringifyDefinition($v);
            }

            return implode('|', $variations);
        } else {
            throw new UnexpectedValueException('Unexpected value type');
        }
    }
}
