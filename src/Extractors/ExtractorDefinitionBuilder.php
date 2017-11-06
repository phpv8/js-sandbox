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


namespace Pinepain\JsSandbox\Extractors;


use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\PlainExtractorDefinition;
use Pinepain\JsSandbox\Extractors\Definition\PlainExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\RecursiveExtractorDefinition;
use Pinepain\JsSandbox\Extractors\Definition\VariableExtractorDefinition;


class ExtractorDefinitionBuilder implements ExtractorDefinitionBuilderInterface
{
    /**
     * @var string
     */
    protected $type_regexp = '/
    ^
    (
        (?<name>
            [-_\w]*
        )
        (?:
            \s*
            (?<group>
                \(
                \s*
                (?<param>
                    (?-4)*
                    |
                    [\w\\\\]+
                )
                \s*
                \)
            )
        )?
        (?:
            \s*
            (?<arr>(?:\s*\[\s*\]\s*)+)
        )?
        (?:
            \s*
            \|
            \s*
            (?<alt>(?-6))
        )?
    )
    $
    /xi';

    /**
     * {@inheritdoc}
     */
    public function build(string $definition): ExtractorDefinitionInterface
    {
        $definition = trim($definition);

        if (!$definition) {
            throw new ExtractorDefinitionBuilderException('Definition must be non-empty string');
        }

        try {
            if (preg_match($this->type_regexp, $definition, $matches)) {
                $extractor = $this->buildExtractor($matches['name'], $matches['param'] ?? '', $matches['alt'] ?? '', $this->getDepth($matches), $this->hasGroups($matches));

                return $extractor;
            }
        } catch (ExtractorDefinitionBuilderException $e) {
            // We don't care about what specific issue we hit inside,
            // for API user it means that the definition is invalid
        }

        throw new ExtractorDefinitionBuilderException("Unable to parse definition: '{$definition}'");
    }

    /**
     * @param string $name
     * @param null|string $param
     * @param null|string $alt_definitions
     * @param int $depth
     * @param bool $groups
     *
     * @return null|ExtractorDefinitionInterface
     * @throws ExtractorDefinitionBuilderException
     */
    protected function buildExtractor(string $name, string $param, string $alt_definitions, int $depth, bool $groups): ExtractorDefinitionInterface
    {
        $next = null;

        if ('' !== $param && preg_match($this->type_regexp, $param, $matches)) {
            $next = $this->buildExtractor($matches['name'], $matches['param'] ?? '', $matches['alt'] ?? '', $this->getDepth($matches), $this->hasGroups($matches));
        }

        if ($name) {
            $definition = $this->buildPlainExtractor($name, $next);
        } else {
            $definition = $next;
        }

        if ($depth > 0) {
            $definition = $this->buildArrayDefinition($definition, $depth, $groups);
        }

        if (!$definition) {
            throw new ExtractorDefinitionBuilderException('Empty group is not allowed');
        }

        if ('' !== $alt_definitions) {
            $definition = $this->buildVariableDefinition($definition, $alt_definitions);
        }

        return $definition;
    }

    /**
     * @param PlainExtractorDefinitionInterface $definition
     * @param string $alt_definitions
     *
     * @return VariableExtractorDefinition
     * @throws ExtractorDefinitionBuilderException
     */
    protected function buildVariableDefinition(PlainExtractorDefinitionInterface $definition, string $alt_definitions): VariableExtractorDefinition
    {
        $alt = [$definition];

        while ('' !== $alt_definitions && preg_match($this->type_regexp, $alt_definitions, $matches)) {
            // build alt
            $alt[] = $this->buildExtractor($matches['name'], $matches['param'] ?? '', '', $this->getDepth($matches), $this->hasGroups($matches));

            $alt_definitions = trim($matches['alt'] ?? '');
        }

        if ('' !== $alt_definitions) {
            // UNEXPECTED
            // this should not be possible, but just in case we will ever get here
            throw new ExtractorDefinitionBuilderException('Invalid varying definition');
        }

        return new VariableExtractorDefinition(...$alt);
    }

    /**
     * @param null|ExtractorDefinitionInterface $definition
     * @param int $depth
     * @param bool $groups
     *
     * @return ExtractorDefinitionInterface
     * @throws ExtractorDefinitionBuilderException
     */
    protected function buildArrayDefinition(?ExtractorDefinitionInterface $definition, int $depth, bool $groups): ExtractorDefinitionInterface
    {
        // special case for blank brackets [] which should be the same as any[]
        if (!$definition) {
            if ($groups) {
                throw new ExtractorDefinitionBuilderException('Empty group is not allowed');
            }

            $definition = $this->buildPlainExtractor('any');
        }

        while ($depth) {
            $depth--;
            // arrayed definition
            $definition = $this->buildPlainExtractor('[]', $definition);
        }

        return $definition;
    }

    private function getDepth(array $matches): int
    {
        if (!isset($matches['arr']) || '' === $matches['arr']) {
            return 0;
        }

        return substr_count($matches['arr'], '[');
    }

    private function hasGroups(array $matches): bool
    {
        return isset($matches['group']) && '' !== $matches['group'];
    }

    private function buildPlainExtractor(string $name, ?ExtractorDefinitionInterface $next = null): PlainExtractorDefinitionInterface
    {
        if ('any' === $name && !$next) {
            return new RecursiveExtractorDefinition($name);
        }

        return new PlainExtractorDefinition($name, $next);
    }
}
