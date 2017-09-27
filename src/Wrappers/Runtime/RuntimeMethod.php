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


use Ref\WeakReference;
use V8\ObjectValue;


class RuntimeMethod
{
    private $reference;
    private $object;

    public function __construct(WeakReference $reference, ObjectValue $object)
    {
        $this->reference = $reference;
        $this->object    = $object;
    }

    /**
     * @return WeakReference
     */
    public function getReference(): WeakReference
    {
        return $this->reference;
    }

    /**
     * @return ObjectValue
     */
    public function getObject(): ObjectValue
    {
        return $this->object;
    }
}
