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
use Pinepain\JsSandbox\Extractors\Definition\PlainExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\VariableExtractorDefinitionInterface;
use V8\Context;
use V8\Value;


class Extractor implements ExtractorInterface
{
    /**
     * @var ExtractorsCollectionInterface
     */
    private $extractors;

    public function __construct(ExtractorsCollectionInterface $extractors)
    {
        $this->extractors = $extractors;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Context $context, Value $value, ExtractorDefinitionInterface $definition)
    {
        if ($definition instanceof PlainExtractorDefinitionInterface) {
            return $this->extractPlain($context, $value, $definition);
        }

        if ($definition instanceof VariableExtractorDefinitionInterface) {
            return $this->extractVarying($context, $value, $definition);
        }

        throw new ExtractorException('Unknown extractor definition');
    }

    protected function extractPlain(Context $context, Value $value, PlainExtractorDefinitionInterface $definition)
    {
        $name = $definition->getName();
        assert(null !== $name);

        return $this->extractors->get($name)->extract($context, $value, $definition, $this);
    }

    protected function extractVarying(Context $context, Value $value, VariableExtractorDefinitionInterface $definition)
    {
        if (!$definition->getVariations()) {
            throw new ExtractorException('Variable extractor definition is empty');
        }

        $e = null;

        foreach ($definition->getVariations() as $variation) {
            try {
                return $this->extract($context, $value, $variation);
            } catch (ExtractorException $e) {
                continue;
            }
        }

        throw $e;
    }
}
