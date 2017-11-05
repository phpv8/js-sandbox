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
use Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException;
use Pinepain\JsSandbox\Specs\PropertySpec;
use Pinepain\JsSandbox\Specs\PropertySpecInterface;
use function strlen;


class PropertySpecBuilder implements PropertySpecBuilderInterface
{
    /**
     * @var ExtractorDefinitionBuilderInterface
     */
    private $builder;

    public function __construct(ExtractorDefinitionBuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $definition): PropertySpecInterface
    {
        $definition = trim($definition);

        if (!$definition) {
            throw new PropertySpecBuilderException('Definition must be non-empty string');
        }

        $proto = new PropertySpecPrototype();

        if ($this->isSpecReadonly($definition)) {
            $definition = trim(substr($definition, strlen('readonly')));

            $proto->readonly = true;
        }

        try {
            $this->getSpecMethodsOrType($proto, $definition);
        } catch (ExtractorDefinitionBuilderException $e) {
            throw new PropertySpecBuilderException("Failed to build definition from '{$definition}': " . $e->getMessage());
        }

        return new PropertySpec($proto->readonly, $proto->definition, $proto->getter, $proto->setter);
    }

    /**
     * @param string $definition
     *
     * @return bool
     */
    protected function isSpecReadonly(string $definition): bool
    {
        return preg_match('/^readonly\b\s*/i', $definition) > 0;
    }

    /**
     * @param PropertySpecPrototype $proto
     * @param string                $definition
     *
     * @throws PropertySpecBuilderException
     */
    protected function getSpecMethodsOrType(PropertySpecPrototype $proto, string $definition)
    {
        if (preg_match("/^(?:get\:\s*(?<getter>\w+)\(\))(?:\s+set\:\s*(?<setter>\w+)\(\s*(?<type>.*)\s*\))?$/", $definition, $matches)) {

            $proto->getter = $matches['getter'];

            if (isset($matches['setter'])) {
                $proto->setter = $matches['setter'];

                if (!$matches['type']) {
                    throw new PropertySpecBuilderException("Setter type is missed from definition: '{$definition}'");
                }
                $proto->definition = $this->builder->build($matches['type']);
            } else {
                $proto->readonly = true;
            }

            return;
        }

        if (preg_match('/^(?<type>([\w\-]*(?:\(.*\))?(?:\[\s*\])?)(?:\s*\|\s*(?-1))*)$/', $definition, $matches)) {
            $proto->definition = $this->builder->build($matches['type']);

            return;
        }

        throw new PropertySpecBuilderException("Unable to parse definition: '{$definition}'");
    }
}
