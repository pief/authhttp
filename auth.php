<?php
/**
 * DokuWiki HTTP authentication plugin
 * https://www.dokuwiki.org/plugin:authhttp
 *
 * This plugin basically replaces DokuWiki's own authentication features
 * with the HTTP authentication configured in the Webserver. As only login name and
 * password are known:
 * - the user's real name is set to his login name
 * - a possibly non-working email address is constructed with the "emaildomain"
 *   config setting
 * - all users are part of the DokuWiki group configured with the "group"
 *   config setting (default: "user")
 * - users that are specified in the list configured with "adminusers" will
 *   also be member of the group configured with "admingroup" (default: "admin")
 *
 * These restrictions may not suit your setup, in which case you should check out
 * the "authsplit" plugin at https://www.dokuwiki.org/plugin:authhttp.
 *
 * This plugin in based on the ideas in the "ggauth" auth backend by Grant Gardner
 * <grant@lastweekend.com.au>, https://www.dokuwiki.org/auth:ggauth.
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Pieter Hollants <pieter@hollants.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class auth_plugin_authhttp extends DokuWiki_Auth_Plugin {
    protected $emaildomain;
    protected $group;
    protected $adminusers;
    protected $admingroup;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        /* Load the config */
        $this->loadConfig();

	/* Set the config values */
        foreach (array("emaildomain", "group", "adminusers", "admingroup") as $cfgvar) {
            $this->$cfgvar = $this->getConf("$cfgvar");
            if (!$this->$cfgvar) {
                 msg("Config error: \"$cfgvar\" not set!", -1);
                 $this->success = false;
                 return;
            }
        }
	$this->adminusers = explode(" ", $this->adminusers);

        if ($_SERVER['PHP_AUTH_USER'] == "" || $_SERVER['PHP_AUTH_PW'] == "") {
            msg($this->getLang('nocreds'), -1);
            $this->success = false;
            return;
        }

	/* We do authentication only, so no capabilities are set */
    }

    /**
     * Check user+password
     *
     * @param   string $user the user name
     * @param   string $pass the clear text password
     * @return  bool
     */
    public function checkPass($user, $pass) {
        return ($user == $_SERVER['PHP_AUTH_USER'] && $pass == $_SERVER['PHP_AUTH_PW']);
    }

    /**
     * Return user info
     *
     * Returned info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email address of the user
     * grps array   list of groups the user is in
     *
     * @param   string $user the user name
     * @return  array containing user data or false
     */
    public function getUserData($user) {
        $info['name'] = $user;
        $info['mail'] = $user."@".$this->emaildomain;
        $info['grps'] = array($this->group);
        if (in_array($user, $this->adminusers)) {
            $info['grps'][] = $this->admingroup;
        }

        return $info;
    }
}

// vim:ts=4:sw=4:et:
