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
            (?<type>(\w*(?:\(.*\))?(?:\[\s*\])?)(?:\s*\|\s*(?-1))*)
            \s*
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

            $this->validateDefinition($definition, $matches);

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
        return new VariadicParameterSpec($matches['name'], $this->builder->build($matches['type']));
    }

    protected function buildOptionalParameterSpec(array $matches, ?string $default): OptionalParameterSpec
    {
        if (null !== $default) {
            $default = $this->buildDefaultValue($matches['default']);
        }

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
            // UNEXPECTED
            // Less likely we will ever get here because it should fail at a parsing step, but just in case
            throw new ParameterSpecBuilderException("Unknown default value format '{$definition}'");
        }

        if ($this->wrappedWith($definition, '[', ']')) {
            return [];
        }

        if ($this->wrappedWith($definition, '{', '}')) {
            return [];
        }

        foreach (['"', "'"] as $quote) {
            if ($this->wrappedWith($definition, $quote, $quote)) {
                return trim($definition, $quote);
            }
        }

        // UNEXPECTED
        // Less likely we will ever get here because it should fail at a parsing step, but just in case
        throw new ParameterSpecBuilderException("Unknown default value format '{$definition}'");
    }

    private function wrappedWith(string $definition, string $starts, $ends)
    {
        assert(strlen($definition) >= 2);

        return $starts == $definition[0] && $ends == $definition[-1];
    }

    protected function validateDefinition(string $definition, array $matches): void
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
}
