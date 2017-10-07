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


interface CallbackGuardInterface
{
    /**
     * Wrap callback to catch all PHP exception and throw them back to js.
     *
     * Returned callback could be safely invoked from js runtime with a guarantee
     * that there will be no chained uncaught PHP exception
     *
     * @param callable $callback
     *
     * @return callable
     */
    public function guard(callable $callback): callable;
}
