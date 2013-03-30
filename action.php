<?php
/**
 * DokuWiki HTTP authentication plugin
 * https://www.dokuwiki.org/plugin:authhttp
 *
 * This is authhttp's action plugin which modifies DokuWiki's register form
 * so that the username is hard-coded to match the username from the HTTP
 * authentication.
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Pieter Hollants <pieter@hollants.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_authhttp extends DokuWiki_Action_Plugin {
    /**
     * Register the event handler
     */
    function register(&$controller){
        $controller->register_hook('HTML_REGISTERFORM_OUTPUT',
                                   'BEFORE',
                                   $this,
                                  'handle_registerform_output',
                                   Null);
    }

    /**
     * Event handler for the registration form
     */
    function handle_registerform_output(&$event, $param){
        /* Get an array representing the login name input field */
        $pos = $event->data->findElementByAttribute('name','login');
        if (!$pos)
            return;
        $elem = $event->data->getElementAt($pos);

        /* Hard-code the HTTP auth user name as login name to be registered */
        $elem["value"] = $_SERVER["PHP_AUTH_USER"];
        $elem["readonly"] = "readonly";

        /* Replace the field with our modified version */
        $event->data->replaceElement($pos, $elem);
    }
}
