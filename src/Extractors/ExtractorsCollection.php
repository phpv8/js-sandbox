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


use OutOfBoundsException;
use OverflowException;
use Pinepain\JsSandbox\Extractors\PlainExtractors\PlainExtractorInterface;


class ExtractorsCollection implements ExtractorsCollectionInterface
{
    protected $extractors = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $name): PlainExtractorInterface
    {
        if (!isset($this->extractors[$name])) {
            throw new OutOfBoundsException("Extractor '{$name}' not found");
        }

        return $this->extractors[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $name, PlainExtractorInterface $extractor)
    {
        if (isset($this->extractors[$name])) {
            throw new OverflowException("Extractor with the same name ('{$name}') already exists");
        }
        $this->extractors[$name] = $extractor;
    }
}
