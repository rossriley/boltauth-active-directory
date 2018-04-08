<?php

namespace Bolt\Extension\BoltAuth\ActiveDirectory\Controller;


use Bolt\Extension\BoltAuth\ActiveDirectory\Handler\ActiveDirectory;
use Bolt\Extension\BoltAuth\Auth\Controller\Authentication as BoltAuthAuthentication;
use Bolt\Extension\BoltAuth\Auth\Exception\DisabledProviderException;
use Bolt\Extension\BoltAuth\Auth\Exception\InvalidProviderException;
use Bolt\Extension\BoltAuth\Auth\Form\AuthForms;
use Silex\Application;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Authentication extends BoltAuthAuthentication
{
    /**
     * Login route. This jumps in front of the main BoltAuth controller and tests whether the domain is managed by
     * the ActiveDirectory plugin. If so, we do the authentication here, otherwise we pass back to the parent
     *
     * @param Application $app
     * @param Request $request
     *
     * @return Response
     * @throws InvalidProviderException
     * @throws DisabledProviderException
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
                return $this->activeDirectoryAuthentication($app, $request, $passwordForm);
            }

            return parent::login($app, $request);
        }

    }

    /**
     * @param Application $app
     * @param Request $request
     * @param FormInterface $passwordForm
     * @return mixed
     * @throws \Exception
     * @throws DisabledProviderException
     * @throws InvalidProviderException
     */
    protected function activeDirectoryAuthentication(Application $app, Request $request, FormInterface $passwordForm)
    {
        $this->getAuthOauthProviderManager()->setProvider($app, 'activedirectory');

        /** @var ActiveDirectory $handler */
        $handler = $this->getContainer()['auth.oauth.handler.activedirectory']();
        $handler->setSubmittedForm($passwordForm);

        // Initial login checks
        $response = $handler->login($request);
        if ($response instanceof Response) {
            return $response;
        }

        // Process and check password, initiate the session is successful
        $response = $handler->process($request);
        if ($response instanceof Response) {
            return $response;
        }

        $this->getAuthFeedback()->info('Login details are incorrect.');
    }
}