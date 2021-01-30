<?php
declare(strict_types=1);

namespace Iit\HyLib\Auth;

use Iit\HyLib\Auth\Annotation\GuardAnnotation;
use Iit\HyLib\Auth\Contract\Authenticatable;
use Iit\HyLib\Auth\Contract\StatefulGuard;
use Iit\HyLib\Auth\Contract\UserProvider;

/**
 * Class AccessTokenGuard
 * @package Iit\HyLib\Auth
 *
 * @GuardAnnotation("access-token")
 */
class AccessTokenGuard implements StatefulGuard
{
    use GuardHelpers;

    /**
     * @var AccessToken
     */

    protected $accessToken;

    /**
     * Get the currently authenticated user.
     *
     * @return null|Authenticatable
     */
    public function user()
    {
        if (is_null($this->user) && $userCode = $this->accessToken->getUserCode()) {
            $this->user = $this->provider->retrieveById($userCode);
        }
        return $this->user;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    /**
     * AccessTokenGuard constructor.
     * @param $config
     * @param UserProvider $provider
     */

    public function __construct($config, UserProvider $provider)
    {
        $this->accessToken = make(AccessToken::class, ['config' => $config]);
        $this->setProvider($provider);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        return false;
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param array $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        return false;
    }

    /**
     * Log a user into the application.
     *
     * @param Authenticatable $user
     * @param bool $remember
     */
    public function login(Authenticatable $user, $remember = false)
    {
        if ($this->accessToken->setUserCode($user->getAuthIdentifier())) {
            $this->user = $user;
        }
    }

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     * @param bool $remember
     * @return Authenticatable|false
     */
    public function loginUsingId($id, $remember = false)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }
        return false;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param mixed $id
     * @return bool
     */
    public function onceUsingId($id)
    {
        return false;
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        return false;
    }

    /**
     * Log the user out of the application.
     */
    public function logout()
    {
        $this->accessToken->clearToken();
        $this->user = null;
    }
}
