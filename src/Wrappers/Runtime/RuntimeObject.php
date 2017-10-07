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


namespace Pinepain\JsSandbox\Wrappers\Runtime;


use Pinepain\JsSandbox\Specs\ObjectSpecInterface;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use Ref\WeakReference;
use V8\FunctionObject;
use V8\ObjectValue;


class RuntimeObject
{
    /**
     * @var object
     */
    private $object;
    /**
     * @var ObjectSpecInterface
     */
    private $spec;
    /**
     * @var WrapperInterface
     */
    private $wrapper;

    /**
     * @var RuntimeMethod[]
     */
    private $wrapped_methods = [];

    /**
     * @param object              $object
     * @param ObjectSpecInterface $spec
     */
    public function __construct($object, ObjectSpecInterface $spec, WrapperInterface $wrapper)
    {
        $this->object  = $object;
        $this->spec    = $spec;
        $this->wrapper = $wrapper;
    }

    public function getSpec(): ObjectSpecInterface
    {
        return $this->spec;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getWrapper(): WrapperInterface
    {
        return $this->wrapper;
    }

    public function hasMethod(string $name): bool
    {
        return null !== $this->getMethod($name);
    }

    /**
     * @param string $name
     *
     * @return FunctionObject|null
     */
    public function getMethod(string $name)
    {
        if (!isset($this->wrapped_methods[$name])) {
            return null;
        }

        if (!$this->wrapped_methods[$name]->getReference()->valid()) {
            // UNEXPECTED
            unset($this->wrapped_methods[$name]);

            return null;
        }

        /** @var FunctionObject $js_function */
        $js_function = $this->wrapped_methods[$name]->getReference()->get();
        assert($js_function != null);

        return $js_function;
    }

    public function storeMethod(string $name, FunctionObject $function, ObjectValue $object)
    {
        $ref = new WeakReference($function, function () use ($name) {
            unset($this->wrapped_methods[$name]);
        });

        $this->wrapped_methods[$name] = new RuntimeMethod($ref, $object);
    }
}
