<?php declare(strict_types=1);

/*
 * This file is part of the pinepain/js-sandbox PHP library.
 *
 * Copyright (c) 2016-2017 Bogdan Padalko <thepinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace PhpV8\JsSandbox\Laravel;


use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use PhpV8\JsSandbox\Executor;
use PhpV8\JsSandbox\Executors\ModulesNativeExecutor;
use PhpV8\JsSandbox\Executors\StringExecutor;
use V8\Context;


class JsSandboxExecutorServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        $this->app->singleton(StringExecutor::class);
        // TODO: if config('js_sandbox.modules_enabled') - with modules, without otherwise
        $this->app->singleton(ModulesNativeExecutor::class); // TODO: register native require function

        $this->app->singleton(Executor::class, function (Container $app) {
            $context = $app->make(Context::class);
            $string  = $app->make(StringExecutor::class);
            $module  = $app->make(ModulesNativeExecutor::class);

            return new Executor($context, $string, $module);
        });
    }

    public function provides()
    {
        return [
            StringExecutor::class,
            ModulesNativeExecutor::class,
            Executor::class,
        ];
    }
}
