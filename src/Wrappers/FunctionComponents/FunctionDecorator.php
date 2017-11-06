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


namespace Pinepain\JsSandbox\Wrappers\FunctionComponents;


use Pinepain\JsSandbox\Decorators\DecoratorsCollectionInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\Runtime\ExecutionContextInterface;


class FunctionDecorator implements FunctionDecoratorInterface
{
    /**
     * @var DecoratorsCollectionInterface
     */
    private $collection;

    /**
     * @param DecoratorsCollectionInterface $collection
     */
    public function __construct(DecoratorsCollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function decorate(callable $callback, FunctionSpecInterface $spec, ExecutionContextInterface $exec): callable
    {
        foreach ($spec->getDecorators() as $decorator_spec) {
            $decorator = $this->collection->get($decorator_spec->getName());

            $callback = $decorator->decorate($callback, $exec);
        }

        return $callback;
    }
}
