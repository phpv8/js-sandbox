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


namespace PhpV8\JsSandbox\Extractors\PlainExtractors;


use DateTime;
use PhpV8\JsSandbox\Extractors\Definition\PlainExtractorDefinitionInterface;
use PhpV8\JsSandbox\Extractors\ExtractorException;
use PhpV8\JsSandbox\Extractors\ExtractorInterface;
use V8\Context;
use V8\DateObject;
use V8\Value;


class DateTimeExtractor implements PlainExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Context $context, Value $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor)
    {
        if ($value instanceof DateObject) {
            return new DateTime($value->valueOf() / 1000);
        }

        throw new ExtractorException('Value must be of the type date, ' . $value->typeOf()->value() . ' given');
    }
}
