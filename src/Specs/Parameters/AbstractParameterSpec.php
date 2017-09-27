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


namespace Pinepain\JsSandbox\Specs\Parameters;


use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use Pinepain\JsSandbox\Specs\SpecException;


abstract class AbstractParameterSpec implements ParameterSpecInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var ExtractorDefinitionInterface
     */
    private $extractor;
    /**
     * @var bool
     */
    private $variadic;
    /**
     * @var bool
     */
    private $optional;
    /**
     * @var mixed
     */
    private $default;

    /**
     * @param string                       $name
     * @param ExtractorDefinitionInterface $extractor
     * @param bool                         $variadic
     * @param bool                         $optional
     * @param mixed                        $default
     */
    public function __construct(string $name, ExtractorDefinitionInterface $extractor, bool $variadic = false, bool $optional = false, $default = null)
    {
        $this->name      = $name;
        $this->extractor = $extractor;

        // TODO: should we check arguments to not conflict, like variadic && !optional && !default?

        $this->variadic = $variadic;

        $this->optional = $optional;
        $this->default  = $default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExtractorDefinition(): ExtractorDefinitionInterface
    {
        return $this->extractor;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function getDefaultValue()
    {
        if ($this->variadic) {
            throw new SpecException('Variadic parameter cannot have a default value');
        }

        if (!$this->optional) {
            throw new SpecException('Mandatory parameter cannot have a default value');
        }

        return $this->default;
    }

    public function isVariadic(): bool
    {
        return $this->variadic;
    }
}
