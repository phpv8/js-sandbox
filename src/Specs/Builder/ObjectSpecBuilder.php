<?php declare(strict_types=1);

/*
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


namespace PhpV8\JsSandbox\Specs\Builder;


use PhpV8\JsSandbox\Specs\BindingSpecInterface;
use PhpV8\JsSandbox\Specs\Builder\Exceptions\BindingSpecBuilderException;
use PhpV8\JsSandbox\Specs\Builder\Exceptions\FunctionSpecBuilderException;
use PhpV8\JsSandbox\Specs\Builder\Exceptions\ObjectSpecBuilderException;
use PhpV8\JsSandbox\Specs\Builder\Exceptions\PropertySpecBuilderException;
use PhpV8\JsSandbox\Specs\FunctionSpecInterface;
use PhpV8\JsSandbox\Specs\PropertySpecInterface;


class ObjectSpecBuilder implements ObjectSpecBuilderInterface
{
    /**
     * @var PropertySpecBuilderInterface
     */
    private $property;
    /**
     * @var FunctionSpecBuilderInterface
     */
    private $function;
    /**
     * @var BindingSpecBuilderInterface
     */
    private $binding;

    public function __construct(PropertySpecBuilderInterface $property, FunctionSpecBuilderInterface $function, BindingSpecBuilderInterface $binding)
    {
        $this->property = $property;
        $this->function = $function;
        $this->binding  = $binding;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $definitions): array
    {
        $out = [];

        foreach ($definitions as $name => $definition) {
            if (!is_string($definition)) {
                $out[$name] = $definition;
                continue;
            }

            $out[$name] = $this->buildConcreteDefinition($name, $definition);
        }

        return $out;
    }

    /**
     * @param string $name
     * @param string $definition
     *
     * @return BindingSpecInterface|FunctionSpecInterface|PropertySpecInterface
     * @throws ObjectSpecBuilderException
     */
    protected function buildConcreteDefinition(string $name, string $definition)
    {
        try {
            return $this->property->build($definition);
        } catch (PropertySpecBuilderException $e) {
            //
        }

        try {
            return $this->function->build($definition);
        } catch (FunctionSpecBuilderException $e) {
            //
        }

        try {
            return $this->binding->build($definition);
        } catch (BindingSpecBuilderException $e) {
            //
        }

        throw new ObjectSpecBuilderException("Unable to build spec for '{$name}' from definition '{$definition}'");
    }
}
