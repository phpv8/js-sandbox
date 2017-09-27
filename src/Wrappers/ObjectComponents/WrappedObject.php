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


namespace Pinepain\JsSandbox\Wrappers\ObjectComponents;


use Pinepain\JsSandbox\Specs\ObjectSpecInterface;
use V8\ObjectValue;


class WrappedObject
{
    /**
     * @var ObjectValue
     */
    private $value;
    /**
     * @var object
     */
    private $object;
    /**
     * @var null|ObjectSpecInterface
     */
    private $spec;

    /**
     * @param ObjectValue              $value
     * @param                          $object
     * @param null|ObjectSpecInterface $spec
     */
    public function __construct(ObjectValue $value, $object, ?ObjectSpecInterface $spec = null)
    {
        $this->value  = $value;
        $this->object = $object;
        $this->spec   = $spec;
    }

    /**
     * @return ObjectValue
     */
    public function getValue(): ObjectValue
    {
        return $this->value;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return null|ObjectSpecInterface
     */
    public function getSpec(): ?ObjectSpecInterface
    {
        return $this->spec;
    }
}
