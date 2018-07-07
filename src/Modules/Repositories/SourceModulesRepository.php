<?php declare(strict_types=1);

/*
 * This file is part of the pinepain/js-sandbox PHP library.
 *
 * Copyright (c) 2016-2017 Bogdan Padalko <thepinepain@gmail.com>
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source or visit
 * http://opensource.org/licenses/MIT
 */


namespace PhpV8\JsSandbox\Modules\Repositories;


use League\Flysystem\FilesystemInterface;
use PhpV8\JsSandbox\Modules\SourceModuleBuilder;
use PhpV8\JsSandbox\Modules\SourceModuleBuilderInterface;
use UnexpectedValueException;


class SourceModulesRepository implements SourceModulesRepositoryInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;


    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function has(string $id): bool
    {
        return $this->filesystem->has($id);
    }

    public function get(string $id): SourceModuleBuilderInterface
    {
        if (!$this->has($id)) {
            throw new UnexpectedValueException('Source module not found');
        }

        return new SourceModuleBuilder($this->filesystem);
    }
}
