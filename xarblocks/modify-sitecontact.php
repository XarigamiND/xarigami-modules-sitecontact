<?php
/**
 * SiteContact Form Block
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
 * Modify sitecontact block settings
 */
function sitecontact_sitecontactblock_modify($blockinfo)
{

    if (!is_array($blockinfo['content'])) {
        $vars = @unserialize($blockinfo['content']);
    } else {
        $vars = $blockinfo['content'];
    }

    if (!isset($vars['formchoice'])) {
        $vars['formchoice'] = xarModGetVar('sitecontact','defaultform');
    }

    if (!isset($vars['showdd'])) {
        $vars['showdd'] = false;
    }

    $vars['formtypes'] = xarModAPIFunc('sitecontact', 'user', 'getcontacttypes');

    $vars['blockid'] = $blockinfo['bid'];

    // Return output
    return $vars;
}

/**
 * Update sitecontact block settings
 */
function sitecontact_sitecontactblock_update($blockinfo)
{
    $defaultformid = xarModGetVar('sitecontact','defaultform');
    if (!xarVarFetch('showdd', 'checkbox', $vars['showdd'], false, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('formchoice', 'id', $vars['formchoice'],$defaultformid, XARVAR_NOT_REQUIRED)) {return;}

   $blockinfo['content'] = $vars;

    return $blockinfo;
}

?>