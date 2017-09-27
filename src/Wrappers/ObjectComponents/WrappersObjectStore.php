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


namespace Pinepain\JsSandbox\Wrappers\ObjectComponents;


use OverflowException;
use Pinepain\ObjectMaps\ObjectMapInterface;
use UnexpectedValueException;
use V8\ObjectValue;


class WrappersObjectStore implements WrappersObjectStoreInterface
{
    /**
     * @var ObjectMapInterface
     */
    private $map;

    public function __construct(ObjectMapInterface $map)
    {
        $this->map = $map;
    }

    /**
     * {@inheritdoc}
     */
    public function has($object): bool
    {
        return $this->map->has($object);
    }

    /**
     * {@inheritdoc}
     */
    public function get($object): ObjectValue
    {
        if (!$this->map->has($object)) {
            throw new UnexpectedValueException('Object mapping not found');
        }

        return $this->map->get($object);
    }

    /**
     * {@inheritdoc}
     */
    public function put($object, ObjectValue $value)
    {
        if ($this->map->has($object)) {
            throw new OverflowException('Object mapping already exists');
        }

        $this->map->put($object, $value);
    }
}
