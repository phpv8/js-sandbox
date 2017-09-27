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


namespace Pinepain\JsSandbox\Modules;


use OutOfBoundsException;
use OverflowException;


class ModulesCache implements ModulesCacheInterface
{
    /**
     * @var ModuleInterface[]
     */
    protected $modules = [];

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return isset($this->modules[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id): ModuleInterface
    {
        if (!isset($this->modules[$id])) {
            throw new OutOfBoundsException("Can not load module '$id' from cache");
        }

        return $this->modules[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $id, ModuleInterface $module)
    {
        if (isset($this->modules[$id])) {
            throw new OverflowException("Module '$id' is already stored in cache ");
        }

        $this->modules[$id] = $module;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $id)
    {
        if (!isset($this->modules[$id])) {
            throw new OutOfBoundsException("Module '$id' does not exists in cache");
        }

        unset($this->modules[$id]);
    }


}
