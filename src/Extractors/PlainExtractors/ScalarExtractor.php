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
use V8\Context;
use V8\Value;


class ScalarExtractor implements PlainExtractorInterface
{
    /**
     * @var PlainExtractorInterface[]
     */
    private $scalar_extractors;

    public function __construct(PlainExtractorInterface ...$scalar_extractors)
    {
        $this->scalar_extractors = $scalar_extractors;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Context $context, Value $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor)
    {
        foreach ($this->scalar_extractors as $scalar_extractor) {
            try {
                return $scalar_extractor->extract($context, $value, $definition, $extractor);
            } catch (ExtractorException $e) {
                //
            }
        }

        throw new ExtractorException('Value must be of the type scalar or be able to be casted to scalar, ' . $value->typeOf()->value() . ' given');
    }
}
