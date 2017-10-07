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


namespace Pinepain\JsSandbox\Wrappers\FunctionComponents;


use Pinepain\JsSandbox\Exceptions\NativeException;
use Pinepain\JsSandbox\Extractors\Definition\ExtractorDefinitionInterface;
use Pinepain\JsSandbox\Extractors\ExtractorException;
use Pinepain\JsSandbox\Extractors\ExtractorInterface;
use Pinepain\JsSandbox\Specs\FunctionSpecInterface;
use Pinepain\JsSandbox\Specs\Parameters\ParameterSpecInterface;
use V8\Context;
use V8\FunctionCallbackInfo;
use V8\Value;


class ArgumentsExtractor implements ArgumentsExtractorInterface
{
    /**
     * @var ExtractorInterface
     */
    private $extractor;

    public function __construct(ExtractorInterface $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(FunctionCallbackInfo $args, FunctionSpecInterface $spec): array
    {
        $parameters_list = $spec->getParameters();
        // this check should be done on higher level

        // this check is handled as missed required parameter exception
        // like https://wiki.php.net/rfc/too_few_args
        // We run this check as it doesn't make sense to start args unpacking if we know that function call requirements are not fulfilled
        if ($args->length() < $parameters_list->getNumberOfRequiredParameters()) {
            throw new NativeException("Too few arguments passed: expected at least {$parameters_list->getNumberOfRequiredParameters()}, given {$args->length()}");
        }

        // TODO: this is rather warning then exception
        //Method call uses 3 parameters, but method signature uses 3 parameters
        if ($args->length() > $parameters_list->getNumberOfParameters() && !$parameters_list->isVariadic()) {
            throw new NativeException("Too many arguments passed: expected no more than {$parameters_list->getNumberOfParameters()}, given {$args->length()}");
        }

        return $this->extractUnrolled($args->getContext(), $args->arguments(), $parameters_list->getParameters());
    }

    /**
     * @param Context                  $context
     * @param Value[]                  $arguments
     * @param ParameterSpecInterface[] $specs
     *
     * @return array
     * @throws NativeException
     */
    public function extractUnrolled(Context $context, array $arguments, array $specs)
    {
        $extracted_args = [];
        $position       = 0;

        /** @var ParameterSpecInterface $spec */
        foreach ($specs as $spec) {
            if ($spec->isVariadic()) {
                // current argument is variadic and the rest of passed arguments goes to it
                $variadic_args  = $this->extractVariadicArguments($context, $arguments, $spec->getExtractorDefinition(), $position);
                $extracted_args = array_merge($extracted_args, $variadic_args);
                // variadic argument is the last possible argument
                break;
            }

            // if no arguments left, we interpret any left as undefined
            if (empty($arguments)) {

                if (!$spec->isOptional()) {
                    throw new NativeException("Missing argument {$position}");
                }

                $extracted_args[] = $spec->getDefaultValue();
            } else {
                // extract current argument
                $argument         = array_shift($arguments);
                $extracted_args[] = $this->extractPlainArgument($context, $argument, $spec->getExtractorDefinition(), $position);
            }

            $position++;
        }

        return $extracted_args;
    }

    /**
     * @param Context                      $context
     * @param Value[]                      $arguments
     * @param ExtractorDefinitionInterface $definition
     * @param int                          $position
     *
     * @return array
     */
    public function extractVariadicArguments(Context $context, array $arguments, ExtractorDefinitionInterface $definition, int $position): array
    {
        $out = [];

        foreach ($arguments as $argument) {
            $out[] = $this->extractPlainArgument($context, $argument, $definition, $position++);
        }

        return $out;
    }

    /**
     * @param Context                      $context
     * @param Value                        $argument
     * @param ExtractorDefinitionInterface $definition
     * @param int                          $position
     *
     * @return mixed
     * @throws NativeException
     */
    public function extractPlainArgument(Context $context, Value $argument, ExtractorDefinitionInterface $definition, int $position)
    {
        try {
            return $this->extractor->extract($context, $argument, $definition);
        } catch (ExtractorException $e) {
            throw new NativeException("Failed to extract argument {$position}: {$e->getMessage()}");
        }
    }
}
