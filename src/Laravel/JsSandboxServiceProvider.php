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


namespace Pinepain\JsSandbox\Laravel;


use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Pinepain\JsSandbox\Common\NativeGlobalObjectWrapper;
use Pinepain\JsSandbox\Common\NativeGlobalObjectWrapperInterface;
use Pinepain\JsSandbox\Decorators\DecoratorsCollection;
use Pinepain\JsSandbox\Decorators\DecoratorsCollectionInterface;
use Pinepain\JsSandbox\Decorators\DecoratorSpecBuilder;
use Pinepain\JsSandbox\Decorators\DecoratorSpecBuilderInterface;
use Pinepain\JsSandbox\Decorators\Definitions\ExecutionContextInjectorDecorator;
use Pinepain\JsSandbox\Extractors\Extractor;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilder;
use Pinepain\JsSandbox\Extractors\ExtractorDefinitionBuilderInterface;
use Pinepain\JsSandbox\Extractors\ExtractorInterface;
use Pinepain\JsSandbox\Extractors\ExtractorsCollection;
use Pinepain\JsSandbox\Extractors\ExtractorsCollectionInterface;
use Pinepain\JsSandbox\Extractors\ObjectComponents\ExtractorsObjectStore;
use Pinepain\JsSandbox\Extractors\ObjectComponents\ExtractorsObjectStoreInterface;
use Pinepain\JsSandbox\Extractors\PlainExtractors\AnyExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\ArrayExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\AssocExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\BoolExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\DateExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\DateTimeExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\FunctionExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\JsonableExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\JsonExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\NativeObjectExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\NullExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\NumberExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\ObjectExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\PrimitiveExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\RawExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\RegExpExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\ScalarExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\StringExtractor;
use Pinepain\JsSandbox\Extractors\PlainExtractors\UndefinedExtractor;
use Pinepain\JsSandbox\Specs\Builder\ArgumentValueBuilder;
use Pinepain\JsSandbox\Specs\Builder\ArgumentValueBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\BindingSpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\BindingSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\FunctionSpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\FunctionSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\ObjectSpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\ObjectSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\ParameterSpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\ParameterSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\Builder\PropertySpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\PropertySpecBuilderInterface;
use Pinepain\JsSandbox\Specs\ObjectSpecsCollection;
use Pinepain\JsSandbox\Specs\ObjectSpecsCollectionInterface;
use Pinepain\JsSandbox\Wrappers\ArrayWrapper;
use Pinepain\JsSandbox\Wrappers\CallbackGuards\CallbackGuard;
use Pinepain\JsSandbox\Wrappers\CallbackGuards\CallbackGuardInterface;
use Pinepain\JsSandbox\Wrappers\CallbackGuards\DebugCallbackGuard;
use Pinepain\JsSandbox\Wrappers\CallbackGuards\DevCallbackGuard;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\ArgumentsExtractor;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\ArgumentsExtractorInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionCallHandler;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionCallHandlerInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionDecorator;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionDecoratorInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionExceptionHandler;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionExceptionHandlerInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionWrappersCache;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\FunctionWrappersCacheInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\ReturnValueSetter;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\ReturnValueSetterInterface;
use Pinepain\JsSandbox\Wrappers\FunctionWrapper;
use Pinepain\JsSandbox\Wrappers\ObjectComponents\PropertiesHandler;
use Pinepain\JsSandbox\Wrappers\ObjectComponents\PropertiesHandlerInterface;
use Pinepain\JsSandbox\Wrappers\ObjectComponents\WrappersObjectStore;
use Pinepain\JsSandbox\Wrappers\ObjectComponents\WrappersObjectStoreInterface;
use Pinepain\JsSandbox\Wrappers\ObjectWrapper;
use Pinepain\JsSandbox\Wrappers\PrimitiveWrapper;
use Pinepain\JsSandbox\Wrappers\Wrapper;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use Pinepain\ObjectMaps\ObjectBiMap;
use Pinepain\ObjectMaps\ObjectMap;
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
