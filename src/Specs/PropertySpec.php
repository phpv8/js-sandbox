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


use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;


class PropertySpec implements PropertySpecInterface
{
    /**
     * @var bool
     */
    private $is_readonly;
    /**
     * @var null|ExtractorDefinitionInterface
     */
    private $extractor_definition;
    /**
     * @var null|string
     */
    private $getter;
    /**
     * @var null|string
     */
    private $setter;

    /**
     * @param bool                              $is_readonly
     * @param null|ExtractorDefinitionInterface $extractor_definition
     * @param null|string                       $getter
     * @param null|string                       $setter
     */
    public function __construct(bool $is_readonly, ?ExtractorDefinitionInterface $extractor_definition, ?string $getter = null, ?string $setter = null)
    {
        $this->is_readonly          = $is_readonly;
        $this->extractor_definition = $extractor_definition;
        $this->getter               = $getter;
        $this->setter               = $setter; // NOTE: having both $is_readonly and $setter set doesn't make much sense
    }

    /**
     * {@inheritdoc}
     */
    public function isReadonly(): bool
    {
        return $this->is_readonly;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtractorDefinition(): ?ExtractorDefinitionInterface
    {
        return $this->extractor_definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getGetterName(): ?string
    {
        return $this->getter;
    }

    /**
     * {@inheritdoc}
     */
    public function getSetterName(): ?string
    {
        return $this->setter;
    }
}
