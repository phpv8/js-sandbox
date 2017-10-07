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


use OverflowException;
use Pinepain\ObjectMaps\ObjectMapInterface;
use UnexpectedValueException;
use V8\FunctionObject;


class FunctionWrappersCache implements FunctionWrappersCacheInterface
{
    /**
     * @var ObjectMapInterface
     */
    private $map;

    public function __construct(ObjectMapInterface $map)
    {
        // NOTE: map should be a weak values map
        $this->map = $map;
    }

    public function has($object): bool
    {
        return $this->map->has($object);
    }

    public function get($object): FunctionObject
    {
        if (!$this->map->has($object)) {
            throw new UnexpectedValueException('FunctionObject mapping not found');
        }

        return $this->map->get($object);
    }

    public function put($object, FunctionObject $value)
    {
        if ($this->map->has($object)) {
            throw new OverflowException('FunctionObject mapping already exists');
        }

        $this->map->put($object, $value);
    }
}
