<?php

namespace Bolt\Extension\BoltAuth\ActiveDirectory;

use InvalidArgumentException;
use RuntimeException;

/**
 * Simple LDAP object to interact with LDAP
 *
 * @author Denis CLAVIER <clavierd at gmail dot com>
 */

class LDAP
{
    protected $ldap_server;

    /**
     * LDAP Resource
     *
     * @param string @hostname
     * Either a hostname or, with OpenLDAP 2.x.x and later, a full LDAP URI
     * @param int @port
     * An optional int to specify ldap server port
     *
     * Initiate LDAP connection by creating an associated resource
     * @throws \InvalidArgumentException
     */
    public function __construct($hostname, $port = 389)
    {
        if (!is_string($hostname))
        {
            throw new InvalidArgumentException('First argument to LDAP must be the hostname of a ldap server (string). Ex: ldap//example.com/ ');
        }

        if (!is_int($port))
        {
            throw new InvalidArgumentException('Second argument to LDAP must be the ldap server port (int). Ex : 389');
        }

        $this->ldap_server = ldap_connect($hostname, $port);
    }

    /**
     * @param string @user
     * A ldap username or email or sAMAccountName
     * @param string @password
     * An optional password linked to the user, if not provided an anonymous bind is attempted
     * @param string @search_attribute
     * The attribute used on your LDAP to identify user (uid, email, cn, sAMAccountName)
     * @param string @filter
     * An optional filter to search in LDAP (ex : objectClass = person).
     * @param string @base_dn
     * The LDAP base DN.
     * @param string @bind_dn
     * The directory name of a service user to bind before search. Must be a user with read permission on LDAP.
     * @param string @bind_pass
     * The password associated to the service user to bind before search.
     *
     * @return bool
     * TRUE if the user is identified and can access to the LDAP server
     * and FALSE if it isn't
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    public function checkLogin($user, $password = null, $search_attribute, $filter = null, $base_dn,$bind_dn, $bind_pass) {
        if (!is_string($user))
        {
            throw new InvalidArgumentException('First argument to LDAP/checkLogin must be the username or email of a ldap user (string). Ex: jdupont or jdupont@company.com');
        }
        if (!is_string($password) && $password !== null)
        {
            throw new InvalidArgumentException('Second argument to LDAP/checkLogin must be the password associated to the relative directory name (string).');
        }
        if (!is_string($search_attribute))
        {
            throw new InvalidArgumentException('Third argument to LDAP/checkLogin must be the attribute to identify users (ex : uid, email, sAMAccountName) (string).');
        }
        if (!is_string($filter) && $filter !== null)
        {
            throw new InvalidArgumentException('Fourth argument to LDAP/checkLogin must be an optional filter to search in LDAP (string).');
        }
        if (!is_string($base_dn))
        {
            throw new InvalidArgumentException('Fifth argument to LDAP/checkLogin must be the ldap base directory name (string). Ex: o=Company');
        }
        if (!is_string($bind_dn) && $bind_dn !== null)
        {
            throw new InvalidArgumentException('Sixth argument to LDAP/checkLogin must be an optional service account on restrictive LDAP (string).');
        }
        if (!is_string($bind_pass) && $bind_pass !== null)
        {
            throw new InvalidArgumentException('Seventh argument to LDAP/checkLogin must be an optional password for the service account on restrictive LDAP (string).');
        }

        // If LDAP service account for search is specified, do an ldap_bind with this account
        if ($bind_dn !== '' && $bind_dn !== null)
        {
            $bind_result=ldap_bind($this->ldap_server,$bind_dn,$bind_pass);

            // If authentication failed, throw an exception
            if (!$bind_result)
            {
                throw new RuntimeException('An error has occurred during ldap_bind execution. Please check parameter of LDAP/checkLogin, and make sure that user provided have read permission on LDAP.');
            }
        }
        if ($filter!=='' && $filter !== null)
        {
            $search_filter = '(&(' . $search_attribute . '=' . $user . ')(' . $filter .'))';
        }
        else
        {
            $search_filter = $search_attribute . '=' . $user;
        }


        $result = ldap_search($this->ldap_server, $base_dn, $search_filter, array(), 0, 1, 500);

        if (!$result)
        {
            throw new RuntimeException('An error has occured during ldap_search execution. Please check parameter of LDAP/checkLogin.');
        }

        $data = ldap_first_entry($this->ldap_server, $result);
        if (!$data)
        {
            throw new RuntimeException('An error has occured during ldap_first_entry execution. Please check parameter of LDAP/checkLogin.');
        }
        $dn = ldap_get_dn($this->ldap_server, $data);
        if (!$dn)
        {
            throw new RuntimeException('An error has occured during ldap_get_values execution (dn). Please check parameter of LDAP/checkLogin.');
        }

        return ldap_bind($this->ldap_server,$dn,$password);
    }

    /**
     * @param $base_dn
     * @param $filter
     * @param $bind_dn
     * @param $bind_pass
     * @param $search_attribute
     * @param $user
     * @return array An array with the user's mail and complete name.
     * An array with the user's mail and complete name.
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    public function getUserData($base_dn, $filter, $bind_dn, $bind_pass, $search_attribute, $user)
    {

        if (!is_string($base_dn))
        {
            throw new InvalidArgumentException('First argument to LDAP/getData must be the ldap base directory name (string). Ex: o=Company');
        }
        if (!is_string($filter))
        {
            throw new InvalidArgumentException('Second argument to LDAP/getData must be a filter to get relevant data. Often is the user id in ldap (string). Ex : uid=jdupont');
        }
        if (!is_string($bind_dn) && $bind_dn !== null)
        {
            throw new InvalidArgumentException('Third argument to LDAP/getData must be an optional service account on restrictive LDAP (string).');
        }
        if (!is_string($bind_pass) && $bind_pass !== null)
        {
            throw new InvalidArgumentException('Fourth argument to LDAP/getData must be an optional password for the service account on restrictive LDAP (string).');
        }
        if (!is_string($search_attribute))
        {
            throw new InvalidArgumentException('Fifth argument to LDAP/getData must be the attribute to identify users (ex : uid, email, sAMAccountName) (string).');
        }
        if (!is_string($user))
        {
            throw new InvalidArgumentException('Sixth argument to LDAP/getData must be the username or email of a ldap user (string). Ex: jdupont or jdupont@company.com');
        }

        // If LDAP service account for search is specified, do an ldap_bind with this account
        if ($bind_dn !== '' && $bind_dn !== null)
        {
            $bind_result=ldap_bind($this->ldap_server,$bind_dn,$bind_pass);

            // If authentication failed, throw an exception
            if (!$bind_result)
            {
                throw new RuntimeException('An error has occurred during ldap_bind execution. Please check parameter of LDAP/getData, and make sure that user provided have read permission on LDAP.');
            }
        }

        if ($filter!=='' && $filter !== null)
        {
            $search_filter = '(&(' . $search_attribute . '=' . $user . ')(' . $filter .'))';
        }
        else
        {
            $search_filter = $search_attribute . '=' . $user;
        }

        $result = ldap_search($this->ldap_server, $base_dn, $search_filter, array(), 0, 1, 500);

        if (!$result)
        {
            throw new RuntimeException('An error has occurred during ldap_search execution. Please check parameter of LDAP/getData.');
        }

        $data = ldap_first_entry($this->ldap_server, $result);
        if (!$data)
        {
            throw new RuntimeException('An error has occurred during ldap_first_entry execution. Please check parameter of LDAP/getData.');
        }

        $mail = ldap_get_values($this->ldap_server, $data, 'mail');
        if (!$mail)
        {
            throw new RuntimeException('An error has occurred during ldap_get_values execution (mail). Please check parameter of LDAP/getData.');
        }

        $cn = ldap_get_values($this->ldap_server, $data, 'cn');
        if (!$cn)
        {
            throw new RuntimeException('An error has occurred during ldap_get_values execution (complete name). Please check parameter of LDAP/getData.');
        }

        return array('mail' => $mail[0], 'cn' => $cn[0]);
    }

    /*
	 * Destructor to close the LDAP connection
     */
    public function __destruct()
    {
        ldap_unbind($this->ldap_server);
    }

}
