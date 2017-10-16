<?php declare(strict_types=1);

/**
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


namespace Pinepain\JsSandbox\Tests\Extractors\Definition;


use PHPUnit\Framework\TestCase;
use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\Definition\PlainExtractorDefinition;


class PlainExtractorDefinitionTest extends TestCase
{
    public function testWithNext()
    {
        $name = 'test';
        $next = $this->createMock(ExtractorDefinitionInterface::class);

        $extractor = new PlainExtractorDefinition($name, $next);

        $this->assertSame($name, $extractor->getName());
        $this->assertSame($next, $extractor->getNext());
        $this->assertSame([$extractor], $extractor->getVariations());
    }

    public function testWithoutNext()
    {
        $name = 'test';

        $extractor = new PlainExtractorDefinition($name);

        $this->assertSame($name, $extractor->getName());
        $this->assertNull($extractor->getNext());
        $this->assertSame([$extractor], $extractor->getVariations());
    }
}
