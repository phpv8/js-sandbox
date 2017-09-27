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


namespace Pinepain\JsSandbox\Specs;


use Pinepain\JsSandbox\Specs\Parameters\ParameterSpecInterface;


class ParametersList implements ParametersListInterface
{
    /**
     * @var int
     */
    private $number_of_parameters = 0;

    /**
     * @var int
     */
    private $number_of_required_parameters = 0;

    /**
     * @var bool
     */
    private $variadic = false;

    /**
     * @var ParameterSpecInterface[]
     */
    private $parameters;

    /**
     * @param ParameterSpecInterface[] ...$parameters
     */
    public function __construct(ParameterSpecInterface ...$parameters)
    {
        $this->parameters = $parameters;

        $this->number_of_parameters          = $this->getNumberOfParametersFromArray($parameters);
        $this->number_of_required_parameters = $this->getNumberOfRequiredParametersFromArray($parameters);
        $this->variadic                      = $this->getIsVariadicFromArray($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberOfParameters(): int
    {
        return $this->number_of_parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberOfRequiredParameters(): int
    {
        return $this->number_of_required_parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function isVariadic(): bool
    {
        return $this->variadic;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param ParameterSpecInterface[] $parameters
     *
     * @return int
     */
    protected function getNumberOfParametersFromArray(array $parameters): int
    {
        return count($parameters);
    }

    /**
     * @param ParameterSpecInterface[] $parameters
     *
     * @return int
     */
    protected function getNumberOfRequiredParametersFromArray(array $parameters): int
    {
        $required = 0;

        foreach ($parameters as $p) {
            if ($p->isOptional() || $p->isVariadic()) {
                break;
            }

            $required++;
        }

        return $required;
    }

    /**
     * @param ParameterSpecInterface[] $parameters
     *
     * @return bool
     */
    protected function getIsVariadicFromArray(array $parameters): bool
    {
        foreach ($parameters as $p) {
            if ($p->isVariadic()) {
                return true;
            }
        }

        return false;
    }
}
