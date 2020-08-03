<?php
/**
 * Update a response
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * update item
 *
 * @param id     ptid       The publication Type ID for this new article
 * @param array  new_cids   An array with the category ids for this new article (OPTIONAL)
 * @param string preview    Are we gonna see a preview? (OPTIONAL)
 * @param string save       Call the save action (OPTIONAL)
 * @param string return_url The URL to return to (OPTIONAL)
 * @return  bool true on success, or mixed on failure
 */
function sitecontact_admin_update()
{
    // Get parameters

    if (!xarVarFetch('scrid', 'id', $scrid)) return;
    if (!xarVarFetch('objectid', 'id', $objectid, $objectid, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('username',      'str:1:',   $username,    '',    XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('useremail',     'str:1:',    $useremail,  '',    XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('requesttext',   'str:1:',   $requesttext, '',    XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('company',       'str:1:',   $company,     '',    XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('usermessage',   'str:1:',   $usermessage, '',    XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('scform',        'str:0:',   $scform,      NULL,  XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('scid',          'int:1:',   $scid,       '', XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('confirm',  'str:1:', $confirm, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('return_url',  'str:1',    $return_url, NULL, XARVAR_NOT_REQUIRED)) {return;}

    if (!empty($objectid)) {
        $scrid = $objectid;
    }

    // Confirm authorisation code
    if (!xarSecConfirmAuthKey()) {
    return; //TODO: do something useful here
    }

    if (empty($scrid) || !is_numeric($scrid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                     'item id', 'admin', 'update', 'Sitecontact');
        throw new BadParameterException('null',$msg);
    }

    $item = xarModAPIFunc('sitecontact','user','get',array('scrid' => $scrid));
    if (!isset($item) || !is_array($item)) {
        $msg = xarML('Item with scrid  response id #(1) not found',$scrid);
            return xarResponseNotFound($msg);
    }
    $scid=$item['scid'];
    $item['itemtype'] = $scid;

    if (!xarSecurityCheck('EditSiteContact',0,'ContactForm',"$scid")) {
        return; // todo: something
    }

    $thisform = xarModAPIFunc('sitecontact','user','getcontacttypes',array('scid'=>$scid));
    $thisform=current($thisform);

    $args['username'] = $username;
    $args['useremail'] = $useremail;
    $args['requesttext'] = $requesttext;
    $args['company'] = $company;
    $args['useremail'] = $useremail;
    $args['usermessage'] = $usermessage;

    $args['scrid'] = $scrid;
    $args['scid'] = $scid;

   $updated = xarModAPIFunc('sitecontact','admin','update',$args);
    if (!$updated) {
        $msg = xarML('There was a problem when updating the form, and the Response form was not updated.');
        xarTplSetMessage($msg,'error');
    } else {
        $msg = xarML('Response form successfully updated.');
        xarTplSetMessage($msg,'status');
    }
    $return_url = !empty($return_url) ? $return_url : xarModURL('sitecontact','admin','view',array('scid'=>$scid,'scrid'=>$scrid));
    xarResponseRedirect($return_url);
    return;
}

?>
