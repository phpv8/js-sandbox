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
use Pinepain\JsSandbox\Extractors\ObjectComponents\ExtractorsObjectStoreInterface;
use UnexpectedValueException;
use V8\Context;
use V8\ObjectValue;
use V8\Value;


class NativeObjectExtractor implements PlainExtractorInterface
{
    /**
     * @var ExtractorsObjectStoreInterface
     */
    private $object_store;

    public function __construct(ExtractorsObjectStoreInterface $object_store)
    {
        $this->object_store = $object_store;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Context $context, Value $value, PlainExtractorDefinitionInterface $definition, ExtractorInterface $extractor)
    {
        if ($value instanceof ObjectValue) {
            try {
                $instance = $this->object_store->get($value);

                if ($definition->getNext()) {
                    foreach ($definition->getNext()->getVariations() as $variation) {
                        if (is_a($instance, $variation->getName())) {
                            return $instance;
                        }
                    }

                    throw new ExtractorException('Native object value constraint failed: value is not an instance of given classes/interfaces');
                }

                return $instance;
            } catch (UnexpectedValueException $e) {
                throw new ExtractorException('Unable to find bound native object');
            }
        }

        throw new ExtractorException('Value must be of the type object to be able to find instance, ' . $value->typeOf()->value() . ' given');
    }
}
