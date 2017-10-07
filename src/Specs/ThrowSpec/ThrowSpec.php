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


namespace Pinepain\JsSandbox\Specs\ThrowSpec;


use Pinepain\JsSandbox\Specs\ExceptionHandlers\ExceptionHandlerInterface;


class ThrowSpec implements ThrowSpecInterface
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var ExceptionHandlerInterface
     */
    private $handler;

    /**
     * @param string                    $class
     * @param ExceptionHandlerInterface $handler
     */
    public function __construct(string $class, ExceptionHandlerInterface $handler)
    {
        $this->class   = $class;
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler(): ExceptionHandlerInterface
    {
        return $this->handler;
    }
}
