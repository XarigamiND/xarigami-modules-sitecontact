<?php
/**
 * Xarigami Sitecontact module version information
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage SiteContact Module
 * @copyright (C) 2004-2012 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

 /**
  * Version information on this module
  * @param none
  * @return version information of this module
  */
$modversion['name']           = 'sitecontact';
$modversion['id']             = '890';
$modversion['version']        = '1.3.3';
$modversion['displayname']    = 'SiteContact';
$modversion['description']    = 'Contact and other forms with email';
$modversion['credits']        = 'xardocs/credits.txt';
$modversion['help']           = 'xardocs/help.txt';
$modversion['changelog']      = 'xardocs/changelog.txt';
$modversion['license']        = 'xardocs/license.txt';
$modversion['official']       = 1;
$modversion['author']         = 'Jo Dalle Nogare, <icedlava@2skies.com>';
$modversion['contact']        = 'http://xarigami.com/';
$modversion['homepage']       = 'http://xarigami.com/project/sitecontact';
$modversion['admin']          = 1;
$modversion['user']           = 1;
$modversion['class']          = 'Complete';
$modversion['category']       = 'Content';
$modversion['dependency']     = array(182); //earlier format
$modversion['dependencyinfo']   = array(
                                    0 => array(
                                            'name' => 'core',
                                            'version_ge' => '1.4.0'
                                         ),
                                    182 => array(
                                            'name' => 'dynamicdata',
                                            'version_ge' => '1.4.0'
                                        )
                                );
if (false) { //Load and translate once
    xarML('SiteContact');
    xarML('Contact and other forms with email');
}

?>