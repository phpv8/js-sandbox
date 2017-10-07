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


class FunctionExceptionHandler implements FunctionExceptionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(Isolate $isolate, Context $context, Throwable $throwable, ThrowSpecListInterface $throw_specs): bool
    {
        foreach ($throw_specs->getThrowSpecs() as $throw_spec) {
            // TODO: what about if ($throw_spec->canHandle($throwable)) { ... }, anyway, there are already one function call - $throw_spec->getClass()

            $filter_class = $throw_spec->getClass();

            if (is_subclass_of($throwable, $filter_class)) {
                //if ($throwable instanceof $filter_class) {
                $throw_spec->getHandler()->handle($isolate, $context, $throwable);

                // TODO: as handling mean to re-throw exception with maybe different desc, return statement here MAY be redundant
                return true;
            }
        }

        return false;
    }
}
