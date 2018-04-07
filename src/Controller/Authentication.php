<?php

namespace Bolt\Extension\BoltAuth\ActiveDirectory\Controller;


use Bolt\Extension\BoltAuth\Auth\Controller\Authentication as BoltAuthAuthentication;
use Bolt\Extension\BoltAuth\Auth\Form\AuthForms;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Authentication extends BoltAuthAuthentication
{
    /**
     * Login route. This jumps in front of the main BoltAuth controller and tests whether the domain is managed by
     * the ActiveDirectory plugin. If so, we do the authentication here, otherwise we pass back to the parent
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function login(Application $app, Request $request)
    {
        $config = $this->getAuthConfig();

        $builder = $this->getAuthFormsManager()->getFormLogin($request);
        $passwordForm = $builder->getForm(AuthForms::LOGIN_PASSWORD);
        if ($passwordForm->isValid()) {

            $adConfig = $config->getProvider('activedirectory');
            $settings = $adConfig->getOptions();

            $loginEmail = $passwordForm->get('email')->getData();
            $loginDomain = substr($loginEmail ,strpos($loginEmail , '@') +1, 255);

            if ($loginDomain === $settings['account_domain']) {
                return $this->activeDirectoryAuthentication($request);
            }

            return parent::login($app, $request);
        }

    }

    protected function activeDirectoryAuthentication($request)
    {
        dump("we gonna do some active directory"); exit;
        $this->getAuthOauthProviderManager()->setProvider($app, 'local');
    }
}