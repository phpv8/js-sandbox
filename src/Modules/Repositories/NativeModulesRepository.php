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


namespace Pinepain\JsSandbox\Modules\Repositories;


use OverflowException;
use Pinepain\JsSandbox\Modules\NativeModulePrototypeInterface;
use Pinepain\JsSandbox\Modules\SourceModulePrototype;
use UnexpectedValueException;


class NativeModulesRepository implements NativeModulesRepositoryInterface
{
    /**
     * @var NativeModulePrototypeInterface[]
     */
    private $modules = [];

    public function __construct()
    {
    }

    public function has($module): bool
    {
        return isset($this->modules[$module]);
    }

    public function get($module): NativeModulePrototypeInterface
    {
        if (!isset($this->modules[$module])) {
            throw new UnexpectedValueException('Native module not found');
        }

        return $this->modules[$module];
    }

    public function put($module, NativeModulePrototypeInterface $value)
    {
        if (isset($this->modules[$module])) {
            throw new OverflowException('Native module mapping already exists');
        }

        $this->modules[$module] = $value;
    }
}
