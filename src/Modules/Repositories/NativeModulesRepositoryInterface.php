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


use Pinepain\JsSandbox\Modules\NativeModulePrototypeInterface;


interface NativeModulesRepositoryInterface
{
    public function has($module): bool;

    public function get($module): NativeModulePrototypeInterface;

    public function put($module, NativeModulePrototypeInterface $value);
}
