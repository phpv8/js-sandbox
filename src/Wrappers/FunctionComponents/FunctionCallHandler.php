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


use Pinepain\JsSandbox\Wrappers\FunctionComponents\Runtime\ColdExecutionContextInterface;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunctionInterface;
use Throwable;
use V8\FunctionCallbackInfo;


class FunctionCallHandler implements FunctionCallHandlerInterface
{
    /**
     * @var ArgumentsExtractorInterface
     */
    private $arguments_extractor;
    /**
     * @var FunctionDecoratorInterface
     */
    private $decorator;
    /**
     * @var FunctionExceptionHandlerInterface
     */
    private $exception_handler;
    /**
     * @var ReturnValueSetterInterface
     */
    private $return_setter;

    /**
     * @param ArgumentsExtractorInterface $arguments_extractor
     * @param FunctionDecoratorInterface $decorator
     * @param FunctionExceptionHandlerInterface $exception_handler
     * @param ReturnValueSetterInterface $return_setter
     */
    public function __construct(
        ArgumentsExtractorInterface $arguments_extractor,
        FunctionDecoratorInterface $decorator,
        FunctionExceptionHandlerInterface $exception_handler,
        ReturnValueSetterInterface $return_setter
    ) {
        $this->arguments_extractor = $arguments_extractor;
        $this->decorator           = $decorator;
        $this->exception_handler   = $exception_handler;
        $this->return_setter       = $return_setter;
    }

    public function wrap(RuntimeFunctionInterface $function, ColdExecutionContextInterface $cold_execution_context)
    {
        return function (FunctionCallbackInfo $args) use ($function, $cold_execution_context) {
            $spec     = $function->getSpec();
            $callback = $function->getCallback();

            if ($spec->getDecorators()) {
                // When we have decorators, we need executions context.
                // Execution context is simple and abstract way to write advanced functions which relies on existent
                // abstraction level but at the same time allow manipulate on a lower level, e.g. examine current
                // context, building rich v8 native objects, but not limited to.
                $exec = $cold_execution_context->warm($args, $spec);

                $callback = $this->decorator->decorate($callback, $spec, $exec);
            }

            $arguments = $this->arguments_extractor->extract($args, $spec);
            
            try {
                $ret = $callback(...$arguments);
            } catch (Throwable $e) {
                $this->exception_handler->handle($args->getIsolate(), $args->getContext(), $e, $spec->getExceptions());

                // Handling exception means process thrown exception in some way, e.g. cleanup message, hide or add details
                // and then throw new exception that inherits SandboxException. If it doesn't happened, we re-throw original
                // exception
                throw $e;
            }

            if (null !== $ret || !$spec->getReturn()->prefersUndefined()) {
                $this->return_setter->set($cold_execution_context->getWrapper(), $args, $spec, $ret);
            }
        };
    }
}
