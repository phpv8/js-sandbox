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


namespace Pinepain\JsSandbox\Extractors\Definition;


abstract class AbstractExtractorDefinition implements ExtractorDefinitionInterface
{
    /**
     * @var null|string
     */
    private $name;
    /**
     * @var null|ExtractorDefinitionInterface
     */
    private $next;
    /**
     * @var ExtractorDefinitionInterface[]
     */
    private $variations;

    /**
     * @param null|string                       $name
     * @param null|ExtractorDefinitionInterface $next
     * @param ExtractorDefinitionInterface[]    $variations
     */
    public function __construct(?string $name, ?ExtractorDefinitionInterface $next, array $variations)
    {
        $this->name       = $name;
        $this->next       = $next;
        $this->variations = $variations;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getNext(): ?ExtractorDefinitionInterface
    {
        return $this->next;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariations(): array
    {
        return $this->variations;
    }
}
