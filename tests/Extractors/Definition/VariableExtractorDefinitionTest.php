<?php declare(strict_types=1);

/**
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


namespace Pinepain\JsSandbox\Tests\Extractors\Definition;


use PHPUnit\Framework\TestCase;
use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\VariableExtractorDefinition;


class VariableExtractorDefinitionTest extends TestCase
{
    public function test()
    {
        $variations = [
            $this->createMock(ExtractorDefinitionInterface::class),
            $this->createMock(ExtractorDefinitionInterface::class),
        ];

        $extractor = new VariableExtractorDefinition(...$variations);

        $this->assertNull($extractor->getName());
        $this->assertNull($extractor->getNext());
        $this->assertSame($variations, $extractor->getVariations());
    }
}
