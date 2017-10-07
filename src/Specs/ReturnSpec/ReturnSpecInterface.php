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


namespace Pinepain\JsSandbox\Specs\ReturnSpec;


interface ReturnSpecInterface
{
    //public function getType() : string; // TODO: maybe used for further use, e.g. docs generation

    //public function validate($value) : bool;
    //
    //public function convert(Isolate $isolate, Context $context, $value) : Value;

    public function allowsNull(): bool;

    public function prefersUndefined(): bool;

    //public function isVoid() : bool;
    //public function isScalar() : bool;

    //public function isUndefined() : bool;
    //public function isNull() : bool;
    //public function isBool() : bool;
    //public function isNumeric() : bool;
    //public function isString() : bool;
    //public function isArray() : bool;
    //public function isObject() : bool;
    //
    //public function getClass() : string;

    //public function process(Isolate $isolate, Context $context, ReturnValue $retval, $value) : Value;
    //public function convert(Isolate $isolate, Context $context, $value) : Value;
    //public function isVirtual() : bool;
}
