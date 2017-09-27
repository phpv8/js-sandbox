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


use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Specs\ObjectSpecInterface;


class RuntimeFunction implements RuntimeFunctionInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var callable
     */
    private $callback;
    /**
     * @var FunctionSpecInterface
     */
    private $spec;
    /**
     * @var null|object
     */
    private $object;
    /**
     * @var null|ObjectSpecInterface
     */
    private $object_spec;

    public function __construct(string $name, callable $callback, FunctionSpecInterface $spec, $object = null, ?ObjectSpecInterface $object_spec = null)
    {
        $this->name        = $name;
        $this->callback    = $callback;
        $this->spec        = $spec;
        $this->object      = $object;
        $this->object_spec = $object_spec;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @return FunctionSpecInterface
     */
    public function getSpec(): FunctionSpecInterface
    {
        return $this->spec;
    }

    /**
     * @return null|object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return null|ObjectSpecInterface
     */
    public function getObjectSpec(): ?ObjectSpecInterface
    {
        return $this->object_spec;
    }
}
