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


namespace Pinepain\JsSandbox\Wrappers\CallbackGuards;


use Pinepain\JsSandbox\Exceptions\EscapableException;
use Pinepain\JsSandbox\Exceptions\NativeException;
use Throwable;
use V8\CallbackInfoInterface;
use V8\ExceptionManager;
use V8\Exceptions\TryCatchException;
use V8\ObjectValue;
use V8\StringValue;


class CallbackGuard implements CallbackGuardInterface
{
    /**
     * {@inheritdoc}
     */
    public function guard(callable $callback): callable
    {
        return function (...$arguments) use ($callback) {
            /** @var CallbackInfoInterface $args */
            $args = end($arguments); // By convention, CallbackInfoInterface (FunctionCallbackInfo or PropertyCallbackInfo) is the last argument passed to callback

            assert($args instanceof CallbackInfoInterface);

            $isolate = $args->getIsolate();
            $context = $args->getContext();

            try {
                return $callback(...$arguments);
            } catch (TryCatchException $e) {
                $native_exception = $e->getTryCatch()->getException();

                if (!$native_exception) {
                    $message = $this->getMessageFromException($e);
                    $isolate->throwException($context, ExceptionManager::createError($context, new StringValue($isolate, $message)), $e);
                } elseif ($native_exception instanceof ObjectValue) {
                    $isolate->throwException($context, $native_exception, $e);
                } else {
                    // TODO: convert non-object native exception to object and wire with external exception $e ?
                    $isolate->throwException($context, $native_exception);
                }
            } catch (NativeException $e) {
                $isolate->throwException($context, ExceptionManager::createError($context, new StringValue($isolate, $e->getMessage())), $e);
            } catch (EscapableException $e) {
                throw $e->getOriginal();
            } catch (Throwable $e) {
                // UNEXPECTED
                $message = $this->getMessageFromException($e);
                $isolate->throwException($context, ExceptionManager::createError($context, new StringValue($isolate, $message)), $e);
            }

            return null;
        };
    }

    protected function getMessageFromException(Throwable $e): string
    {
        return 'An internal exception occurred';
    }
}
