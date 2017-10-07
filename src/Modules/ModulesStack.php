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


class ModulesStack implements ModulesStackInterface
{
    private $stack = [];

    /**
     * {@inheritdoc}
     */
    public function push(ModuleInterface $module)
    {
        $this->stack[] = $module;
    }

    /**
     * {@inheritdoc}
     */
    public function pop(): ModuleInterface
    {
        return array_pop($this->stack);
    }

    /**
     * {@inheritdoc}
     */
    public function top(): ?ModuleInterface
    {
        return reset($this->stack) ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function bottom(): ?ModuleInterface
    {
        return end($this->stack) ?: null;
    }
}
