<?php
/**
 * Validate code
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/*
 * Author jojodee
 * Generic validation routine using an scid, scrid and valcode
 * If the data matches the specific  scrid data, the passed in validation field is updated
 * TODO: Make the validation email configuration/optional
*/
function sitecontact_user_valcode()
{
    if (!xarVarFetch('scid',     'id', $scid,    0,     XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('scrid',    'id', $scrid,  0, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('valcode',   'str:1:',   $valcode,   '',    XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('vf',        'str:1:',   $vf,   'validation_code',    XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('uf',        'str:1:',   $uf,   'validated',    XARVAR_NOT_REQUIRED)) return;
    if (empty($scid) || empty($scrid) || empty($valcode)) {
    //handle this error
       return xarTplModule('sitecontact','user','errors',array('errortype' => 'bad_validation'));
    }
    
    $formdata = xarModAPIFunc('sitecontact','user','getcontacttypes',array('scid' => $scid));
    $formdata=current($formdata);
    $sctypename = $formdata['sctypename']; //used for custom templates
    $response = xarModAPIFunc('sitecontact','user','get',array('scrid'=>$scrid));
    
    if (!is_array($response)) $result = FALSE;
    
    $item = xarModAPIFunc('dynamicdata','user','getitem',
             array('moduleid' => xarModGetIdFromName('sitecontact'),
                  'itemtype' => $scid,
                  'itemid'    =>$scrid));
                  
    if (!isset($item)) $result = FALSE;
    $extra = '';
    if ($item && $response) {

        if ($item[$vf] ==$valcode) {
            if ($item[$uf] != 1) {
                $object = xarModAPIFunc('dynamicdata','user','getobject',
                     array('moduleid' => xarModGetIdFromName('sitecontact'),
                          'itemtype' => $scid,
                          'itemid'=>$scrid));
                          
                if (!isset($object)) return;  /* throw back */
                $dditem = $object->getItem();
                $properties = $object->getProperties();
                $object->properties[$uf]->setValue(TRUE);
                $object->updateItem();
                $result  = TRUE;
            } else {
                $result = FALSE;
                $extra = xarML('This validation code has already been used or expired, please contact the Site Administrator');
            }
        } else {
            $result = FALSE;
        }
    }
    if ($result) {
        //notify admin of validation
        $adminname  = xarModGetVar('mail', 'adminname');
        $adminemail = xarModGetVar('mail', 'adminmail');
        $viewlink = xarModURL('sitecontact','admin','display',array('scrid'=>$scrid),false);
        $editlink = xarModURL('sitecontact','admin','modify',array('scrid'=>$scrid),false);
        
        $message    = xarML("A user has validated their #(1) form submission.  Their details:",$sctypename)." \n\n";
        $message   .= xarML('User name').":  ".$response['username']."\n";
        $message   .= xarML('Email Address').":  ".$response['useremail']."\n\n";
        $message   .= xarML('View submission').":  $viewlink\n";        
        $message   .= xarML('Edit submission').":  $editlink\n"; 
        $messagetitle = xarML("A user has validated their #(1) form submission",$sctypename);

        if (!xarModAPIFunc('mail', 'admin', 'sendmail',
                               array('info'    => $adminemail,
                                     'name'    => $adminname,
                                     'subject' => $messagetitle,
                                     'message' => $message))) return;
    }
    $data['extra'] = $extra;   
    $data['result'] = $result;
    $templatedata = xarTplModule('sitecontact', 'user', 'valcode', $data, $sctypename);
 return $templatedata;
 
}
?>