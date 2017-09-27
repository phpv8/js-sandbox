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


use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\FunctionCallbackInfo;


class ReturnValueSetter implements ReturnValueSetterInterface
{
    public function set(WrapperInterface $wrapper, FunctionCallbackInfo $args, FunctionSpecInterface $spec, $value)
    {
        $isolate = $args->getIsolate();
        $context = $args->getContext();

        $return_value = $args->getReturnValue();

        //$return_spec = $spec->getReturn();
        // TODO: handle return type

        $val = $wrapper->wrap($isolate, $context, $value);
        $return_value->set($val);
    }
}
