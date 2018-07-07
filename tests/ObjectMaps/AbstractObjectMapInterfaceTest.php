<?php declare(strict_types=1);

/*
 * This file is part of the pinepain/php-object-maps PHP library.
 *
 * Copyright (c) 2016-2017 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit http://opensource.org/licenses/MIT
 */


namespace PhpV8\ObjectMaps\Tests;

use PHPUnit\Framework\TestCase;
use PhpV8\ObjectMaps\ObjectBiMapInterface;
use PhpV8\ObjectMaps\ObjectMapInterface;
use stdClass;


abstract class AbstractObjectMapInterfaceTest extends TestCase
{
    /**
     * @param int $behavior
     *
     * @return ObjectMapInterface | ObjectBiMapInterface
     */
    abstract public function buildMap(int $behavior = ObjectMapInterface::DEFAULT);

    public function testMap()
    {
        $map = $this->buildMap();

        $key = new stdClass();
        $value = new stdClass();

        $this->assertSame(0, $map->count());
        $map->put($key, $value);
        $this->assertSame(1, $map->count());

        $value = null;
        $this->assertSame(1, $map->count());

        $key = null;
        $this->assertSame(1, $map->count());
    }

    public function testWeakKeyMap()
    {
        $map = $this->buildMap(ObjectMapInterface::WEAK_KEY);

        $key = new stdClass();
        $value = new stdClass();

        $this->assertSame(0, $map->count());
        $map->put($key, $value);
        $this->assertSame(1, $map->count());
        $this->assertSame($value, $map->get($key));

        $value = null;
        $this->assertSame(1, $map->count());

        $key = null;
        $this->assertSame(0, $map->count());
    }

    public function testWeakValueMap()
    {
        $map = $this->buildMap(ObjectMapInterface::WEAK_VALUE);

        $key = new stdClass();
        $value = new stdClass();

        $this->assertSame(0, $map->count());
        $map->put($key, $value);
        $this->assertSame(1, $map->count());
        $this->assertSame($value, $map->get($key));

        $key = null;
        $this->assertSame(1, $map->count());

        $value = null;
        $this->assertSame(0, $map->count());
    }

    public function testWeakKeyValueMap()
    {
        $map = $this->buildMap(ObjectMapInterface::WEAK_KEY_VALUE);

        $key = new stdClass();
        $value = new stdClass();

        $this->assertSame(0, $map->count());
        $map->put($key, $value);
        $this->assertSame(1, $map->count());

        $key = null;
        $this->assertSame(0, $map->count());


        $key = new stdClass();
        $value = new stdClass();

        $this->assertSame(0, $map->count());
        $map->put($key, $value);
        $this->assertSame(1, $map->count());

        $value = null;
        $this->assertSame(0, $map->count());
    }

    public function testPutAndGet()
    {
        $map = $this->buildMap();

        $key   = new stdClass();
        $value = new stdClass();

        $map->put($key, $value);
        $this->assertSame($value, $map->get($key));
    }

    /**
     * @expectedException \Pinepain\ObjectMaps\Exceptions\OverflowException
     * @expectedExceptionMessage Value with such key already exists
     */
    public function testPutExistentKeyFails()
    {
        $map = $this->buildMap();

        $key   = new stdClass();
        $value = new stdClass();

        $map->put($key, $value);
        $map->put($key, $value);
    }

    /**
     * @expectedException \Pinepain\ObjectMaps\Exceptions\OutOfBoundsException
     * @expectedExceptionMessage Value with such key not found
     */
    public function testGetNonexistentKeyFails()
    {
        $map = $this->buildMap();

        $map->get(new stdClass());
    }

    public function testHasNonexistentKey()
    {
        $map = $this->buildMap();

        $this->assertFalse($map->has(new stdClass()));
    }

    public function testHasExistentKey()
    {
        $map = $this->buildMap();

        $key = new stdClass();
        $map->put($key, new stdClass());

        $this->assertTrue($map->has($key));
    }

    /**
     * @expectedException \Pinepain\ObjectMaps\Exceptions\OutOfBoundsException
     * @expectedExceptionMessage Value with such key not found
     */
    public function testRemoveNonexistentKeyFails()
    {
        $map = $this->buildMap();

        $map->remove(new stdClass());
    }

    public function testRemove()
    {
        $map = $this->buildMap();

        $key = new stdClass();
        $map->put($key, new stdClass());

        $this->assertTrue($map->has($key));

        $map->remove($key);

        $this->assertFalse($map->has($key));
    }

    public function testCount()
    {
        $map = $this->buildMap();

        $this->assertSame(0, $map->count());

        $key = new stdClass();
        $map->put($key, new stdClass());

        $this->assertSame(1, $map->count());

        $map->remove($key);

        $this->assertSame(0, $map->count());
    }

    public function testClear()
    {
        $map = $this->buildMap();

        $this->assertSame(0, $map->count());

        $map->put(new stdClass(), new stdClass());
        $this->assertSame(1, $map->count());
        $map->put(new stdClass(), new stdClass());
        $this->assertSame(2, $map->count());

        $map->clear();

        $this->assertSame(0, $map->count());
    }
}
