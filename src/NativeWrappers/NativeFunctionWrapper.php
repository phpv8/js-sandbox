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


namespace Pinepain\JsSandbox\NativeWrappers;


use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\Context;
use V8\FunctionObject;
use V8\ObjectValue;


class NativeFunctionWrapper implements NativeFunctionWrapperInterface
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var ObjectValue
     */
    private $recv;
    /**
     * @var FunctionObject
     */
    private $function_object;
    /**
     * @var WrapperInterface
     */
    private $wrapper;

    public function __construct(Context $context, ObjectValue $recv, FunctionObject $function_object, WrapperInterface $wrapper)
    {
        $this->context         = $context;
        $this->recv            = $recv;
        $this->function_object = $function_object;
        $this->wrapper         = $wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function call(...$args)
    {
        $args_for_call = [];
        foreach ($args as $arg) {
            $args_for_call[] = $this->wrapper->wrap($this->context->getIsolate(), $this->context, $arg);
        }

        return $this->function_object->call($this->context, $this->recv, $args_for_call);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(...$args)
    {
        return $this->call(... $args);
    }
}
