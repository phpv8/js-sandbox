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
use V8\RegExpObject;
use V8\Value;


class RegExpExtractor implements PlainExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Context $context, Value $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor)
    {
        if ($value instanceof RegExpObject) {

            $regex = $value->getSource()->value();

            if ($definition->getNext() && 'string' == $definition->getNext()->getName()) {
                return $regex;
            }

            $flags  = $value->getFlags();
            $regexp = '/' . preg_quote($regex, '/') . '/';

            if (!$flags) {
                return $regexp;
            }

            if ($flags & RegExpObject::FLAG_GLOBAL) {
                // global flag is not supported in PHP
                throw new ExtractorException('Global flag is not supported');
            }

            if ($flags & RegExpObject::FLAG_IGNORE_CASE) {
                $regexp .= 'i';
            }

            if ($flags & RegExpObject::FLAG_MULTILINE) {
                $regexp .= 'm';
            }

            if ($flags & RegExpObject::FLAG_STICKY) {
                // sticky flag is not supported in PHP
                throw new ExtractorException('Sticky flag is not supported');
            }

            if ($flags & RegExpObject::FLAG_UNICODE) {
                $regexp .= 'u';
            }

            if ($flags & RegExpObject::FLAG_DOTALL) {
                $regexp .= 's';
            }

            return $regexp;
        }

        throw new ExtractorException('Value must be of the type regexp, ' . $value->typeOf()->value() . ' given');
    }
}
