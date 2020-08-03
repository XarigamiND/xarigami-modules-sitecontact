<?php
/**
 * Overview menu link
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/**
 * The displays the overview in the menu
 * @author Jo Dalle Nogare
 */
function sitecontact_admin_overview()
{
    if (!xarSecurityCheck('AdminSiteContact')) return;

    $data=array();
    //common menulink
    $data['menulinks'] = xarModAPIFunc('sitecontact','admin','getmenulinks');
    /* let's just display the main module overview */
    return xarTplModule('sitecontact', 'admin', 'main',$data,'main');
}

?>