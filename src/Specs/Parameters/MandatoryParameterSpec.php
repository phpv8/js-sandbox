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
use Pinepain\JsSandbox\Specs\SpecException;


class MandatoryParameterSpec extends AbstractParameterSpec
{
    /**
     * @param string                       $name
     * @param ExtractorDefinitionInterface $extractor
     */
    public function __construct(string $name, ExtractorDefinitionInterface $extractor)
    {
        parent::__construct($name, $extractor);
    }

    public function getDefaultValue()
    {
        throw new SpecException('Mandatory parameter cannot have a default value');
    }
}
