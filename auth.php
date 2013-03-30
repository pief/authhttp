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
 * - all users are part of the DokuWiki group configured with DokuWiki's
 *   "defaultgroup" config setting
 * - users that are specified in the list configured with "specialusers" will
 *   also be member of the group configured with "specialgroup" (default: "admin")
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
    protected $specialusers;
    protected $specialgroup;

    /**
     * Constructor.
     */
    public function __construct() {
        global $conf;

        parent::__construct();

        /* Make sure that HTTP authentication has been enabled in the Web
           server. Note that does not seem to work with PHP >= 4.3.0 and safe
           mode enabled! */
        if ($_SERVER['PHP_AUTH_USER'] == "" || $_SERVER['PHP_AUTH_PW'] == "") {
            msg($this->getLang('nocreds'), -1);
            $this->success = false;
            return;
        }

        /* Load the config */
        $this->loadConfig();

        /* Set the config values */
        foreach (array("emaildomain", "specialusers", "specialgroup") as $cfgvar) {
            $this->$cfgvar = $this->getConf("$cfgvar");
            if (!$this->$cfgvar) {
                 msg("Config error: \"$cfgvar\" not set!", -1);
                 $this->success = false;
                 return;
            }
        }
        $this->specialusers = explode(" ", $this->specialusers);

        /* With HTTP authentication, there is no sense in allowing login any
           longer because our checkPass() below can't authenticate anyone but
           the already successfully authenticated user anyway.

           DokuWiki has no capability setting for 'login', so we need a little
           hack that pretends the admin disabled the login action himself. */
        $disableactions = explode(',', $conf['disableactions']);
        $disableactions = array_map('trim', $disableactions);
        if (!in_array('login', $disableactions)) {
            $disableactions[] = 'login';
        }
        $conf['disableactions'] = implode(',', $disableactions);

        /* We also can't support logout, but there's a capability bit for this. */
        $this->cando['logout'] = false;
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
        global $conf;

        $info['name'] = $user;
        $info['mail'] = $user."@".$this->emaildomain;
        $info['grps'] = array($conf['defaultgroup']);
        if (in_array($user, $this->specialusers)) {
            $info['grps'][] = $this->specialgroup;
        }

        return $info;
    }
}

// vim:ts=4:sw=4:et:
