<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Iit\HyLib\Auth\Contract;

/**
 * Interface HttpAuthContract.
 *
 * @method bool check()
 * @method bool guest()
 *
 * @see Guard
 * @method null|Authenticatable user()
 * @method null|int|string id()
 * @method null|string name()
 * @method bool validate(array $credentials = [])
 * @method setUser(Authenticatable $user)
 *
 * @see StatefulGuard
 * @method bool attempt(array $credentials = [], $remember = false)
 * @method bool once(array $credentials = [])
 * @method login(Authenticatable $user, $remember = false)
 * @method Authenticatable loginUsingId($id, $remember = false)
 * @method bool onceUsingId($id)
 * @method viaRemember()
 * @method logout()
 */
interface HttpAuthContract
{
    /**
     * @param null|string $name
     * @return Guard
     */
    public function guard($name = null): Guard;

    /**
     * @param string $name
     */
    public function shouldUse($name);
}
