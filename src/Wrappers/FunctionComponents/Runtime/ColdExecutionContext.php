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


namespace Pinepain\JsSandbox\Wrappers\FunctionComponents\Runtime;


use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Wrappers\Runtime\RuntimeFunction;
use Pinepain\JsSandbox\Wrappers\WrapperInterface;
use V8\FunctionCallbackInfo;


class ColdExecutionContext implements ColdExecutionContextInterface
{
    /**
     * @var WrapperInterface
     */
    private $wrapper;
    /**
     * @var RuntimeFunction
     */
    private $runtime_function;

    public function __construct(WrapperInterface $wrapper, RuntimeFunction $runtime_function)
    {
        $this->wrapper          = $wrapper;
        $this->runtime_function = $runtime_function;
    }

    public function getWrapper(): WrapperInterface
    {
        return $this->wrapper;
    }

    public function warm(FunctionCallbackInfo $args, FunctionSpecInterface $spec): ExecutionContextInterface
    {
        return new ExecutionContext($this->wrapper, $this->runtime_function, $args, $spec);
    }
}
