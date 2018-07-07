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
use InvalidArgumentException;
use PhpV8\JsSandbox\Common\NativeGlobalObjectWrapper;
use PhpV8\JsSandbox\Common\NativeGlobalObjectWrapperInterface;
use PhpV8\JsSandbox\Decorators\DecoratorsCollection;
use PhpV8\JsSandbox\Decorators\DecoratorsCollectionInterface;
use PhpV8\JsSandbox\Decorators\DecoratorSpecBuilder;
use PhpV8\JsSandbox\Decorators\DecoratorSpecBuilderInterface;
use PhpV8\JsSandbox\Decorators\Definitions\ExecutionContextInjectorDecorator;
use PhpV8\JsSandbox\Extractors\Extractor;
use PhpV8\JsSandbox\Extractors\ExtractorDefinitionBuilder;
use PhpV8\JsSandbox\Extractors\ExtractorDefinitionBuilderInterface;
use PhpV8\JsSandbox\Extractors\ExtractorInterface;
use PhpV8\JsSandbox\Extractors\ExtractorsCollection;
use PhpV8\JsSandbox\Extractors\ExtractorsCollectionInterface;
use PhpV8\JsSandbox\Extractors\ObjectComponents\ExtractorsObjectStore;
use PhpV8\JsSandbox\Extractors\ObjectComponents\ExtractorsObjectStoreInterface;
use PhpV8\JsSandbox\Extractors\PlainExtractors\AnyExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\ArrayExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\AssocExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\BoolExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\DateExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\DateTimeExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\FunctionExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\JsonableExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\JsonExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\NativeObjectExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\NullExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\NumberExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\ObjectExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\PrimitiveExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\RawExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\RegExpExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\ScalarExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\StringExtractor;
use PhpV8\JsSandbox\Extractors\PlainExtractors\UndefinedExtractor;
use PhpV8\JsSandbox\Specs\Builder\ArgumentValueBuilder;
use PhpV8\JsSandbox\Specs\Builder\ArgumentValueBuilderInterface;
use PhpV8\JsSandbox\Specs\Builder\BindingSpecBuilder;
use PhpV8\JsSandbox\Specs\Builder\BindingSpecBuilderInterface;
use PhpV8\JsSandbox\Specs\Builder\FunctionSpecBuilder;
use PhpV8\JsSandbox\Specs\Builder\FunctionSpecBuilderInterface;
use PhpV8\JsSandbox\Specs\Builder\ObjectSpecBuilder;
use PhpV8\JsSandbox\Specs\Builder\ObjectSpecBuilderInterface;
use PhpV8\JsSandbox\Specs\Builder\ParameterSpecBuilder;
use PhpV8\JsSandbox\Specs\Builder\ParameterSpecBuilderInterface;
use PhpV8\JsSandbox\Specs\Builder\PropertySpecBuilder;
use PhpV8\JsSandbox\Specs\Builder\PropertySpecBuilderInterface;
use PhpV8\JsSandbox\Specs\ObjectSpecsCollection;
use PhpV8\JsSandbox\Specs\ObjectSpecsCollectionInterface;
use PhpV8\JsSandbox\Wrappers\ArrayWrapper;
use PhpV8\JsSandbox\Wrappers\CallbackGuards\CallbackGuard;
use PhpV8\JsSandbox\Wrappers\CallbackGuards\CallbackGuardInterface;
use PhpV8\JsSandbox\Wrappers\CallbackGuards\DebugCallbackGuard;
use PhpV8\JsSandbox\Wrappers\CallbackGuards\DevCallbackGuard;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\ArgumentsExtractor;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\ArgumentsExtractorInterface;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\FunctionCallHandler;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\FunctionCallHandlerInterface;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\FunctionDecorator;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\FunctionDecoratorInterface;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\FunctionExceptionHandler;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\FunctionExceptionHandlerInterface;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\FunctionWrappersCache;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\FunctionWrappersCacheInterface;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\ReturnValueSetter;
use PhpV8\JsSandbox\Wrappers\FunctionComponents\ReturnValueSetterInterface;
use PhpV8\JsSandbox\Wrappers\FunctionWrapper;
use PhpV8\JsSandbox\Wrappers\ObjectComponents\PropertiesHandler;
use PhpV8\JsSandbox\Wrappers\ObjectComponents\PropertiesHandlerInterface;
use PhpV8\JsSandbox\Wrappers\ObjectComponents\WrappersObjectStore;
use PhpV8\JsSandbox\Wrappers\ObjectComponents\WrappersObjectStoreInterface;
use PhpV8\JsSandbox\Wrappers\ObjectWrapper;
use PhpV8\JsSandbox\Wrappers\PrimitiveWrapper;
use PhpV8\JsSandbox\Wrappers\Wrapper;
use PhpV8\JsSandbox\Wrappers\WrapperInterface;
use PhpV8\ObjectMaps\ObjectBiMap;
use PhpV8\ObjectMaps\ObjectMap;
use V8\Context;
use V8\Isolate;


class JsSandboxServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->registerIsolateAndContext();
        $this->registerCallbackGuard();
        $this->registerFunctionCallHandler();
        $this->registerWrapper();
        $this->registerExtractor();
        $this->registerCommon();
    }

    protected function registerIsolateAndContext()
    {
        $this->app->singleton(Isolate::class, function (/*Container $app*/) {
            return new Isolate();
        });

        $this->app->singleton(Context::class, function (Container $app) {
            return new Context($app->make(Isolate::class));
        });
    }

    protected function registerCallbackGuard()
    {

        $config = $this->app->make('config');

        $guards = [
            'prod'  => CallbackGuard::class,
            'dev'   => DevCallbackGuard::class,
            'debug' => DebugCallbackGuard::class,
        ];

        $guard = $config->get('js_sandbox.guard', 'auto');

        if ('auto' == $guard) {
            if ($config->get('app.debug')) {
                if ($this->app->environment('production')) {
                    $guard = 'debug';
                } else {
                    $guard = 'dev';
                }
            } else {
                if ($this->app->environment('production')) {
                    $guard = 'prod';
                } else {
                    $guard = 'debug';
                }
            }
        }

        if (!isset($guards[$guard])) {
            $expected = '[\'auto\', \'' . implode('\', \'', array_keys($guards)) . '\']';
            throw new InvalidArgumentException("Invalid guard mode specified: expected one of {$expected}, '$guard' given instead");
        }


        $this->app->singleton(CallbackGuardInterface::class, $guards[$guard]);

    }

    protected function registerFunctionCallHandler()
    {
        $this->app->singleton(ArgumentsExtractorInterface::class, ArgumentsExtractor::class);
        $this->app->singleton(FunctionDecoratorInterface::class, FunctionDecorator::class);
        $this->app->singleton(FunctionExceptionHandlerInterface::class, FunctionExceptionHandler::class);
        $this->app->singleton(ReturnValueSetterInterface::class, ReturnValueSetter::class);

        $this->app->singleton(FunctionCallHandlerInterface::class, FunctionCallHandler::class);


        $this->app->singleton(DecoratorsCollectionInterface::class, function (Container $app) {

            $collection = new DecoratorsCollection();

            $collection->put('inject-context', new ExecutionContextInjectorDecorator());

            return $collection;
        });
    }

    protected function registerWrapper()
    {
        $function_wrappers_map = new ObjectMap(ObjectMap::WEAK_VALUE);

        $this->app->singleton(ObjectSpecsCollectionInterface::class, ObjectSpecsCollection::class);
        $this->app->singleton(FunctionWrappersCacheInterface::class, function () use ($function_wrappers_map) {
            return new FunctionWrappersCache($function_wrappers_map);
        });

        $object_store_map = new ObjectBiMap();

        // $this->app->when(WrappersObjectStore::class)
        //           ->needs(ObjectMapInterface::class)
        //           ->give(function () use ($object_store_map) {
        //               return $object_store_map;
        //           });
        //
        // $this->app->when(ExtractorsObjectStoreInterface::class)
        //           ->needs(ObjectMapInterface::class)
        //           ->give(function () use ($object_store_map) {
        //               return $object_store_map->values();
        //           });
        //
        // $this->app->singleton(WrappersObjectStoreInterface::class, WrappersObjectStore::class);
        // $this->app->singleton(ExtractorsObjectStoreInterface::class, ExtractorsObjectStore::class);

        $this->app->instance(WrappersObjectStoreInterface::class, new WrappersObjectStore($object_store_map));
        $this->app->instance(ExtractorsObjectStoreInterface::class, new ExtractorsObjectStore($object_store_map->values()));

        $this->app->singleton(PropertiesHandlerInterface::class, PropertiesHandler::class);

        $this->app->singleton(WrapperInterface::class, function (Container $app) {

            $wrapper = new Wrapper();

            $primitive_wrapper = $app->make(PrimitiveWrapper::class);

            $array_wrapper = $app->make(ArrayWrapper::class);
            $array_wrapper->setWrapper($wrapper);

            $function_wrapper = $app->make(FunctionWrapper::class);
            $function_wrapper->setWrapper($wrapper);

            $object_wrapper = $app->make(ObjectWrapper::class);
            $object_wrapper->setWrapper($wrapper);

            $wrapper->setPrimitiveWrapper($primitive_wrapper);
            $wrapper->setArrayWrapper($array_wrapper);
            $wrapper->setFunctionWrapper($function_wrapper);
            $wrapper->setObjectWrapper($object_wrapper);

            return $wrapper;
        });
    }

    protected function registerExtractor()
    {
        $this->app->singleton(ExtractorDefinitionBuilderInterface::class, ExtractorDefinitionBuilder::class);
        $this->app->singleton(PropertySpecBuilderInterface::class, PropertySpecBuilder::class);
        $this->app->singleton(ArgumentValueBuilderInterface::class, ArgumentValueBuilder::class);
        $this->app->singleton(ParameterSpecBuilderInterface::class, ParameterSpecBuilder::class);
        $this->app->singleton(DecoratorSpecBuilderInterface::class, DecoratorSpecBuilder::class);
        $this->app->singleton(FunctionSpecBuilderInterface::class, FunctionSpecBuilder::class);
        $this->app->singleton(BindingSpecBuilderInterface::class, BindingSpecBuilder::class);
        $this->app->singleton(ObjectSpecBuilderInterface::class, ObjectSpecBuilder::class);

        $this->app->singleton(ExtractorInterface::class, Extractor::class);

        $this->app->singleton(ExtractorsCollectionInterface::class, function (Container $app) {

            $collection = new ExtractorsCollection();

            // TODO: register basic extractor

            $collection->put('[]', $assoc = new AssocExtractor());
            $collection->put('array', $array = new ArrayExtractor(new AssocExtractor(false)));

            $collection->put('raw', $raw = new RawExtractor());
            $collection->put('primitive', $primitive = new PrimitiveExtractor());

            $collection->put('string', $string = new StringExtractor());
            $collection->put('number', $number = new NumberExtractor());
            $collection->put('bool', $bool = new BoolExtractor());
            $collection->put('null', $null = new NullExtractor());
            $collection->put('undefined', $undefined = new UndefinedExtractor());

            $collection->put('regexp', $regexp = new RegExpExtractor());
            $collection->put('date', $date = new DateExtractor());
            $collection->put('datetime', $datetime = new DateTimeExtractor());
            $collection->put('object', $object = new ObjectExtractor());
            $collection->put('function', $function = new FunctionExtractor());
            $collection->put('native-object', $instance = $app->make(NativeObjectExtractor::class));

            $collection->put('json', $json = new JsonExtractor());
            $collection->put('jsonable', $json = new JsonableExtractor());

            $collection->put('scalar', $scalar = new ScalarExtractor($string, $number, $bool, $null, $undefined));
            $collection->put('any', $any = new AnyExtractor($scalar, $regexp, $datetime, $assoc));

            return $collection;
        });
    }

    public function registerCommon()
    {
        $this->app->singleton(NativeGlobalObjectWrapperInterface::class, function (Container $app) {
            return new NativeGlobalObjectWrapper(
                $app->make(Isolate::class),
                $app->make(Context::class),
                $app->make(Context::class)->globalObject(),
                $app->make(WrapperInterface::class)
            );
        });
    }

    public function provides()
    {
        return [
            Isolate::class,
            Context::class,

            CallbackGuardInterface::class,

            ArgumentsExtractorInterface::class,
            FunctionDecoratorInterface::class,
            FunctionExceptionHandlerInterface::class,
            ReturnValueSetterInterface::class,
            FunctionCallHandlerInterface::class,

            ObjectSpecsCollectionInterface::class,
            FunctionWrappersCacheInterface::class,
            WrappersObjectStoreInterface::class,
            PropertiesHandlerInterface::class,
            WrapperInterface::class,

            ExtractorDefinitionBuilderInterface::class,
            PropertySpecBuilderInterface::class,
            ArgumentValueBuilderInterface::class,
            ParameterSpecBuilderInterface::class,
            DecoratorSpecBuilderInterface::class,
            FunctionSpecBuilderInterface::class,
            BindingSpecBuilderInterface::class,
            ObjectSpecBuilderInterface::class,

            ExtractorsCollectionInterface::class,
            ExtractorsObjectStoreInterface::class,
            ExtractorInterface::class,

            NativeGlobalObjectWrapperInterface::class,
        ];
    }
}
