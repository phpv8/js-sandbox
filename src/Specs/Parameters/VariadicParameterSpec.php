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


namespace PhpV8\JsSandbox\Specs\Parameters;


use PhpV8\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use PhpV8\JsSandbox\Specs\SpecException;


class VariadicParameterSpec extends AbstractParameterSpec
{
    /**
     * @param string                       $name
     * @param ExtractorDefinitionInterface $extractor
     */
    public function __construct(string $name, ExtractorDefinitionInterface $extractor)
    {
        parent::__construct($name, $extractor, true);
    }

    public function getDefaultValue()
    {
        throw new SpecException('Variadic parameter cannot have a default value');
    }
}
