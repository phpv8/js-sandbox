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


namespace Pinepain\JsSandbox\Specs\ExceptionHandlers;


use Pinepain\JsSandbox\Exceptions\NativeException;
use Throwable;
use V8\Context;
use V8\Isolate;


class DebugEchoExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(Isolate $isolate, Context $context, Throwable $throwable)
    {
        $message = 'An internal error occurred: ' . get_class($throwable) . ': ' . $throwable->getMessage() . PHP_EOL . $throwable->getTraceAsString();

        throw new NativeException($message, 0, $throwable);
    }
}
