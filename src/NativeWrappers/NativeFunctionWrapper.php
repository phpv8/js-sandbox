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
use V8\Isolate;
use V8\ObjectValue;
use V8\Value;


class NativeFunctionWrapper extends NativeObjectWrapper implements NativeFunctionWrapperInterface
{
    /**
     * @var ObjectValue
     */
    protected $recv;

    public function __construct(Isolate $isolate, Context $context, FunctionObject $target, WrapperInterface $wrapper, ObjectValue $recv)
    {
        parent::__construct($isolate, $context, $target, $wrapper);
        $this->recv = $recv;
    }

    /**
     * {@inheritdoc}
     */
    public function call(...$args): Value
    {
        $args_for_call = [];
        foreach ($args as $arg) {
            $args_for_call[] = $this->wrapper->wrap($this->context->getIsolate(), $this->context, $arg);
        }

        assert($this->target instanceof FunctionObject);

        return $this->target->call($this->context, $this->recv, $args_for_call);
    }
}
