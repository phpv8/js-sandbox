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


namespace Pinepain\JsSandbox\Specs;


use Pinepain\JsSandbox\Decorators\DecoratorSpecInterface;
use Pinepain\JsSandbox\Specs\ReturnSpec\ReturnSpecInterface;
use Pinepain\JsSandbox\Specs\ThrowSpec\ThrowSpecListInterface;


class FunctionSpec implements FunctionSpecInterface
{
    /**
     * @var ParametersListInterface
     */
    private $parameters;
    /**
     * @var ThrowSpecListInterface
     */
    private $exceptions;

    /**
     * @var ReturnSpecInterface
     */
    private $return;
    /**
     * @var array|DecoratorSpecInterface[]
     */
    private $decorators;

    /**
     * @param ParametersListInterface $parameters
     * @param ThrowSpecListInterface $exceptions
     * @param ReturnSpecInterface $return
     * @param DecoratorSpecInterface[] $decorators
     */
    public function __construct(ParametersListInterface $parameters, ThrowSpecListInterface $exceptions, ReturnSpecInterface $return, array $decorators = [])
    {
        $this->parameters = $parameters;
        $this->exceptions = $exceptions;
        $this->return     = $return;
        $this->decorators = $decorators;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): ParametersListInterface
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getExceptions(): ThrowSpecListInterface
    {
        return $this->exceptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturn(): ReturnSpecInterface
    {
        return $this->return;
    }

    /**
     * {@inheritdoc}
     */
    public function getDecorators(): array
    {
        return $this->decorators;
    }

}
