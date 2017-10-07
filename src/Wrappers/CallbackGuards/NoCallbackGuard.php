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


namespace Pinepain\JsSandbox\Wrappers\CallbackGuards;


class NoCallbackGuard implements CallbackGuardInterface
{
    /**
     * {@inheritdoc}
     */
    public function guard(callable $callback): callable
    {
        //NOTE: Use at your own risk! As it does not re-throw exception back to V8, js exection continues, but as PHP
        //      stays in unclean state it callbacks won't be run and thus js will get undefined value instead of
        //      expected result, which will yield js-originated exceptions, which in turn will result in PHP exception
        //      which will replace original PHP exception that caused problem and set it as Exception::$previous.
        return $callback;
    }
}
