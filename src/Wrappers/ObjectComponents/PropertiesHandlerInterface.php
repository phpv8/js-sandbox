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


use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeObject;
use V8\NamedPropertyHandlerConfiguration;


interface PropertiesHandlerInterface
{
    public function createGetter(RuntimeObject $bridge): callable;

    public function createSetter(RuntimeObject $bridge): callable;

    public function createQuery(RuntimeObject $bridge): callable;

    public function createDeleter(RuntimeObject $bridge): callable;

    public function createEnumerator(RuntimeObject $bridge): callable;

    public function createConfiguration(RuntimeObject $bridge): NamedPropertyHandlerConfiguration;
}
