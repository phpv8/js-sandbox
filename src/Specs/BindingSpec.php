<?php declare(strict_types=1);

/*
 * This file is part of the pinepain/php-v8 PHP extension.
 *
 * Copyright (c) 2015-2017 Bogdan Padalko <pinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace Pinepain\JsSandbox\Specs;


class BindingSpec implements BindingSpecInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var FunctionSpecInterface|PropertySpecInterface
     */
    private $spec;

    /**
     * @param string                                      $name
     * @param PropertySpecInterface|FunctionSpecInterface $spec
     */
    public function __construct(string $name, $spec)
    {
        $this->name = $name;
        $this->spec = $spec;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpec()
    {
        return $this->spec;
    }
}
