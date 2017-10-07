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
use Pinepain\JsSandbox\Extractors\Definition\VariableExtractorDefinition;


class ExtractorDefinitionBuilder implements ExtractorDefinitionBuilderInterface
{
    /**
     * @var string
     */
    protected $type_regexp = '/^((?<name>[-_\w]+)(?:\s*\(\s*(?<param>(?-3)*|[\w\\\\]+)\s*\))?(?:\s*\|\s*(?<alt>(?-4)))?)$/';

    /**
     * {@inheritdoc}
     */
    public function build(string $definition): ExtractorDefinitionInterface
    {
        $definition = trim($definition);

        if (!$definition) {
            throw new ExtractorDefinitionBuilderException('Definition must be non-empty string');
        }

        if (preg_match($this->type_regexp, $definition, $matches)) {
            return $this->buildExtractor($matches['name'], $matches['param'] ?? null, $matches['alt'] ?? null);
        }

        throw new ExtractorDefinitionBuilderException("Unable to parse definition: '{$definition}'");
    }

    /**
     * @param string      $name
     * @param null|string $param
     * @param null|string $alt_definitions
     *
     * @return ExtractorDefinitionInterface
     * @throws ExtractorDefinitionBuilderException
     */
    protected function buildExtractor(string $name, ?string $param, ?string $alt_definitions): ExtractorDefinitionInterface
    {
        $next = null;

        if ($param && preg_match($this->type_regexp, $param, $matches)) {
            $next = $this->buildExtractor($matches['name'], $matches['param'] ?? null, $matches['alt'] ?? null);
        }

        $definition = new PlainExtractorDefinition($name, $next);

        if ($alt_definitions) {
            $definition = $this->buildVariableDefinition($definition, $alt_definitions);
        }

        return $definition;
    }

    /**
     * @param PlainExtractorDefinitionInterface $definition
     * @param string                            $alt_definitions
     *
     * @return VariableExtractorDefinition
     * @throws ExtractorDefinitionBuilderException
     */
    protected function buildVariableDefinition(PlainExtractorDefinitionInterface $definition, string $alt_definitions): VariableExtractorDefinition
    {
        $alt = [$definition];

        while ($alt_definitions && preg_match($this->type_regexp, $alt_definitions, $matches)) {
            // build alt
            $alt[] = $this->buildExtractor($matches['name'], $matches['param'] ?? null, null);

            $alt_definitions = $matches['alt'] ?? null;
        }

        if ($alt_definitions) {
            // this should not be possible, but just in case we will ever get here
            throw new ExtractorDefinitionBuilderException('Invalid varying definition');
        }

        return new VariableExtractorDefinition(...$alt);
    }

}
