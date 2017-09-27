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


class Module implements ModuleInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    private $dirname;

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var null|ModuleInterface
     */
    protected $parent;

    /**
     * @var ModuleInterface[]
     */
    protected $children = [];

    /**
     * @var mixed
     */
    protected $exports;


    public function __construct(string $id, string $filename, string $dirname, ?ModuleInterface $parent)
    {
        $this->id       = $id;
        $this->filename = $filename;
        $this->dirname  = $dirname;
        $this->parent   = $parent;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getDirname(): string
    {
        return $this->dirname;
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function getParent(): ?ModuleInterface
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getExports()
    {
        return $this->exports;
    }

    public function setLoaded()
    {
        $this->loaded = true;
    }

    public function setExports($exports)
    {
        $this->exports = $exports;
    }

    public function addChild(ModuleInterface $module)
    {
        $this->children[] = $module;
    }
}
//module.id
//module.filename
//module.loaded

//module.parent
//module.children
//module.exports
