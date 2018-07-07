<?php declare(strict_types=1);


namespace PhpV8\JsSandbox\Tests\Specs\Builder;


use PHPUnit\Framework\TestCase;
use PhpV8\JsSandbox\Specs\Builder\ArgumentValueBuilder;


class ArgumentValueBuilderTest extends TestCase
{
    /**
     * @param string $raw
     * @param        $expected
     *
     * @dataProvider provideValidValues
     */
    public function testBuildingValidWithoutLiteral(string $raw, $expected)
    {
        $builder = new ArgumentValueBuilder();

        $value = $builder->build($raw, false);
        $this->assertSame($expected, $value);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ArgumentValueBuilderException
     * @expectedExceptionMessage Unknown value format 'garbage'
     */
    public function testBuildingInvalidWithoutLiteralShouldThrow()
    {
        $builder = new ArgumentValueBuilder();

        $builder->build('garbage', false);
    }

    public function testBuildingInvalidWithLiteralShouldReturnStringifiedLiteral()
    {
        $builder = new ArgumentValueBuilder();

        $value = $builder->build('garbage', true);
        $this->assertSame('garbage', $value);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Specs\Builder\Exceptions\ArgumentValueBuilderException
     * @expectedExceptionMessage Unknown value format 'x'
     */
    public function testBuildingInvalidShortShouldThrow()
    {
        $builder = new ArgumentValueBuilder();

        $builder->build('x', false);
    }

    public function provideValidValues()
    {
        return [
            ['42', 42],
            ['-1', -1],
            ['-1.0', -1.0],
            ['1.2345', 1.2345],
            ['[]', []],
            ['[ ]', []],
            ['{}', []],
            ['{ }', []],
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
}
