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


namespace Pinepain\JsSandbox\Specs\Builder;


use Pinepain\JsSandbox\Specs\Builder\Exceptions\ArgumentValueBuilderException;


class ArgumentValueBuilder implements ArgumentValueBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(string $definition, bool $with_literal)
    {
        if (is_numeric($definition)) {
            if (false !== strpos($definition, '.')) {
                return (float)$definition;
            }

            return (int)$definition;
        }

        switch (strtolower($definition)) {
            case 'null':
                return null;
            case 'true':
                return true;
            case 'false':
                return false;
        }

        if ($this->wrappedWith($definition, '[', ']')) {
            return [];
        }

        if ($this->wrappedWith($definition, '{', '}')) {
            return [];
        }

        foreach (['"', "'"] as $quote) {
            if ($this->wrappedWith($definition, $quote, $quote)) {
                return trim($definition, $quote);
            }
        }

        if (!$with_literal) {
            throw new ArgumentValueBuilderException("Unknown value format '{$definition}'");
        }

        return $definition;
    }

    private function wrappedWith(string $definition, string $starts, $ends)
    {
        if (strlen($definition) < 2) {
            return false;
        }

        return $starts == $definition[0] && $ends == $definition[-1];
    }
}
