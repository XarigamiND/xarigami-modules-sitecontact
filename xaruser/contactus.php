<?php
/**
 * Contact us main function
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
 * @ Function: contactus
 * @ Deprecated at version 1.1.0  - use xaruser/respond.php that passes vars to xaruserapi/respond.php
 * @ Author Jo Dalle Nogare <icedlava@2skies.com>
 * @ Param username, useremail, requesttext,company, usermessage,useripaddress,userreferer,altmail
  */
function sitecontact_user_contactus($args)
{
  extract($args);

      $defaultformid=(int)xarModGetVar('sitecontact','defaultform');
    /* Get parameters */
    if (!xarVarFetch('username', 'str:1:', $username, '', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('useremail', 'str:1:', $useremail, '', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('requesttext', 'str:1:', $requesttext, '', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('company', 'str:1:', $company, '', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('usermessage', 'str:1:', $usermessage, '', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
    if (!xarVarFetch('useripaddress', 'str:1:', $dummy, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('userreferer', 'str:1:', $userreferer, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('sendcopy', 'checkbox', $sendcopy, true, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('sctypename', 'str:0:', $sctypename, NULL, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('scform',     'str:0:', $scform, NULL, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('scid',       'int:1:', $scid,       $defaultformid, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('bccrecipients', 'str:1', $bccrecipients, '')) return;
    if (!xarVarFetch('ccrecipients', 'str:1', $ccrecipients, '')) return;
    if (!xarVarFetch('return_url',  'isset', $return_url, NULL, XARVAR_DONT_SET)) {return;}
    if (!xarVarFetch('savedata',     'checkbox', $savedata, 0, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('permissioncheck', 'checkbox', $permissioncheck, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('permission',   'checkbox', $permission, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('termslink',    'str:1:',   $termslink, '', XARVAR_NOT_REQUIRED)) return;
    //formcapcha variables
    if (!xarVarFetch('antiselect', 'int:0:', $antiselect, NULL, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('antiword', 'str:1', $antiword, '', XARVAR_NOT_REQUIRED)) {return;}
    /* Confirm authorisation code. */
    if (!xarSecConfirmAuthKey()) return;

    $formdata=array();
    if (isset($sctypename) && trim($sctypename) !=''){
        $sctypename=trim($sctypename);
    }
    if (isset($scform) && (trim($scform) !='')) { //provide alternate entry name
      $sctypename=trim($scform);
    }
   //Have we got a form that is available and active?
    if (isset($sctypename) && trim($sctypename) !='') {
       $formdata = xarModAPIFunc('sitecontact','user','getcontacttypes',array('sctypename'=> $sctypename));
    }elseif (isset($scid) && is_int($scid)) {
        $formdata = xarModAPIFunc('sitecontact','user','getcontacttypes',array('scid' => $scid));
    } else {
        $formdata = xarModAPIFunc('sitecontact','user','getcontacttypes',array('scid' => $defaultformid));
    }

    //Have we got an active form
    if (!is_array($formdata)) { //exists but not active
        //fallback to default form again
        $formdata = xarModAPIFunc('sitecontact','user','getcontacttypes',array('scid' => $defaultformid));
    }
    $formdata=current($formdata);

    if ($formdata['scactive'] !=1) { //form but not active
        return xarTplModule('sitecontact','user','errors',array('errortype' => 'form_unavailable'));
    }

    $data['submit'] = xarML('Submit');

    //now check for the options, and including antibot and - bbccrecipient and ccrecipient switch Bug 5799
     if (isset($formdata['soptions'])) {
            $soptions=unserialize($formdata['soptions']);
            if (is_array($soptions)) {
                foreach ($soptions as $k=>$v) {
                    $soptions[$k]=$v;
              }
           }
    }
    $useantibot=$soptions['useantibot'];

  //this is to be moved to a hook
  //the original code logic copied in here, but needs a bit of a change later
  $badcaptcha = 0;
  $args['botreset']=false;
  if (!xarUserIsLoggedIn()) { //we want to use this else don't bother
     if (xarModIsAvailable('formcaptcha') && xarModGetVar('formcaptcha','usecaptcha') == true && $useantibot) {
        include_once 'modules/formcaptcha/xaruser/anticonfig.php'; // get rid of this - move to modvars
        $cas_antiselect = intval($antiselect);
        $cas_antiword = $antiword;
        $cas_textcount = count($cas_text);
        // Copy the first element to a new last element
        $cas_text[] = $cas_text[0];
        // Set the first element to invalid
        $cas_text[0] = "* * * INVALID * * *";
        //Determine the correct word
        $cas_antispam = $cas_text[$cas_antiselect];
        if ($cas_antispam != $cas_antiword) {
            $badcaptcha =1;
            $casmsg = xarModGetVar('formcaptcha','antierror');
        } else {
            $badcaptcha = 0;
            $casmsg ='';
        }

        if ($badcaptcha ==1) {
            $args['company'] = $company;
                $args['scid']   = $scid;
                $args['scform'] = $scform;
                $args['usermessage'] = $usermessage;
                $args['sctypename'] = $sctypename;
                $args['requesttext'] = $requesttext;
                $args['antibotinvalid'] = TRUE;
                $args['botreset']=true;
                $args['userreferer']= $userreferer; //don't loose our original referer
                $args['casmsg']=$casmsg;
                return xarModFunc('sitecontact', 'user', 'main', $args);
        }
        $args['botreset']=false; // switch used for referer mainly in main function
      }


        if (xarModIsAvailable('formantibot') && $useantibot) {
            if (!xarVarFetch('antibotcode',  'str:6:10', $antibotcode, '', XARVAR_NOT_REQUIRED) ||
            !xarModAPIFunc('formantibot', 'user', 'validate', array('userInput' => $antibotcode))) {
                $args['company'] = $company;
                $args['scid']   = $scid;
                $args['scform'] = $scform;
                $args['usermessage'] = $usermessage;
                $args['sctypename'] = $sctypename;
                $args['requesttext'] = $requesttext;
                $args['antibotinvalid'] = TRUE;
                $args['botreset']=true;
                $args['userreferer']= $userreferer; //don't loose our original referer
                return xarModFunc('sitecontact', 'user', 'main', $args);
            }
        } else {
            $args['botreset']=false; // switch used for referer mainly in main function
        }
    }

    if (!isset($soptions['allowbccs']) || $soptions['allowbccs']!=1) {
        $bccrecipients='';
    }
    if (!isset($soptions['allowccs']) || $soptions['allowccs']!=1) {
        $ccrecipients='';
    }
    //end check for bug 5799
    if (!isset($soptions['allowanoncopy']) || $soptions['allowanoncopy']!=1) {
        $allowanoncopy=false;
    } else {
        $allowanoncopy=true;
    }
    //Feature request for more accurate IP
    //leave the ip capture in the forms - hehehe :)
    $useripaddress=xarModAPIFunc('sitecontact','admin','getcurrentip');

    //Put all set data in an array for later processing
     $item=array('scid'           => array(xarML('Form ID'),(int)$scid),
                'sctypename'      => array(xarML('Form'),$sctypename),
                'scform'          => array(xarML('Form Name'),$scform),
                'username'        => array(xarML('Name'),$username),
                'useremail'       => array(xarML('Email'),$useremail),
                'requesttext'     => array(xarML('Subject'),$requesttext),
                'company'         => array(xarML('Organization'),$company),
                'usermessage'     => array(xarML('Message'),$usermessage),
                'useripaddress'   => array(xarML('IP'),$useripaddress),
                'userreferer'     => array(xarML('Referrer'),$userreferer),
                'sendcopy'        => array(xarML('Copy?'),$sendcopy),
                'savedata'        => array(xarML('Allow Save?'),$savedata),
                'permissioncheck' => array(xarML('Check permission?'),$permissioncheck),
                'permission'      => array(xarML('Agree to save?'),$permission),
                'termslink'       => array(xarML('Terms provided'),$termslink),
                'bccrecipients'   => array(xarML('BCC'),$bccrecipients),
                'ccrecipients'    => array(xarML('CC'),$ccrecipients)
                );

    /* process CC Recipient list */
    $ccrecipientarray=array();
    $ccrec=array();
    $cctemp=array();
    if (isset($ccrecipients) && !empty($ccrecipients)) {
        $ccrecipientarray=explode(';',$ccrecipients);
        if (is_array($ccrecipientarray)) {
            foreach ($ccrecipientarray as $recipientkey=>$v) {
                $cctemp[]=explode(',',$v);
            }
            foreach ($cctemp as $recipient=>$values) {
                $ccrec[$values[0]]=isset($values[1])?$values[1]:'';
            }
       }
    }
    $ccrecipients=$ccrec;

    /* process BCC Recipient list */
    $bccrecipientarray=array();
    $bccrec=array();
    $bcctemp=array();
    if (isset($bccrecipients) && !empty($bccrecipients)) {
        $bccrecipientarray=explode(';',$bccrecipients);
        if (is_array($bccrecipientarray)) {
            foreach ($bccrecipientarray as $recipientkey=>$v) {
                $bcctemp[]=explode(',',$v);
            }
            foreach ($bcctemp as $recipient=>$values) {
                $bccrec[$values[0]]=isset($values[1])?$values[1]:'';
            }
        }
    }
    $bccrecipients=$bccrec;

    $data['scid']=$formdata['scid'];
    $data['sctypename']=$formdata['sctypename'];
    $withupload = isset($withupload)? $withupload :(int) false;
    $dditems=array();
    $propdata=array();
    if (xarModIsHooked('dynamicdata','sitecontact',$data['scid'])) {
        /* get the Dynamic Object defined for this module (and itemtype, if relevant) */
        $object = xarModAPIFunc('dynamicdata','user','getobject',
                array('module' => 'sitecontact',
                      'itemtype' => $data['scid']));

        if (!isset($object)) return;  /* throw back */
        $objectid=$object->objectid;

        /*we just want a copy of data - don't need to save it in a table yet */
        if (isset($object) && !empty($object->objectid)) {
            /* check the input values for this object and do ....what here? */
            $isvalid = $object->checkInput();
            $dditems = $object->properties;
        }

        if (is_array($dditems)) {
            foreach ($dditems as $itemid => $fields) {

                if (isset($fields->upload) && $fields->upload == true) {
                    $withupload = (int) true;
                    $fileuploadfieldname=$itemid;
                }
                $items[$itemid] = array();
                foreach ($fields as $name => $value) {
                    $items[$itemid][$name] = ($value);
                }

                $propdata=array();
                foreach ($items as $key => $value) {
                    $propdata[$value['name']]['label']=$value['label'];
                    $propdata[$value['name']]['value']=$value['value'];
                }
            }
        }
    }

    if ($withupload && isset($fileuploadfieldname) && is_array($items[$fileuploadfieldname]) && !empty($items[$fileuploadfieldname]['value'])) {
        $filebasepath=$items[$fileuploadfieldname]['basePath'];
        $filebasedir=$items[$fileuploadfieldname]['basedir'];
        $fileattachmentname=$items[$fileuploadfieldname]['value'];
        $attachpath=$filebasepath.'/'.$filebasedir.'/'.$fileattachmentname;
        $attachname=$fileattachmentname;
    } else {
        $attachpath='';
        $attachname='';
    }


    /* Do we want to save the data for this form? */
    if ($savedata) {
        // save the form - let it handle save of the hooked dd
        // First check to see if we needed user permission or not, and if we do the user has agreed
        if (($permissioncheck && $permission) || !$permissioncheck) {
            //ok to save
            $args = array('scid'            => (int)$scid,
                          'scform'          => $scform,
                          'username'        => $username,
                          'useremail'       => $useremail,
                          'requesttext'     => $requesttext,
                          'company'         => $company,
                          'usermessage'     => $usermessage,
                          'useripaddress'   => $useripaddress,
                          'userreferer'     => $userreferer,
                          'sendcopy'        => $sendcopy,
                          'savedata'        => $savedata,
                          'permissioncheck' => $permissioncheck,
                          'permission'      => $permission,
                          'bccrecipients'   => serialize($bccrecipients),
                          'ccrecipients'    => serialize($ccrecipients)
                    );
        } elseif ($permissioncheck && !$permission) {
            //what to do - better save a 'blank' spot as missing data?
            //let's do that for now
            $args = array('scid'            => (int)$scid,
                          'scform'          => '',
                          'username'        => xarML('Missing Value'),
                          'useremail'       => '',
                          'requesttext'     => '',
                          'company'         => '',
                          'usermessage'     => '',
                          'useripaddress'   => '',
                          'userreferer'     => '',
                          'sendcopy'        => 0,
                          'savedata'        => $savedata,
                          'permissioncheck' => $permissioncheck,
                          'permission'      => $permission,
                          'bccrecipients'   => '',
                          'ccrecipients'    => ''
                        );
        }

        $newscrid = xarModAPIFunc('sitecontact','admin','create',$args);
        if (!$newscrid) {
            //no, don't do anything ... if there is a prob we don't want to disrupt the user feedback
            //on their emailing
            //TODO: workout something for this and any other errors related to create reponse portion of process
        }
    }

    /* Security Check - caused some problems here with anon browsing and cachemanager
     * should be ok now - review
     * if(!xarSecurityCheck('ReadSiteContact')) return;
     */

    $notetouser = $formdata['notetouser'];
    if (!isset($notetouser)){
        $notetouser = xarModGetVar('sitecontact','defaultnote');
    }
    $usehtmlemail= $formdata['usehtmlemail'];
    $allowcopy = $formdata['allowcopy'];
    $optiontext = $formdata['optiontext'];
    $optionset = array();
    $selectitem=array();
    $adminemail = xarModGetVar('mail','adminmail');
    $mainemail=$formdata['scdefaultemail'];

    $optionset=explode(',',$optiontext);
    $data['optionset']=$optionset;
    $optionitems=array();
    foreach ($optionset as $optionitem) {
      $optionitems[]=explode(';',$optionitem);
    }
    foreach ($optionitems as $optionid) {
        if (trim($optionid[0])==trim($requesttext)) {
            if (isset($optionid[1])) {
                $setmail=$optionid[1];
            }else{
                $setmail=$mainemail;
            }
        }
    }
    if (!isset($setmail) ) {
        $setmail = $formdata['scdefaultemail'];;
    }
    $data['setmail']=$setmail;
    $today = getdate();
    $month = $today['month'];
    $mday  = $today['mday'];
    $year  = $today['year'];
    $todaydate = $mday.' '.$month.', '.$year;

    $notetouser = preg_replace('/%%username%%/',
                            $username,
                            $notetouser);
    $notetouser = preg_replace('/%%useremail%%/',
                            $useremail,
                            $notetouser);
    $notetouser = preg_replace('/%%requesttext%%/',
                            $requesttext,
                            $notetouser);
    $notetouser = preg_replace('/%%company%%/',
                            $company,
                            $notetouser);

    $sendname=$formdata['scdefaultname'];;
    if (!isset($sendname)) {
        $adminname= xarModGetVar('mail','adminname');
        $sendname=$adminname;
    }
    $sitename = xarModGetVar('themes','SiteName');
    $siteurl = xarServerGetBaseURL();
    $subject = $requesttext;

    /* comments in emails is a problem - set it manually for this module
       let's make it contingent on the mail module var - as that is what
       seems intuitively the correct thing
    */

    $themecomments = xarModGetVar('themes','ShowTemplates');
    $mailcomments = xarModGetVar('mail','ShowTemplates');
    if ($mailcomments == 1) {
        xarModSetVar('themes','ShowTemplates',1);
    } else {
        xarModSetVar('themes','ShowTemplates',0);
    }

    /* Prepare the html text message to user */

    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    $htmlsubject = strtr(xarVarPrepHTMLDisplay($requesttext), $trans);
    $htmlcompany = strtr(xarVarPrepHTMLDisplay($company), $trans);
    $htmlusermessage  = strtr(xarVarPrepHTMLDisplay($usermessage), $trans);
    $htmlnotetouser  = strtr(xarVarPrepHTMLDisplay($notetouser), $trans);

        if (!empty($data['sctypename'])){
             $htmltemplate = 'html_' . $data['sctypename'];
             $texttemplate = 'text_' . $data['sctypename'];
        } else {
             $htmltemplate =  'html';
             $texttemplate =  'text';
        }
        $userhtmlarray= array('notetouser' => $htmlnotetouser,
                              'username'   => $username,
                              'useremail'  => $useremail,
                              'company'    => $htmlcompany,
                              'requesttext'=> $htmlsubject,
                              'usermessage'=> $htmlusermessage,
                              'sitename'   => $sitename,
                              'siteurl'    => $siteurl,
                              'propdata'   => $propdata,
                              'dditems'    => $dditems,
                              'todaydate'  => $todaydate);

        try {
            $userhtmlmessage= xarTplModule('sitecontact','user','usermail',$userhtmlarray,$htmltemplate);
        } catch (Exception $e) {
            $userhtmlmessage= xarTplModule('sitecontact', 'user', 'usermail',$userhtmlarray,'html');
        }
        /* prepare the text message to user */
        $textsubject = strtr($requesttext,$trans);
        $textcompany = strtr($company,$trans);
        $textusermessage = strtr($usermessage,$trans);
        $textnotetouser = strtr($notetouser,$trans);

        $usertextarray =array('notetouser' => $textnotetouser,
                              'username'   => $username,
                              'useremail'  => $useremail,
                              'company'    => $textcompany,
                              'requesttext'=> $textsubject,
                              'usermessage'=> $textusermessage,
                              'sitename'   => $sitename,
                              'siteurl'    => $siteurl,
                              'propdata'   => $propdata,
                              'dditems'    => $dditems,
                              'todaydate'  => $todaydate);

        try {
            $usertextmessage= xarTplModule('sitecontact','user','usermail', $usertextarray,$texttemplate);
        } catch (Exception $e) {
            $usertextmessage= xarTplModule('sitecontact', 'user', 'usermail',$usertextarray,'text');
        }
    if (($allowcopy ) and ($sendcopy)) { //the user wants to copy to self and it is allowed by admin
        /* check the logged in user's email address  and if anon is allowed*/
        $docopy = false;
        if (xarUserIsLoggedIn()) {
            $userofficialemail = trim(strtolower(xarUserGetVar('email')));
            $comparemail = trim(strtolower($useremail));
            if ($userofficialemail == $comparemail) {
                $docopy = true;
            }

        } elseif ($allowanoncopy) {
            $docopy = true;
        } else {
            $docopy = false;
        }
        if ($docopy) { //either they are anon and allowed, or logged in and their email is correct
            /* let's send a copy of the feedback form to the sender
                              * if it is permitted by admin, and the user wants it */
            $args = array('info'         => $useremail,
                          'name'         => $username,
                          'subject'      => $subject,
                          'message'      => $usertextmessage,
                          'htmlmessage'  => $userhtmlmessage,
                          'from'         => $setmail,
                          'fromname'     => $sendname,
                          'attachName'   => $attachname,
                          'attachPath'   => $attachpath,
                          'usetemplates' => false);

            /* send mail to user , if html email let's do that  else just send text*/
            if ($usehtmlemail != 1) {
                if (!xarModAPIFunc('mail','admin','sendmail', $args)) return;
            } else {/*it's html email */
                if (!xarModAPIFunc('mail','admin','sendhtmlmail', $args)) return;
            }
        } //end do copy
    } //end user copy to self check

    /* now let's do the html message to admin */

    $adminhtmlarray=array('notetouser'  => $htmlnotetouser,
                          'username'    => $username,
                          'useremail'   => $useremail,
                          'company'     => $htmlcompany,
                          'requesttext' => $htmlsubject,
                          'usermessage' => $htmlusermessage,
                          'sitename'    => $sitename,
                          'siteurl'     => $siteurl,
                          'todaydate'   => $todaydate,
                          'useripaddress' => $useripaddress,
                          'propdata'    => $propdata,
                          'dditems'     => $dditems,
                          'userreferer' => $userreferer);

    try {
        $adminhtmlmessage= xarTplModule('sitecontact','user','adminmail',$adminhtmlarray,$htmltemplate);
    } catch (Exception $e) {
        $adminhtmlmessage= xarTplModule('sitecontact', 'user', 'adminmail',$adminhtmlarray,'html');
    }
    $admintextarray =  array('notetouser'  => $textnotetouser,
                             'username'    => $username,
                             'useremail'   => $useremail,
                             'company'     => $textcompany,
                             'requesttext' => $textsubject,
                             'usermessage' => $textusermessage,
                             'sitename'    => $sitename,
                             'siteurl'     => $siteurl,
                             'todaydate'   => $todaydate,
                             'useripaddress' => $useripaddress,
                             'propdata'    => $propdata,
                             'dditems'     => $dditems,
                             'userreferer' => $userreferer);

    /* Let's do admin text message */
    try {
        $admintextmessage= xarTplModule('sitecontact','user','adminmail',$admintextarray,$texttemplate);
    } catch (Exception $e) {
        $admintextmessage= xarTplModule('sitecontact', 'user', 'adminmail',$admintextarray,'text');
    }

    /* send email to admin */
    $args = array('info'         => $setmail,
                  'name'         => $sendname,
                  'ccrecipients' => $ccrecipients,
                  'bccrecipients' => $bccrecipients,
                  'subject'      => $subject,
                  'message'      => $admintextmessage,
                  'htmlmessage'  => $adminhtmlmessage,
                  'from'         => $useremail,
                  'fromname'     => $username,
                  'attachName'   => $attachname,
                  'attachPath'   => $attachpath,
                  'usetemplates' => false);
    if ($usehtmlemail != 1) {
        if (!xarModAPIFunc('mail','admin','sendmail', $args))return;
    } else {
        if (!xarModAPIFunc('mail','admin','sendhtmlmail', $args))return;
    }
    if (isset($attachpath) && !empty($attachpath)){
        if (file_exists($attachpath)) {
            unlink("{$attachpath}");
        }
    }
    /* Set the theme comments back */
    xarModSetVar('themes','ShowTemplates',$themecomments);
    /* lets update status and display updated configuration */
    xarSessionSetVar('sitecontact.sent',1);

    if (isset($return_url)) {
        xarResponseRedirect($return_url);
    } else {
        xarResponseRedirect(xarModURL('sitecontact', 'user', 'main', array('message' => '1', 'scid'=>$data['scid'])));
    }
    /* Return */
    return true;
}
?>