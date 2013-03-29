<?php
/**
 * Options for the authhttp plugin
 *
 * @author Pieter Hollants <pieter@hollants.com>
 */

$meta['emaildomain'] = array('string');
$meta['group']       = array('string', '_cautionList' => array('plugin____authhttp____group' => 'danger'));
$meta['adminusers']  = array('string', '_cautionList' => array('plugin____authhttp____adminusers' => 'danger'));
$meta['admingroup']  = array('string', '_cautionList' => array('plugin____authhttp____admingroup' => 'danger'));
