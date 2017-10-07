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


use Pinepain\JsSandbox\Specs\FunctionSpec;
use Pinepain\JsSandbox\Specs\ObjectSpecInterface;
use Pinepain\JsSandbox\Specs\ObjectSpecsCollectionInterface;
use Pinepain\JsSandbox\Wrappers\ObjectComponents\PropertiesHandlerInterface;
use Pinepain\JsSandbox\Wrappers\ObjectComponents\WrappedObject;
use Pinepain\JsSandbox\Wrappers\ObjectComponents\WrappersObjectStoreInterface;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeObject;
use V8\Context;
use V8\FunctionObject;
use V8\FunctionTemplate;
use V8\Isolate;
use V8\ObjectValue;
use V8\PropertyAttribute;
use V8\StringValue;
use V8\Value;
use function is_object;


class ObjectWrapper implements WrapperInterface, WrapperAwareInterface
{
    use WrapperAwareTrait;

    /**
     * @var ObjectSpecsCollectionInterface
     */
    private $specs;
    /**
     * @var WrappersObjectStoreInterface
     */
    private $wrappers_cache;
    /**
     * @var PropertiesHandlerInterface
     */
    private $properties_handler;

    public function __construct(WrappersObjectStoreInterface $wrappers_cache, ObjectSpecsCollectionInterface $specs, PropertiesHandlerInterface $properties_handler)
    {
        $this->wrappers_cache     = $wrappers_cache;
        $this->specs              = $specs;
        $this->properties_handler = $properties_handler;
    }

    /**
     * @param Isolate $isolate
     * @param Context $context
     * @param object  $object
     *
     * @return ObjectValue
     * @throws WrapperException
     */
    public function wrap(Isolate $isolate, Context $context, $object)
    {
        if (!is_object($object)) {
            // UNLIKELY
            throw new WrapperException('Value to wrap must be an object, ' . gettype($object) . ' given instead');
        }

        if ($object instanceof Value) {
            // UNLIKELY
            throw new WrapperException('Wrapping JS native values is not supported');
        }

        $js_object = null;
        $spec      = null;

        if ($object instanceof WrappedObject) {
            $js_object = $object->getValue();
            $spec      = $object->getSpec();
            $object    = $object->getObject();
        }

        if ($this->wrappers_cache->has($object)) {
            return $this->wrappers_cache->get($object);
        }

        if (!$spec) {
            $spec_name = $this->getObjectSpecName($object);
            $spec      = $this->getSpec($spec_name);
        }

        $bridge = new RuntimeObject($object, $spec, $this->wrapper);

        if ($js_object) {
            // we have wrapped js object, but without property set
            $this->setProperties($isolate, $context, $spec, $js_object, $bridge);
        } else {
            // when we have already wrapped object (e.g. when we build function which also acts as an object)
            // we make this wrapped php object tied to it initial js wrapper
            $js_object = $this->createWrapper($isolate, $context, $spec, $bridge);
        }

        $this->wrappers_cache->put($object, $js_object);

        return $js_object;
    }

    private function getObjectSpecName($object): string
    {
        return get_class($object);
    }

    /**
     * @param string $name
     *
     * @return ObjectSpecInterface
     */
    protected function getSpec(string $name): ObjectSpecInterface
    {
        return $this->specs->get($name);
    }

    protected function getWrapperFunction(Isolate $isolate, Context $context, ObjectSpecInterface $spec, RuntimeObject $bridge): FunctionObject
    {
        $tpl = new FunctionTemplate($isolate);
        $tpl->setClassName(new StringValue($isolate, $spec->getName()));
        $tpl->instanceTemplate()->setHandlerForNamedProperty($this->properties_handler->createConfiguration($bridge));
        // NOTE: we don't handle static properties here
        // NOTE: we don't handle inheritance here

        // TODO: add support to call Object as Function
        //$tpl->InstanceTemplate()->SetCallAsFunctionHandler();

        $func = $tpl->getFunction($context);

        return $func;
    }

    protected function createWrapper(Isolate $isolate, Context $context, ObjectSpecInterface $spec, RuntimeObject $bridge): ObjectValue
    {
        return $this->getWrapperFunction($isolate, $context, $spec, $bridge)->newInstance($context);
    }

    protected function setProperties(Isolate $isolate, Context $context, ObjectSpecInterface $spec, ObjectValue $js_object, RuntimeObject $bridge)
    {
        $getter = $this->properties_handler->createGetter($bridge);
        $setter = $this->properties_handler->createSetter($bridge);

        foreach ($spec->getProperties() as $name => $property_spec) {
            $js_name    = new StringValue($isolate, $name);
            $attributes = PropertyAttribute::DONT_DELETE;

            if ($property_spec instanceof FunctionSpec || $property_spec->isReadonly()) {
                $attributes |= PropertyAttribute::READ_ONLY;
            }

            $js_object->setNativeDataProperty($context, $js_name, $getter, $setter, $attributes);
        }
    }
}
