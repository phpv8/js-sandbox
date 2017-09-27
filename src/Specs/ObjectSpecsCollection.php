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


namespace Pinepain\JsSandbox\Specs;


use OutOfBoundsException;
use OverflowException;


class ObjectSpecsCollection implements ObjectSpecsCollectionInterface
{
    private $specs = [];

    public function get(string $class): ObjectSpecInterface
    {
        // TODO: lookup for class parent specs

        if (!isset($this->specs[$class])) {
            throw new OutOfBoundsException('Spec not found');
        }

        return $this->specs[$class];
    }

    public function put(string $class, ObjectSpecInterface $spec)
    {
        if (isset($this->specs[$class])) {
            throw new OverflowException('Spec with the same name already exists');
        }
        $this->specs[$class] = $spec;

        $this->sort($this->specs);

        // TODO:
        //$this->specs = $this->sort($this->specs);
    }

    private function sort(array $specs): array
    {
        uksort($specs, function ($first, $second) {
            if (is_subclass_of($first, $second)) {
                return -1;
            }

            if (is_subclass_of($second, $first)) {
                return 1;
            }

            return 0;
        });

        return $specs;
    }
}

