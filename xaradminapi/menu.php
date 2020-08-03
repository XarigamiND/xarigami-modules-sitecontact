<?php
/**
 * Common admin menu
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
 * Generate the common admin menu configuration
 * DEPRECATED Mar 09
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
function sitecontact_adminapi_menu()
{
    /* Initialise the array that will hold the menu configuration */
    $menu = array();

    /* Specify the menu title to be used in your blocklayout template */
    $menu['menutitle'] = xarML('SiteContact Administration');

    /* Specify the menu labels to be used in your blocklayout template
     * Preset some status variable
     */
    $menu['status'] = '';

    return $menu;
} 
?>