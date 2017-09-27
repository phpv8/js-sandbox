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
use V8\IntegerValue;
use V8\ObjectValue;
use V8\Value;


class AssocExtractor implements PlainExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Context $context, Value $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor)
    {
        if ($value instanceof ObjectValue) {
            $own_properties = $value->getOwnPropertyNames($context);

            $length  = $own_properties->length();
            $isolate = $context->getIsolate();

            $out = [];

            $next = $definition->getNext();

            for ($i = 0; $i < $length; $i++) {
                /** @var \V8\PrimitiveValue $prop */
                $prop = $own_properties->get($context, new IntegerValue($isolate, $i));
                $item = $value->get($context, $prop);

                $prop_name = $prop->value();

                if ($next) {
                    try {
                        $out[$prop_name] = $extractor->extract($context, $item, $definition);
                    } catch (ExtractorException $e) {
                        throw new ExtractorException("Failed to convert assoc item #{$prop_name}: " . $e->getMessage());
                    }
                } else {
                    $out[$prop_name] = $item;
                }
            }

            return $out;

        }

        throw new ExtractorException('Value must be of object type, ' . $value->typeOf()->value() . ' given instead');
    }
}
