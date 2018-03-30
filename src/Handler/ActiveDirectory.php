<?php

namespace Bolt\Extension\BoltAuth\ActiveDirectory\Handler;


use Bolt\Extension\BoltAuth\Auth\Exception\DisabledProviderException;
use Bolt\Extension\BoltAuth\Auth\Exception\InvalidAuthorisationRequestException;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Handler\HandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class ActiveDirectory implements HandlerInterface
{

    /**
     * Login a client.
     *
     * @param Request $request
     *
     * @throws DisabledProviderException
     * @throws InvalidAuthorisationRequestException
     */
    public function login(Request $request)
    {
        // TODO: Implement login() method.
    }

    /**
     * Process a OAuth2 provider login callback.
     *
     * @param Request $request
     * @param string $grantType
     */
    public function process(Request $request, $grantType)
    {
        // TODO: Implement process() method.
    }

    /**
     * Logout a client.
     *
     * @param Request $request
     */
    public function logout(Request $request)
    {
        // TODO: Implement logout() method.
    }
}