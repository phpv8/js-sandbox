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


use Pinepain\JsSandbox\Decorators\DecoratorSpecInterface;
use Pinepain\JsSandbox\Decorators\Definitions\DecoratorInterface;
use Pinepain\JsSandbox\Specs\ReturnSpec\ReturnSpecInterface;
use Pinepain\JsSandbox\Specs\ThrowSpec\ThrowSpecListInterface;


interface FunctionSpecInterface
{
    /**
     * @return ParametersListInterface
     */
    public function getParameters(): ParametersListInterface;

    /**
     * @return ThrowSpecListInterface
     */
    public function getExceptions(): ThrowSpecListInterface;

    /**
     * @return ReturnSpecInterface
     */
    public function getReturn(): ReturnSpecInterface;

    /**
     * @return DecoratorSpecInterface[]
     */
    public function getDecorators(): array;
}
