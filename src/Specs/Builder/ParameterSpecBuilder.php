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
use Pinepain\JsSandbox\Specs\Builder\Exceptions\ArgumentValueBuilderException;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\ParameterSpecBuilderException;
use Pinepain\JsSandbox\Specs\Parameters\MandatoryParameterSpec;
use Pinepain\JsSandbox\Specs\Parameters\OptionalParameterSpec;
use Pinepain\JsSandbox\Specs\Parameters\ParameterSpecInterface;
use Pinepain\JsSandbox\Specs\Parameters\VariadicParameterSpec;


class ParameterSpecBuilder implements ParameterSpecBuilderInterface
{
    protected $regexp = '/
        ^
        (?:
            (?<rest>\.{3})
            \s*
        )?
        (?<name>[_a-z]\w*)
        \s*
        (?<nullable>\?)?
        \s*
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
                (?:\{\s*\})                 # empty object
                |
                true | false | null
            )
            \s*
        )?
        (?:
            \s*
            \:
            \s*
            (?<type>([\w\-]*(?:\(.*\))?(?:\[\s*\])?)(?:\s*\|\s*(?-1))*)
            \s*
        )?
        $
        /xi';
    /**
     * @var ExtractorDefinitionBuilderInterface
     */
    private $extractor;
    /**
     * @var ArgumentValueBuilderInterface
     */
    private $argument;

    /**
     * @param ExtractorDefinitionBuilderInterface $extractor
     * @param ArgumentValueBuilderInterface $argument
     */
    public function __construct(ExtractorDefinitionBuilderInterface $extractor, ArgumentValueBuilderInterface $argument)
    {
        $this->extractor = $extractor;
        $this->argument  = $argument;
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

            $matches = $this->prepareDefinition($matches);

            try {
                if ($this->hasRest($matches)) {
                    return $this->buildVariadicParameterSpec($matches);
                }

                if ($this->hasDefault($matches)) {
                    return $this->buildOptionalParameterSpec($matches, $matches['default']);
                }

                if ($this->hasNullable($matches)) {
                    return $this->buildOptionalParameterSpec($matches, null);
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
        return new VariadicParameterSpec($matches['name'], $this->extractor->build($matches['type']));
    }

    protected function buildOptionalParameterSpec(array $matches, ?string $default): OptionalParameterSpec
    {
        if (null !== $default) {
            $default_definition = $matches['default'];
            try {
                $default = $this->argument->build($default_definition, false);
            } catch (ArgumentValueBuilderException $e) {
                throw new ParameterSpecBuilderException("Unknown or unsupported default value format '{$default_definition}'");
            }

            if (!$this->hasType($matches)) {
                $matches['type'] = $this->guessTypeFromDefault($default);
            }
        }

        if ($this->hasNullable($matches)) {
            // nullable means that null is a valid value and thus we should explicitly enable null extractor here
            $matches['type'] = 'null|' . $matches['type'];
        }

        return new OptionalParameterSpec($matches['name'], $this->extractor->build($matches['type']), $default);
    }

    protected function buildMandatoryParameterSpec(array $matches): MandatoryParameterSpec
    {
        return new MandatoryParameterSpec($matches['name'], $this->extractor->build($matches['type']));
    }

    protected function prepareDefinition(array $matches): array
    {
        if ($this->hasNullable($matches) && $this->hasRest($matches)) {
            throw new ParameterSpecBuilderException("Variadic parameter could not be nullable");
        }

        if ($this->hasNullable($matches) && $this->hasDefault($matches)) {
            throw new ParameterSpecBuilderException("Nullable parameter could not have default value");
        }

        if ($this->hasRest($matches) && $this->hasDefault($matches)) {
            throw new ParameterSpecBuilderException('Variadic parameter could have no default value');
        }

        if (!$this->hasDefault($matches) && !$this->hasType($matches)) {
            // special case when no default value set and no type provided
            $matches['type'] = 'any';
        }

        return $matches;
    }

    private function hasType(array $matches): bool
    {
        return isset($matches['type']) && '' !== $matches['type'];
    }

    private function hasNullable(array $matches): bool
    {
        return isset($matches['nullable']) && '' !== $matches['nullable'];
    }

    private function hasRest(array $matches): bool
    {
        return isset($matches['rest']) && '' !== $matches['rest'];
    }

    private function hasDefault(array $matches): bool
    {
        return isset($matches['default']) && '' !== $matches['default'];
    }

    private function guessTypeFromDefault($default): string
    {
        if (is_array($default)) {
            return '[]';
        }

        if (is_numeric($default)) {
            return 'number';
        }

        if (is_bool($default)) {
            return 'bool';
        }

        if (is_string($default)) {
            return 'string';
        }

        // it looks like we have nullable parameter which could be anything

        return 'any';
    }
}
