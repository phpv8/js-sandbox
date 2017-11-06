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
use V8\IntegerValue;
use V8\ObjectValue;
use V8\Value;


class AssocExtractor implements PlainExtractorInterface
{
    /**
     * @var bool
     */
    private $array_with_props;

    public function __construct(bool $array_with_props = true)
    {
        $this->array_with_props = $array_with_props;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Context $context, Value $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor)
    {
        if ($value instanceof ArrayObject) {
            $items = $this->extractArrayValues($context, $value, $definition, $extractor);

            if (!$this->array_with_props) {
                return $items;
            }

            $props = $this->extractObjectValues($context, $value, $definition, $extractor);

            // length is a built-in property which we are not interested here
            unset($props['length']);

            return array_merge($items, $props);
        }

        if ($value instanceof ObjectValue) {
            return $this->extractObjectValues($context, $value, $definition, $extractor);
        }

        throw new ExtractorException('Value must be of array or object type, ' . $value->typeOf()->value() . ' given instead');
    }

    /**
     * @param Context $context
     * @param ArrayObject $value
     * @param PlainExtractorDefinitionInterface $definition
     * @param ExtractorInterface $extractor
     *
     * @return array
     * @throws ExtractorException
     */
    protected function extractArrayValues(Context $context, ArrayObject $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor): array
    {
        $out     = [];
        $length  = $value->length();
        $isolate = $context->getIsolate();

        $next = $definition->getNext();

        for ($i = 0; $i < $length; $i++) {
            $item = $value->get($context, new IntegerValue($isolate, $i));

            if ($next) {
                try {
                    $out[] = $extractor->extract($context, $item, $next);
                } catch (ExtractorException $e) {
                    throw new ExtractorException("Failed to convert array item #{$i}: " . $e->getMessage());
                }
            } else {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * @param Context $context
     * @param ObjectValue $value
     * @param PlainExtractorDefinitionInterface $definition
     * @param ExtractorInterface $extractor
     *
     * @return array
     * @throws ExtractorException
     */
    protected function extractObjectValues(Context $context, ObjectValue $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor): array
    {
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
                    $out[$prop_name] = $extractor->extract($context, $item, $next);
                } catch (ExtractorException $e) {
                    throw new ExtractorException("Failed to convert array item #{$prop_name}: " . $e->getMessage());
                }
            } else {
                $out[$prop_name] = $item;
            }
        }

        return $out;
    }

}
