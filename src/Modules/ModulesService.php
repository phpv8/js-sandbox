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


namespace Pinepain\JsSandbox\Modules;


use Pinepain\JsSandbox\Specs\AnonymousObjectSpec;
use Pinepain\JsSandbox\Specs\Builder\FunctionSpecBuilder;
use Pinepain\JsSandbox\Specs\Builder\ObjectSpecBuilderInterface;
use Pinepain\JsSandbox\Specs\ObjectSpec;
use Pinepain\JsSandbox\Specs\ObjectSpecsCollectionInterface;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunction;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunctionInterface;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\Context;
use V8\FunctionObject;


class ModulesService
{
    /**
     * @var ObjectSpecBuilderInterface
     */
    private $object;
    /**
     * @var FunctionSpecBuilder
     */
    private $function;

    public function __construct(ObjectSpecBuilderInterface $object, FunctionSpecBuilder $function)
    {
        $this->object   = $object;
        $this->function = $function;
    }

    public function registerObjectSpecs(ObjectSpecsCollectionInterface $specs)
    {
        $module_object_spec = new ObjectSpec('Module', $this->object->build([
            'id'       => 'get: getId()',
            'filename' => 'get: getFilename()',
            // 'dirname'  => 'get: getDirname()', // NOTE: this is non-standard extension compared to node modules system
            'loaded'   => 'get: isLoaded()',
            'exports'  => 'get: getExports() set: setExports(raw)',
            'parent'   => 'get: getParent()',
            'children' => 'get: getChildren()',
        ]));

        $specs->put(Module::class, $module_object_spec);
    }

    public function createNativeFunctionWrapper(Context $context, RequireCallbackInterface $require_object, WrapperInterface $wrapper): NativeRequireFunctionWrapperInterface
    {
        $runtime_function = $this->createRuntimeFunction($require_object);
        $function_object  = $this->createFunctionObject($context, $runtime_function, $wrapper);

        return new NativeRequireFunctionWrapper($context->getIsolate(), $context, $function_object, $wrapper, $context->globalObject());
    }

    /**
     * @param Context                  $context
     * @param RuntimeFunctionInterface $runtime_function
     * @param WrapperInterface         $wrapper
     *
     * @return FunctionObject
     */
    protected function createFunctionObject(Context $context, RuntimeFunctionInterface $runtime_function, WrapperInterface $wrapper): FunctionObject
    {
        /** @var FunctionObject $res */
        $res = $wrapper->wrap($context->getIsolate(), $context, $runtime_function);

        return $res;
    }

    protected function createRuntimeFunction(RequireCallbackInterface $require_object): RuntimeFunctionInterface
    {
        $require_spec = $this->function->build('@inject-context (id: string)');

        $require_function_object_spec = new AnonymousObjectSpec($this->object->build([
            'main'    => 'get: getMain()',
            'resolve' => '(id: string)',
        ]));

        return new RuntimeFunction('require', [$require_object, 'callback'], $require_spec, $require_object, $require_function_object_spec);
    }
}
