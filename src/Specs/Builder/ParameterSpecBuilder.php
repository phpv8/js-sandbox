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


namespace Pinepain\JsSandbox\Specs\Builder;


use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderException;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException;
use Pinepain\JsSandbox\Specs\Parameters\MandatoryParameterSpec;
use Pinepain\JsSandbox\Specs\Parameters\OptionalParameterSpec;
use Pinepain\JsSandbox\Specs\Parameters\ParameterSpecInterface;
use Pinepain\JsSandbox\Specs\Parameters\VariadicParameterSpec;
use function strlen;


class ParameterSpecBuilder implements ParameterSpecBuilderInterface
{
    protected $regexp = '/
        ^
        (?:
            (?<rest>\.{3})
            \s*
        )?
        (?:
            (?<type>(\w+\b(?:\(.*\))?)(?:\s*\|\s*(?-1))*)
            \s*
        )
        (?<name>[_a-z]\w*)
        (?:
            \s* = \s*
            (?<default>
                (?:[+-]?[0-9]+\.?[0-9]*)    # numbers (no exponential notation)
                |
                (?:\\\'[^\\\']*\\\')        # single-quoted string
                |
                (?:\"[^\"]*\")              # double-quoted string
                |
                (?:\[\s*\])                 # empty array
                |
                true | false | null
            )
        )?
        $
        /xi';
    /**
     * @var ExtractorDefinitionBuilderInterface
     */
    private $builder;

    public function __construct(ExtractorDefinitionBuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param string $definition
     *
     * @return ParameterSpecInterface
     * @throws ParameterSpecBuilderException
     */
    public function build(string $definition): ParameterSpecInterface
    {
        $definition = trim($definition);

        if (!$definition) {
            throw new ParameterSpecBuilderException('Definition must be non-empty string');
        }

        if (preg_match($this->regexp, $definition, $matches)) {
            try {
                if ($matches['rest'] ?? false) {
                    return $this->buildVariadicParameterSpec($matches);
                }

                if ($matches['default'] ?? false) {
                    return $this->buildOptionalParameterSpec($matches);
                }

                return $this->buildMandatoryParameterSpec($matches);
            } catch (ExtractorDefinitionBuilderException $e) {
                throw new ParameterSpecBuilderException("Unable to parse definition because of extractor failure: " . $e->getMessage());
            }
        }

        throw new ParameterSpecBuilderException("Unable to parse definition: '{$definition}'");
    }

    protected function buildVariadicParameterSpec(array $matches): VariadicParameterSpec
    {
        if (isset($matches['default'])) {
            throw new ParameterSpecBuilderException('Variadic parameter should have no default value');
        }

        return new VariadicParameterSpec($matches['name'], $this->builder->build($matches['type']));
    }

    protected function buildOptionalParameterSpec(array $matches): OptionalParameterSpec
    {
        $default = $this->buildDefaultValue($matches['default']);

        return new OptionalParameterSpec($matches['name'], $this->builder->build($matches['type']), $default);
    }

    protected function buildMandatoryParameterSpec(array $matches): MandatoryParameterSpec
    {
        return new MandatoryParameterSpec($matches['name'], $this->builder->build($matches['type']));
    }

    protected function buildDefaultValue(string $definition)
    {
        if (is_numeric($definition)) {
            if (false !== strpos($definition, '.')) {
                return (float)$definition;
            }

            return (int)$definition;
        }

        switch (strtolower($definition)) {
            case 'null':
                return null;
            case 'true':
                return true;
            case 'false':
                return false;
        }

        // after this point all expected definition values MUST be at least 2 chars length

        if (strlen($definition) < 2) {
            // Less likely we will ever get here because it should fail at a parsing step, but just in case
            throw new ParameterSpecBuilderException("Unknown default value format '{$definition}'");
        }

        if ('[' == $definition[0] && ']' == $definition[-1]) {
            return [];
        }

        if ('"' == $definition[0] && '"' == $definition[-1]) {
            return trim($definition, '"');
        }
        if ("'" == $definition[0] && "'" == $definition[-1]) {
            return trim($definition, "'");
        }

        // Less likely we will ever get here because it should fail at a parsing step, but just in case
        throw new ParameterSpecBuilderException("Unknown default value format '{$definition}'");
    }
}