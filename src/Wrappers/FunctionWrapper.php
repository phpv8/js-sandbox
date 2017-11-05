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


namespace Pinepain\JsSandbox\Wrappers;


use Pinepain\JsSandbox\Wrappers\CallbackGuards\CallbackGuardInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionCallHandlerInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionWrappersCacheInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\Runtime\ColdExecutionContext;
use Pinepain\JsSandbox\Wrappers\ObjectComponents\WrappedObject;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunction;
use V8\Context;
use V8\FunctionObject;
use V8\Isolate;
use V8\StringValue;


class FunctionWrapper implements WrapperInterface, WrapperAwareInterface
{
    use WrapperAwareTrait;

    /**
     * @var FunctionWrappersCacheInterface
     */
    private $cache;
    /**
     * @var FunctionCallHandlerInterface
     */
    private $handler;
    /**
     * @var CallbackGuardInterface
     */
    private $guard;

    /**
     * @param FunctionWrappersCacheInterface $cache
     * @param FunctionCallHandlerInterface   $handler
     * @param CallbackGuardInterface         $guard
     */
    public function __construct(FunctionWrappersCacheInterface $cache, FunctionCallHandlerInterface $handler, CallbackGuardInterface $guard)
    {
        $this->cache   = $cache;
        $this->handler = $handler;
        $this->guard   = $guard;
    }

    /**
     * @param Isolate         $isolate
     * @param Context         $context
     * @param RuntimeFunction $value
     *
     * @return FunctionObject
     * @throws WrapperException
     */
    public function wrap(Isolate $isolate, Context $context, $value): FunctionObject
    {
        if (!($value instanceof RuntimeFunction)) {
            throw new WrapperException('Invalid function value to wrap');
        }

        if ($this->cache->has($value)) {
            return $this->cache->get($value);
        }

        $cold_execution_context = new ColdExecutionContext($this->wrapper, $value);

        $callback = $this->handler->wrap($value, $cold_execution_context);
        $callback = $this->guard->guard($callback);

        $f = new FunctionObject($context, $callback);

        if ($value->getObject()) {

            // we have function which may act as object, pass it to wrapper as we need it to be properly registered
            $wrapped = new WrappedObject($f, $value->getObject(), $value->getObjectSpec());
            $this->wrapper->wrap($isolate, $context, $wrapped);
        }

        $f->setName(new StringValue($isolate, $value->getName()));

        $this->cache->put($value, $f);

        return $f;
    }
}
