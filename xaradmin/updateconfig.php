<?php
/**
 * Update configuration settings
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
 * This is a standard function to update the configuration parameters of the
 * module given the information passed back by the modification form
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
function sitecontact_admin_updateconfig()
{
    // Confirm authorisation code
    if (!xarSecConfirmAuthKey()) return;

    if (!xarVarFetch('shorturls', 'checkbox', $shorturls, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('scactive', 'checkbox', $scactive, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('useantibot', 'checkbox', $useantibot,  FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('customtext', 'str:1:', $customtext, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('customtitle', 'str:1:', $customtitle, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('optiontext', 'str:1:', $optiontext, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('webconfirmtext', 'str:1:', $webconfirmtext, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('notetouser', 'str:1:', $notetouser, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('allowcopy', 'checkbox', $allowcopy, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('usehtmlemail', 'checkbox', $usehtmlemail, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('scdefaultemail', 'str:1:', $scdefaultemail,'', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('scdefaultname', 'str:1:', $scdefaultname, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('aliasname', 'str:1:', $aliasname, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('modulealias','checkbox', $modulealias,FALSE,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('defaultform','int:1:', $defaultform, 1,XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('itemsperpage','int:1:', $itemsperpage, 10, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('savedata', 'checkbox', $savedata, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('permissioncheck', 'checkbox', $permissioncheck, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('termslink', 'str:1:', $termslink, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('allowcc', 'checkbox', $allowcc, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('allowbcc', 'checkbox', $allowbcc, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('allowanoncopy', 'checkbox', $allowanoncopy, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('fieldconfig', 'array', $fieldconfig, array(), XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('adminccs', 'checkbox', $adminccs, FALSE, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('admincclist', 'str:0:', $admincclist, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('checkbanned', 'checkbox', $checkbanned, FALSE, XARVAR_NOT_REQUIRED)) return;
    $allowanoncopy = ($allowcopy && $allowanoncopy)? TRUE :FALSE; //only allow anonymous if allow copy for registered too
    $fieldconfig = implode(',',$fieldconfig);
    $soptions=array('allowcc'=>$allowcc,
                    'allowbcc'=>$allowbcc,
                    'allowanoncopy'=>$allowanoncopy,
                    'adminccs'=>$adminccs,
                    'admincclist' => $admincclist,
                    'fieldconfig'   =>$fieldconfig);

    $soptions=serialize($soptions);

    xarModSetVar('sitecontact', 'customtext', $customtext);
    xarModSetVar('sitecontact', 'customtitle', $customtitle);
    xarModSetVar('sitecontact', 'optiontext', $optiontext);
    xarModSetVar('sitecontact', 'SupportShortURLs', $shorturls);
    xarModSetVar('sitecontact', 'scactive', $scactive);
    xarModSetVar('sitecontact', 'allowcopy', $allowcopy);
    xarModSetVar('sitecontact', 'usehtmlemail', $usehtmlemail);
    xarModSetVar('sitecontact', 'webconfirmtext', $webconfirmtext);
    xarModSetVar('sitecontact', 'notetouser', $notetouser);
    xarModSetVar('sitecontact', 'scdefaultemail', $scdefaultemail);
    xarModSetVar('sitecontact', 'scdefaultname', $scdefaultname);
    xarModSetVar('sitecontact', 'defaultform', $defaultform);
    xarModSetVar('sitecontact', 'itemsperpage', $itemsperpage);
    xarModSetVar('sitecontact', 'soptions', $soptions);
    xarModSetVar('sitecontact', 'savedata', $savedata);
    xarModSetVar('sitecontact', 'permissioncheck', $permissioncheck);
    xarModSetVar('sitecontact', 'termslink', trim($termslink));
    xarModSetVar('sitecontact', 'useantibot', $useantibot);
    xarModSetVar('sitecontact', 'checkbanned', $checkbanned);
    if (isset($aliasname) && trim($aliasname)<>'') {
        xarModSetVar('sitecontact', 'useModuleAlias', $modulealias);
    } else{
         xarModSetVar('sitecontact', 'useModuleAlias', 0);
    }
    $scdefaultemail=trim($scdefaultemail);
    if ((!isset($scdefaultemail)) || $scdefaultemail=='') {
       $scdefaultemail=xarModGetVar('mail','adminmail');
    }
    xarModSetVar('sitecontact', 'scdefaultemail', $scdefaultemail);

    $scdefaultname=trim($scdefaultname);

    if (!isset($scdefaultname) || $scdefaultname=='') {
       $scdefaultname=xarModGetVar('mail','adminname');
    }

    xarModSetVar('sitecontact', 'scdefaultname', $scdefaultname);

    $currentalias = xarModGetVar('sitecontact','aliasname');
    $newalias = trim($aliasname);
          /* Get rid of the spaces if any, it's easier here and use that as the alias*/
    if ( strpos($newalias,'_') === FALSE )
    {
        $newalias = str_replace(' ','_',$newalias);
    }
    $hasalias= xarModGetAlias($currentalias);
    $useAliasName= xarModGetVar('sitecontact','useModuleAlias');

    if ($useAliasName && !empty($newalias)) {
         if ($aliasname != $currentalias)
         /* First check for old alias and delete it */
            if (isset($hasalias) && ($hasalias =='sitecontact')){
                xarModDelAlias($currentalias,'sitecontact');
            }
            /* now set the new alias if it's a new one */
            $newalias = xarModSetAlias($newalias,'sitecontact');
            if (!$newalias) { //name already taken so unset
                 xarModSetVar('sitecontact', 'aliasname', '');
                 xarModSetVar('sitecontact', 'useModuleAlias', false);
            } else { //it's ok to set the new alias name
                xarModSetVar('sitecontact', 'aliasname', $aliasname);
                xarModSetVar('sitecontact', 'useModuleAlias', $modulealias);
            }
    } else {
       //remove any existing alias and set the vars to none and false
            if (isset($hasalias) && ($hasalias =='sitecontact')){
                xarModDelAlias($currentalias,'sitecontact');
            }
            xarModSetVar('sitecontact', 'aliasname', '');
            xarModSetVar('sitecontact', 'useModuleAlias', false);
    }


    xarModCallHooks('module','updateconfig','sitecontact',
              array('module' => 'sitecontact'));
    $msg = xarML('Sitecontact configuration was successfully updated.');
    xarTplSetMessage($msg,'status');

   xarResponseRedirect(xarModURL('sitecontact', 'admin', 'modifyconfig'));

    /* Return true */
    return true;
}

?>