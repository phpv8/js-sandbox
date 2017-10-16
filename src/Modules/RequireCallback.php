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


use League\Flysystem\Util;
use Pinepain\JsSandbox\Exceptions\NativeException;
use Pinepain\JsSandbox\Modules\Repositories\NativeModulesRepositoryInterface;
use Pinepain\JsSandbox\Modules\Repositories\SourceModulesRepositoryInterface;
use Pinepain\JsSandbox\Wrappers\FunctionComponents\Runtime\ExecutionContextInterface;
use Throwable;
use V8\ObjectValue;
use function strlen;


class RequireCallback implements RequireCallbackInterface
{
    /**
     * @var ModulesCacheInterface
     */
    private $cache;
    /**
     * @var ModulesStackInterface
     */
    private $stack;
    /**
     * @var NativeModulesRepositoryInterface
     */
    private $native;
    /**
     * @var SourceModulesRepositoryInterface
     */
    private $source;

    public function __construct(ModulesCacheInterface $cache, ModulesStackInterface $stack, NativeModulesRepositoryInterface $native, SourceModulesRepositoryInterface $source)
    {
        $this->cache  = $cache;
        $this->stack  = $stack;
        $this->native = $native;
        $this->source = $source;
    }

    public function callback(ExecutionContextInterface $execution, string $id)
    {
        $id = $this->resolve($id);

        if ($this->cache->has($id)) {
            return $this->cache->get($id)->getExports();
        }

        // TODO: should we freeze export if it's an object?
        //https://codereview.chromium.org/1889903003/diff/1/include/v8.h

        if ($this->native->has($id)) {
            return $this->handleNativeModule($execution, $id, $this->native->get($id), $this->stack->top());
        }

        if ($this->source->has($id)) {
            return $this->handleSourceModule($execution, $id, $this->source->get($id), $this->stack->top());
        }

        throw new NativeException("Cannot find module '{$id}'");
    }

    public function resolve(string $id)
    {
        $id = trim($id);

        if (strlen($id) && ('/' != $id[0]) && $top = $this->stack->top()) {
            assert(null !== $top);
            // relative path, try to append directory from current top module, if available
            $id = $top->getDirname() . '/' . $id;
        }

        return Util::normalizePath($id);
    }

    public function getMain(): ?ModuleInterface
    {
        return $this->stack->bottom();
    }

    protected function buildNativeModule(string $filename, ?ModuleInterface $top): ModuleInterface
    {
        $id     = $filename;
        $module = new Module($id, $filename, Util::dirname($filename), $top);

        return $module;
    }

    protected function buildModule(string $filename, ?ModuleInterface $top): ModuleInterface
    {
        $id = $filename;

        if (null === $top) {
            $id = '.';
        }

        $module = new Module($id, $filename, Util::dirname($filename), $top);

        if ($top) {
            if ($top->isLoaded()) {
                // UNLIKELY
                // this should never happens, but just in case
                throw new NativeException('Modifying loaded module is not allowed');
            }

            // add current module as top one child
            $top->addChild($module);
        }

        return $module;
    }

    protected function handleNativeModule(ExecutionContextInterface $execution, string $id, NativeModulePrototypeInterface $prototype, ?ModuleInterface $top)
    {
        $context = $execution->getContext();

        $module = $this->buildNativeModule($id, $top);

        $exports = new ObjectValue($context);
        $module->setExports($exports);

        $this->cache->put($id, $module);

        try {
            $prototype->compile($execution->getIsolate(), $context, $module);
            $module->setLoaded();
        } catch (Throwable $e) {
            $this->cache->remove($id);
            throw $e;
        }

        return $module->getExports();
    }

    protected function handleSourceModule(ExecutionContextInterface $execution, string $id, SourceModuleBuilderInterface $builder, ?ModuleInterface $top)
    {
        $module = $this->buildModule($id, $top);

        $this->stack->push($module); // put current module at the top

        $context = $execution->getContext();

        $exports = new ObjectValue($context);
        $module->setExports($exports);

        $this->cache->put($id, $module);

        try {
            $function = $builder->build($execution->getIsolate(), $context, $module);

            // Alternatively:
            //$native = new NativeFunctionWrapper($context, $exports, $function, $execution->getWrapper());
            //$native->call($exports, $execution->getFunctionObject(), $module, $module->getFilename(), $module->getDirname())

            // Even shorter alternative
            //$native = $execution->wrapNativeFunction($exports, $function);
            //$native->call($exports, $execution->getFunctionObject(), $module, $module->getFilename(), $module->getDirname())

            // NOTE: we need to wrap all this values as we will manually call V8 FunctionObject
            $require    = $execution->getFunctionObject();
            $js_module  = $execution->wrap($module);
            $__filename = $execution->wrap($module->getFilename());
            $__dirname  = $execution->wrap($module->getDirname());

            // exports, require, module, __filename, __dirname
            $args = [$exports, $require, $js_module, $__filename, $__dirname];

            $function->call($context, $exports, $args);
            //$function->Call($context, $execution->getThis(), $args);
            $module->setLoaded();
        } catch (Throwable $e) {
            $this->cache->remove($id);
            throw $e;
        } finally {
            $this->stack->pop(); // remove current module from the top
        }

        return $module->getExports();
    }
}
