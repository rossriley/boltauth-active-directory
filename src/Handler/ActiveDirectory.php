<?php

namespace Bolt\Extension\BoltAuth\ActiveDirectory\Handler;


use Bolt\Extension\BoltAuth\ActiveDirectory\LDAP;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Handler\HandlerInterface;
use Bolt\Extension\BoltAuth\Auth\Oauth2\Handler\Local;
use RuntimeException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ActiveDirectory extends Local implements HandlerInterface
{

    /** @var FormInterface */
    protected $submittedForm;

    /**
     * Login a client.
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function login(Request $request)
    {

        if ($this->submittedForm === null) {
            throw new RuntimeException(sprintf('%s requires a %s object to be set.', __METHOD__, Form::class));
        }

        $adConfig = $this->config->getProvider('activedirectory');
        $serverConfig = $adConfig->getOptions()['server'];

        if (!isset($serverConfig['domain'])) {
            throw new RuntimeException(sprintf('%s via Active Directory requires a %s configuration to be set.', __METHOD__, 'server:domain'));
        }

        if (!isset($serverConfig['org'])) {
            throw new RuntimeException(sprintf('%s via Active Directory requires a %s configuration to be set.', __METHOD__, 'server:org'));
        }
        if (!isset($serverConfig['user'])) {
            throw new RuntimeException(sprintf('%s via Active Directory requires a %s configuration to be set.', __METHOD__, 'server:user'));
        }

        $domain = $serverConfig['domain'];
        $port = $serverConfig['port'] ?: null;
        $ou = $serverConfig['ou'] ?: 'Users';
        $org = $serverConfig['org'];
        $adminUser = $serverConfig['user'];
        $adminPass = $serverConfig['password'];
        $dcs = $serverConfig['dc'];
        $bindDN = sprintf('uid=%s,ou=%s,o=%s,%s', $adminUser, $ou, $org, 'dc='. implode(',dc=', $dcs));
        $baseDN = sprintf('ou=%s,o=%s,%s', $adminUser, $ou, $org, 'dc='. implode(',dc=', $dcs));


        $email = $this->submittedForm->get('email')->getData();
        $username = strstr($email, '@', true);
        $password = $this->submittedForm->get('password')->getData();
        $ldap = new LDAP($domain, $port, [LDAP_OPT_PROTOCOL_VERSION => 3]);
        $authUser = $ldap->checkLogin($username, $password, 'uid', 'objectClass=inetOrgPerson', $baseDN, $bindDN, $adminPass);

        dump($authUser); exit;
    }

    /**
     * Process a OAuth2 provider login callback.
     *
     * @param Request $request
     * @param string $grantType
     */
    public function process(Request $request, $grantType = 'password')
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

    /**
     * @param Form $submittedForm
     */
    public function setSubmittedForm(Form $submittedForm)
    {
        $this->submittedForm = $submittedForm;
    }
}