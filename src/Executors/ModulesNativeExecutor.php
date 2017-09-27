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


namespace Pinepain\JsSandbox\Executors;


use League\Flysystem\Util;
use Pinepain\JsSandbox\Modules\NativeRequireFunctionWrapperInterface;
use V8\Context;
use V8\Isolate;
use V8\Value;


class ModulesNativeExecutor implements ExecutorInterface
{
    /**
     * @var NativeRequireFunctionWrapperInterface
     */
    private $require;

    public function __construct(NativeRequireFunctionWrapperInterface $require)
    {
        $this->require = $require;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Isolate $isolate, Context $context, string $value): Value
    {
        $path = Util::normalizePath($value);

        return $this->require->call($path);
    }
}
