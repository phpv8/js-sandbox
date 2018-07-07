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


namespace Pinepain\JsSandbox\Tests\Decorators;


use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Pinepain\JsSandbox\Decorators\DecoratorSpecBuilder;
use Pinepain\JsSandbox\Decorators\DecoratorSpecBuilderInterface;
use Pinepain\JsSandbox\Decorators\DecoratorSpecInterface;
use Pinepain\JsSandbox\Specs\Builder\ArgumentValueBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\ArgumentValueBuilderException;


class DecoratorDefinitionBuilderTest extends TestCase
{

    /**
     * @var DecoratorSpecBuilderInterface
     */
    protected $builder;

    /**
     * @var ArgumentValueBuilderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $argument_builder;

    public function setUp()
    {
        $this->argument_builder = $this->getMockForAbstractClass(ArgumentValueBuilderInterface::class);

        $this->builder = new DecoratorSpecBuilder($this->argument_builder);
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Decorators\DecoratorSpecBuilderException
     * @expectedExceptionMessage Definition must be non-empty string
     */
    public function testBuildShouldFailOnEmptyDefinition()
    {
        $this->builder->build('');
    }

    public function testBuild()
    {
        $this->argumentDefinitionShouldBuildOn('one', 'two');

        $res = $this->builder->build('@foo(one, two)');

        $this->assertInstanceOf(DecoratorSpecInterface::class, $res);
        $this->assertSame('foo', $res->getName());
        $this->assertSame(['one', 'two'], $res->getArguments());
    }

    public function testBuildDashedShort()
    {
        $this->argumentDefinitionShouldBuildOn('one-two');

        $res = $this->builder->build('@one-two');

        $this->assertInstanceOf(DecoratorSpecInterface::class, $res);
        $this->assertSame('one-two', $res->getName());
        $this->assertSame([], $res->getArguments());
    }

    /**
     * @expectedException \Pinepain\JsSandbox\Decorators\DecoratorSpecBuilderException
     * @expectedExceptionMessage Unable to parse definition: '@test(throw)'
     */
    public function testBuildShouldFailOnArgumentError()
    {
        $this->argumentDefinitionShouldThrowOn('throw');

        $this->builder->build('@test(throw)');
    }

    protected function argumentDefinitionShouldBuildOn(string ...$definitions)
    {
        $map = [];

        foreach ($definitions as $definition) {
            $map[] = [$definition, true, $definition];
        }

        $this->argument_builder->method('build')
                               ->willReturnMap($map);
    }

    protected function argumentDefinitionShouldThrowOn($definition)
    {
        $this->argument_builder->method('build')
                               ->with($definition, true)
                               ->willThrowException(new ArgumentValueBuilderException('ArgumentValueBuilderException exception for testing'));
    }
}
