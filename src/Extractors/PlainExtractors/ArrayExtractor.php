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


namespace Pinepain\JsSandbox\Extractors\PlainExtractors;


use Pinepain\JsSandbox\Extractors\Definition\PlainExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\ExtractorException;
use Pinepain\JsSandbox\Extractors\ExtractorInterface;
use V8\ArrayObject;
use V8\Context;
use V8\Value;


class ArrayExtractor implements PlainExtractorInterface
{
    /**
     * @var AssocExtractor
     */
    private $extractor;

    /**
     * @inheritDoc
     */
    public function __construct(AssocExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Context $context, Value $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor)
    {
        if ($value instanceof ArrayObject) {
            return $this->extractor->extract($context, $value, $definition, $extractor);
        }

        throw new ExtractorException('Value must be of array type, ' . $value->typeOf()->value() . ' given instead');
    }
}
