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


use League\Flysystem\FilesystemInterface;
use Pinepain\JsSandbox\Exceptions\NativeException;
use V8\Context;
use V8\FunctionObject;
use V8\Isolate;
use V8\Script;
use V8\ScriptOrigin;
use V8\StringValue;


class SourceModuleBuilder implements SourceModuleBuilderInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    private $source_wrapper = [
        "(function (exports, require, module, __filename, __dirname) {\n",
        // Your module code actually lives in here
        "\n});",
    ];

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function build(Isolate $isolate, Context $context, ModuleInterface $module): FunctionObject
    {
        $source = $this->source_wrapper[0] . $this->filesystem->read($module->getFilename()) . $this->source_wrapper[1];

        $source = new StringValue($isolate, $source);
        $origin = new ScriptOrigin($module->getFilename(), 1);
        $script = new Script($context, $source, $origin);

        $function = $script->run($context);

        if (!($function instanceof FunctionObject)) {
            // UNLIKELY
            throw new NativeException('Malformed module source code');
        }

        return $function;
    }
}
