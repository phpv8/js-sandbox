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


interface ModulesCacheInterface
{
    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * @param string $id
     *
     * @throws OutOfBoundsException When module with given $id does not exist
     * @return ModuleInterface
     */
    public function get(string $id): ModuleInterface;

    /**
     * @param string          $id
     * @param ModuleInterface $module
     *
     * @throws OverflowException When module with given $id already exists
     * @return void
     */
    public function put(string $id, ModuleInterface $module);

    /**
     * @param string $id
     *
     * @throws OutOfBoundsException When module with given $id does not exist
     */
    public function remove(string $id);
}
