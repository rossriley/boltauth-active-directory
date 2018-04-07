<?php

namespace Bolt\Extension\BoltAuth\ActiveDirectory\Provider;

use Bolt\Extension\BoltAuth\Auth\Oauth2\Client\Provider\Local;
use League\OAuth2\Client\Token\AccessToken;

class ActiveDirectory extends Local
{

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new ActiveDirectoryResourceOwner($response, $token->getResourceOwnerId());
    }

}