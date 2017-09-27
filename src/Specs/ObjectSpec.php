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


namespace Pinepain\JsSandbox\Specs;


class ObjectSpec implements ObjectSpecInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var PropertySpecInterface[]|FunctionSpecInterface[]|BindingSpecInterface[]
     */
    private $properties = [];
    /**
     * @var null|FunctionSpecInterface
     */
    private $function_spec;

    /**
     * @param string                                                                 $name
     * @param PropertySpecInterface[]|FunctionSpecInterface[]|BindingSpecInterface[] $properties
     * @param null|FunctionSpecInterface                                             $function_spec
     */
    public function __construct(string $name, array $properties, ?FunctionSpecInterface $function_spec = null)
    {
        $this->name          = $name;
        $this->properties    = $properties;
        $this->function_spec = $function_spec;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty(string $name)
    {
        return $this->properties[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionSpec(): ?FunctionSpecInterface
    {
        return $this->function_spec;
    }
}
