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


namespace Pinepain\JsSandbox\Decorators;


use OutOfBoundsException;
use OverflowException;
use Pinepain\JsSandbox\Decorators\Definitions\DecoratorInterface;


class DecoratorsCollection implements DecoratorsCollectionInterface
{
    protected $decorators = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $name): DecoratorInterface
    {
        if (!isset($this->decorators[$name])) {
            throw new OutOfBoundsException("Decorator '{$name}' not found");
        }

        return $this->decorators[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $name, DecoratorInterface $extractor)
    {
        if (isset($this->decorators[$name])) {
            throw new OverflowException("Decorator with the same name ('{$name}') already exists");
        }
        $this->decorators[$name] = $extractor;
    }
}
