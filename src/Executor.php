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


namespace Pinepain\JsSandbox;


use Pinepain\JsSandbox\Executors\ExecutorInterface;
use V8\Context;
use V8\Value;


class Executor
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var ExecutorInterface
     */
    private $string;
    /**
     * @var ExecutorInterface
     */
    private $file;

    /**
     * @param Context           $context
     * @param ExecutorInterface $string
     * @param ExecutorInterface $file
     *
     */
    public function __construct(Context $context, ExecutorInterface $string, ExecutorInterface $file)
    {
        $this->context = $context;
        $this->string  = $string;
        $this->file    = $file;
    }

    /**
     * @param string $string
     *
     * @return Value
     */
    public function executeString(string $string): Value
    {
        return $this->string->execute($this->context->getIsolate(), $this->context, $string);
    }

    /**
     * @param string $path
     *
     * @return Value
     */
    public function execute(string $path): Value
    {
        return $this->file->execute($this->context->getIsolate(), $this->context, $path);
    }
}
