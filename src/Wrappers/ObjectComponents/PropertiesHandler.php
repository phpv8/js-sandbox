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


namespace Pinepain\JsSandbox\Wrappers\ObjectComponents;


use Pinepain\JsSandbox\Exceptions\NativeException;
use Pinepain\JsSandbox\Extractors\ExtractorException;
use Pinepain\JsSandbox\Extractors\ExtractorInterface;
use Pinepain\JsSandbox\Specs\BindingSpecInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Wrappers\CallbackGuards\CallbackGuardInterface;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunction;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeObject;
use V8\FunctionObject;
use V8\NamedPropertyHandlerConfiguration;
use V8\NameValue;
use V8\PropertyAttribute;
use V8\PropertyCallbackInfo;
use V8\Value;


class PropertiesHandler implements PropertiesHandlerInterface
{
    /**
     * @var CallbackGuardInterface
     */
    private $guard;
    /**
     * @var ExtractorInterface
     */
    private $extractor;

    public function __construct(CallbackGuardInterface $guard, ExtractorInterface $extractor)
    {
        $this->guard     = $guard;
        $this->extractor = $extractor;
    }

    public function getter(RuntimeObject $bridge, NameValue $name, PropertyCallbackInfo $args): void
    {
        $js_object = $args->this();

        $spec      = $bridge->getSpec();
        $prop_name = $name->value();

        if (!$spec->hasProperty($prop_name)) {
            return;
        }

        $property_spec = $spec->getProperty($prop_name);

        if ($property_spec instanceof BindingSpecInterface) {
            $prop_name     = $property_spec->getName();
            $property_spec = $property_spec->getSpec();
        }

        if ($property_spec instanceof FunctionSpecInterface) {
            $js_function = $bridge->getMethod($prop_name);
            if (!$js_function) {
                /** @var callable $callback */
                $callback = [$bridge->getObject(), $prop_name];
                $func     = new RuntimeFunction($prop_name, $callback, $property_spec);
                /** @var FunctionObject $js_function */
                $js_function = $bridge->getWrapper()->wrap($args->getIsolate(), $args->getContext(), $func);

                $bridge->storeMethod($prop_name, $js_function, $js_object);
                // TODO: is TODO below is still relevant?
                // TODO: store function object in object map
            }
            $ret = $js_function;
        } else {
            // NOTE: we don't handle static properties at this time

            if ($getter = $property_spec->getGetterName()) {
                $prop_value = $bridge->getObject()->$getter();
            } else {
                $prop_value = $bridge->getObject()->$prop_name;
            }

            $ret = $bridge->getWrapper()->wrap($args->getIsolate(), $args->getContext(), $prop_value);
        }

        $args->getReturnValue()->set($ret);
    }

    public function setter(RuntimeObject $bridge, NameValue $name, Value $value, PropertyCallbackInfo $args): void
    {
        $spec      = $bridge->getSpec();
        $prop_name = $name->value();

        if (!$spec->hasProperty($prop_name)) {
            return;
        }

        $property_spec = $spec->getProperty($prop_name);

        if ($property_spec instanceof BindingSpecInterface) {
            $prop_name     = $property_spec->getName();
            $property_spec = $property_spec->getSpec();
        }

        if ($property_spec instanceof FunctionSpecInterface || $property_spec->isReadonly()) {
            return;
        }

        try {
            $definition = $property_spec->getExtractorDefinition();
            assert(null !== $definition);

            $prop_value = $this->extractor->extract($args->getContext(), $value, $definition);
        } catch (ExtractorException $e) {
            throw new NativeException("Failed to set property value: {$e->getMessage()}");
        }

        // NOTE: we don't handle static props here

        if ($setter = $property_spec->getSetterName()) {
            $bridge->getObject()->$setter($prop_value);

            return;
        }

        $bridge->getObject()->$prop_name = $prop_value;
    }

    public function query(RuntimeObject $bridge, NameValue $name, PropertyCallbackInfo $args): void
    {
        $spec      = $bridge->getSpec();
        $prop_name = $name->value();

        if (!$spec->hasProperty($prop_name)) {
            return;
        }

        $property_spec = $spec->getProperty($prop_name);

        if ($property_spec instanceof BindingSpecInterface) {
            $prop_name     = $property_spec->getName();
            $property_spec = $property_spec->getSpec();
        }

        // Functions should be always read-only
        if ($property_spec instanceof FunctionSpecInterface || $property_spec->isReadonly()) {
            $args->getReturnValue()->setInteger(PropertyAttribute::READ_ONLY | PropertyAttribute::DONT_DELETE);
        }

        $args->getReturnValue()->setInteger(PropertyAttribute::DONT_DELETE);
    }

    public function deleter(RuntimeObject $bridge, NameValue $name, PropertyCallbackInfo $args): void
    {
        $spec      = $bridge->getSpec();
        $prop_name = $name->value();

        if (!$spec->hasProperty($prop_name)) {
            return;
        }

        $args->getReturnValue()->setBool(false); // We don't allow to delete any property from js
    }

    public function enumerator(RuntimeObject $bridge, PropertyCallbackInfo $args): void
    {
        $spec = $bridge->getSpec();

        $names = array_keys($spec->getProperties());

        $args->getReturnValue()->set($bridge->getWrapper()->wrap($args->getIsolate(), $args->getContext(), $names));
    }

    public function createGetter(RuntimeObject $bridge): callable
    {
        return $this->guard->guard(function (NameValue $name, PropertyCallbackInfo $args) use ($bridge) {
            $this->getter($bridge, $name, $args);
        });
    }

    public function createSetter(RuntimeObject $bridge): callable
    {
        return $this->guard->guard(function (NameValue $name, Value $value, PropertyCallbackInfo $args) use ($bridge) {
            $this->setter($bridge, $name, $value, $args);
        });
    }

    public function createQuery(RuntimeObject $bridge): callable
    {
        return $this->guard->guard(function (NameValue $name, PropertyCallbackInfo $args) use ($bridge) {
            $this->query($bridge, $name, $args);
        });
    }

    public function createDeleter(RuntimeObject $bridge): callable
    {
        return $this->guard->guard(function (NameValue $name, PropertyCallbackInfo $args) use ($bridge) {
            $this->deleter($bridge, $name, $args);
        });
    }

    public function createEnumerator(RuntimeObject $bridge): callable
    {
        return $this->guard->guard(function (PropertyCallbackInfo $args) use ($bridge) {
            $this->enumerator($bridge, $args);
        });
    }

    public function createConfiguration(RuntimeObject $bridge): NamedPropertyHandlerConfiguration
    {
        return new NamedPropertyHandlerConfiguration(
            $this->createGetter($bridge),
            $this->createSetter($bridge),
            $this->createQuery($bridge),
            $this->createDeleter($bridge),
            $this->createEnumerator($bridge)
        );
    }
}
