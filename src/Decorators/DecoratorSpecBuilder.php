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


namespace Pinepain\JsSandbox\Decorators;


use Pinepain\JsSandbox\Specs\Builder\ArgumentValueBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\ArgumentValueBuilderException;


class DecoratorSpecBuilder implements DecoratorSpecBuilderInterface
{
    private $regexp = '/
        ^
        \@(?<name>[a-z_]+(?:[\w-]*\w)?)
        \s*
        (?:
            \(
            \s*
            (?<params>
                (
                    (?:[^\'\"\(\)\,\s]+)        # literal
                    |
                    (?:[+-]?[0-9]+\.?[0-9]*)    # numbers (no exponential notation)
                    |
                    (?:\'[^\']*\')              # single-quoted string
                    |
                    (?:\"[^\"]*\")              # double-quoted string
                    |
                    (?:\[\s*\])                 # empty array
                    |
                    (?:\{\s*\})                 # empty object
                    |
                    true | false | null
                )(?:\s*\,\s*((?-3))*)*
            )?
            \s*
            \)
        )?
        \s*
        $
        /xi';
    /**
     * @var ArgumentValueBuilderInterface
     */
    private $argument;

    /**
     * @param ArgumentValueBuilderInterface $argument
     */
    public function __construct(ArgumentValueBuilderInterface $argument)
    {
        $this->argument = $argument;
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $definition): DecoratorSpecInterface
    {
        $definition = trim($definition);

        if (!$definition) {
            throw new DecoratorSpecBuilderException('Definition must be non-empty string');
        }

        try {
            if (preg_match($this->regexp, $definition, $matches)) {

                $params    = array_slice($matches, 5);
                $decorator = $this->buildDecorator($matches['name'], $params);

                return $decorator;
            }
        } catch (ArgumentValueBuilderException $e) {
            // We don't care about what specific issue we hit inside,
            // for API user it means that the definition is invalid
        }

        throw new DecoratorSpecBuilderException("Unable to parse definition: '{$definition}'");
    }

    /**
     * @param string $name
     * @param array $raw_args
     *
     * @return DecoratorSpecInterface
     */
    protected function buildDecorator(string $name, array $raw_args): DecoratorSpecInterface
    {
        $arguments = [];
        foreach ($raw_args as $raw_arg) {
            $arguments[] = $this->argument->build($raw_arg, true);
        }

        return new DecoratorSpec($name, $arguments);
    }
}
