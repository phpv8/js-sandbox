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


use Pinepain\JsSandbox\Specs\ThrowSpec\ThrowSpecListInterface;
use Throwable;
use V8\Context;
use V8\Isolate;


interface FunctionExceptionHandlerInterface
{
    /**
     * @param Isolate                $isolate
     * @param Context                $context
     * @param Throwable              $throwable
     * @param ThrowSpecListInterface $throw_specs
     *
     * @return bool
     */
    public function handle(Isolate $isolate, Context $context, Throwable $throwable, ThrowSpecListInterface $throw_specs): bool;
}
