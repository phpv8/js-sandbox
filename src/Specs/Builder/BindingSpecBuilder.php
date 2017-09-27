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


use Pinepain\JsSandbox\Specs\BindingSpec;
use Pinepain\JsSandbox\Specs\BindingSpecInterface;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\BindingSpecBuilderException;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\FunctionSpecBuilderException;
use Pinepain\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException;


class BindingSpecBuilder implements BindingSpecBuilderInterface
{
    /**
     * @var PropertySpecBuilderInterface
     */
    private $property_spec_builder;
    /**
     * @var FunctionSpecBuilderInterface
     */
    private $function_spec_builder;

    /**
     * @param PropertySpecBuilderInterface $property_spec_builder
     * @param FunctionSpecBuilderInterface $function_spec_builder
     */
    public function __construct(PropertySpecBuilderInterface $property_spec_builder, FunctionSpecBuilderInterface $function_spec_builder)
    {
        $this->property_spec_builder = $property_spec_builder;
        $this->function_spec_builder = $function_spec_builder;
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $definition): BindingSpecInterface
    {
        $definition = trim($definition);

        if (!$definition) {
            throw new BindingSpecBuilderException('Definition must be non-empty string');
        }

        if (preg_match('/^(?<name>\w+)\s*=>\s*(?<variation>.+)$/', $definition, $matches)) {
            /** @var PropertySpecBuilderInterface|FunctionSpecBuilderInterface $builder */
            foreach ([$this->property_spec_builder, $this->function_spec_builder] as $builder) {
                try {
                    $spec = $builder->build($matches['variation']);

                    return new BindingSpec($matches['name'], $spec);
                } catch (FunctionSpecBuilderException | PropertySpecBuilderException $e) {
                    // continue
                }
            }

            throw new BindingSpecBuilderException("Unable to extract spec from definition: '{$definition}'");
        }

        throw new BindingSpecBuilderException("Unable to parse definition: '{$definition}'");
    }
}
