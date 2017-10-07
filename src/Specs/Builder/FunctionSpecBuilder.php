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


use Pinepain\JsSandbox\Specs\Builder\Exceptions\FunctionSpecBuilderException;
use Pinepain\JsSandbox\Specs\FunctionSpec;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Specs\ParametersList;
use Pinepain\JsSandbox\Specs\ParametersListInterface;
use Pinepain\JsSandbox\Specs\ReturnSpec\AnyReturnSpec;
use Pinepain\JsSandbox\Specs\ReturnSpec\ReturnSpecInterface;
use Pinepain\JsSandbox\Specs\ReturnSpec\VoidReturnSpec;
use Pinepain\JsSandbox\Specs\ThrowSpec\EchoThrowSpec;
use Pinepain\JsSandbox\Specs\ThrowSpec\ThrowSpecList;
use Pinepain\JsSandbox\Specs\ThrowSpec\ThrowSpecListInterface;


class FunctionSpecBuilder implements FunctionSpecBuilderInterface
{
    /**
     * @var ParameterSpecBuilderInterface
     */
    private $builder;
    /**
     * @var array
     */
    private $return_types;
    /**
     * @var string
     */
    private $default_return_type = 'any';


    public function __construct(ParameterSpecBuilderInterface $builder)
    {
        $this->builder = $builder;

        $this->return_types = [
            'any'  => new AnyReturnSpec(),
            'void' => new VoidReturnSpec(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $definition): FunctionSpecInterface
    {
        $definition = trim($definition);

        if (!$definition) {
            throw new FunctionSpecBuilderException('Definition must be non-empty string');
        }

        if (preg_match('/^(?<return>\w+\b)?\s*(?<needs_context>\!)?\s*\(\s*(?<params>([^,]+)(?:\s*,\s*[^,]+)*)?\s*\)\s*(?<throws>(\\\\?[a-z][\w\\\\]+)(?:\s*\|\s*(?-1))*)?\s*$/i', $definition, $matches)) {

            $needs_context = isset($matches['needs_context']) && $matches['needs_context'];

            $params = $this->getParametersList($matches['params'] ?? '');
            $return = $this->getReturnType(($matches['return'] ?? '') ?: $this->default_return_type);
            $throws = $this->getThrowsList($matches['throws'] ?? '');

            return new FunctionSpec($params, $throws, $return, $needs_context);
        }

        throw new FunctionSpecBuilderException("Unable to parse definition: '{$definition}'");
    }

    protected function getReturnType(string $definition): ReturnSpecInterface
    {
        if (!isset($this->return_types[$definition])) {
            throw new FunctionSpecBuilderException("Invalid return type: '{$definition}'");
        }

        return $this->return_types[$definition];
    }

    protected function getParametersList(string $definition): ParametersListInterface
    {
        $params = [];

        if ($definition) {
            $raw_params_definition = explode(',', $definition);
            foreach ($raw_params_definition as $param_definition) {
                $params[] = $this->builder->build(trim($param_definition));
            }
        }

        return new ParametersList(...$params);
    }

    protected function getThrowsList(string $definition): ThrowSpecListInterface
    {
        $specs = [];

        if ($definition) {
            $classes = array_filter(array_map('\trim', explode('|', $definition)));

            foreach ($classes as $class) {
                $specs[] = new EchoThrowSpec($class);
            }
        }

        return new ThrowSpecList(...$specs);
    }
}
