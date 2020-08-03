<?php
/**
 * Common menu configuration 
 *
 * @copyright (C) 2004-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/**
 * generate the common menu configuration
 * @author Jo Dalle Nogare
 */
function sitecontact_userapi_menu()
{ 
    /* Initialise the array that will hold the menu configuration */
    $menu = array();
    /* Specify the menu title to be used in your blocklayout template */
    $menu['menutitle'] = xarML('ContactUs');

    /* Specify the menu items to be used in your blocklayout template */
    $menu['menulabel_view'] = xarML('Contact');
    $menu['menulink_view'] = xarModURL('sitecontact', 'user', 'main');
    return $menu;
}
?>