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


namespace Pinepain\JsSandbox\Specs\Parameters;


use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;


// don't do full args validation, just necessary minimum for converting from JS to PHP
interface ParameterSpecInterface
{
    public function getExtractorDefinition(): ExtractorDefinitionInterface;

    public function getName(): string; // We may use it later to generate function description

    public function isOptional(): bool; // when undefined passed we may use optional value, if it available

    // TODO:  to generate js function toString() text we may keep this value compatible with default arg standard
    public function getDefaultValue(); // if variadic, [] is default, every variadic is optional.

    public function isVariadic(): bool;
//
//    public function isVirtual() : bool; // TODO: virtual parameter will simply return what user set via $this->getDefaultValue()
}
