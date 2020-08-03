<?php
/**
 * Initialize the SiteContact Module and carry out upgrade or delete
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2012 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/**
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
function sitecontact_init()
{
    xarDBLoadTableMaintenanceAPI();
    /* Setup our table for holding the different contact itemtype forms */
    $dbconn = xarDB::getConn();
    $xarTables = xarDB::getTables();

    $sitecontactTable = $xarTables['sitecontact'];

    /* Get a data dictionary object with all the item create methods in it */
    /* $datadict = xarDBNewDataDict($dbconn, 'ALTERTABLE');

    $fields= "xar_scid            I      AUTO       PRIMARY,
              xar_sctypename      C(100) NotNull    DEFAULT '',
              xar_sctypedesc      C(254) NotNull    DEFAULT '',
              xar_customtext      X      NotNull    DEFAULT '',
              xar_customtitle     C(150) NotNull    DEFAULT '',
              xar_optiontext      X      NotNull    DEFAULT '',
              xar_webconfirmtext  X      NotNull    DEFAULT '',
              xar_notetouser      X      NotNull    DEFAULT '',
              xar_allowcopy       I1     NotNull    DEFAULT 0,
              xar_usehtmlemail    I1     NotNull    DEFAULT 0,
              xar_scdefaultemail  C(254) NotNull    DEFAULT '',
              xar_scdefaultname   C(254) NotNull    DEFAULT '',
              xar_scactive        I1     NotNull    DEFAULT 1,
              xar_savedata        I1     NotNull    DEFAULT 0,
              xar_permissioncheck I1     NotNull    DEFAULT 0,
              xar_termslink       C(254) NotNull    DEFAULT '',
              xar_soptions        X      NotNull    DEFAULT ''
              ";
            $result = $datadict->changeTable($sitecontactTable, $fields);
           if (!$result) {return;}
    */

    $fields = array(
        'xar_scid'          => array('type'=>'integer','null'=>FALSE,'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_sctypename'    => array('type'=>'varchar','size'=>100,'null'=>FALSE,'default'=>''),
        'xar_sctypedesc'    => array('type'=>'varchar','size'=>254,'null'=>FALSE,'default'=>''),
        'xar_customtext'    => array('type'=>'text','size'=>'medium'),
        'xar_customtitle'   => array('type'=>'varchar','size'=>150,'null'=>FALSE,'default'=>''),
        'xar_optiontext'    => array('type'=>'text','size'=>'small'),
        'xar_webconfirmtext'=> array('type'=>'text','size'=>'small'),
        'xar_notetouser'    =>  array('type'=>'text','size'=>'small'),
        'xar_allowcopy'     => array('type'=>'integer','size'=>'small','null'=>FALSE,'default'=>'0'),
        'xar_usehtmlemail'  => array('type'=>'integer','size'=>'small','null'=>FALSE,'default'=>'0'),
        'xar_scdefaultemail'=> array('type'=>'varchar','size'=>254,'null'=>FALSE,'default'=>''),
        'xar_scdefaultname' => array('type'=>'varchar','size'=>254,'null'=>FALSE,'default'=>''),
        'xar_scactive'      => array('type'=>'integer','size'=>'small','null'=>FALSE,'default'=>'1'),
        'xar_savedata'      =>array('type'=>'integer','size'=>'small','null'=>FALSE,'default'=>'0'),
        'xar_permissioncheck' =>array('type'=>'integer','size'=>'small','null'=>FALSE,'default'=>'0'),
        'xar_termslink'     =>array('type'=>'varchar','size'=>254,'null'=>FALSE,'default'=>''),
        'xar_soptions'=>array('type'=>'text','size'=>'medium')
    );

    $query = xarDBCreateTable($sitecontactTable,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table and send exception if unsuccessful
    $result = $dbconn->Execute($query);
    if (!$result) return;


    /* Create a default form */
    $defaultemail=  xarModGetVar('mail', 'adminmail');
    $sitecontactTable = $xarTables['sitecontact'];
    $query = "INSERT INTO $sitecontactTable
                  (xar_scid,
                   xar_sctypename,
                   xar_sctypedesc,
                   xar_customtext,
                   xar_customtitle,
                   xar_optiontext,
                   xar_webconfirmtext,
                   xar_notetouser,
                   xar_allowcopy,
                   xar_usehtmlemail,
                   xar_scdefaultemail,
                   xar_scdefaultname,
                   xar_scactive,
                   xar_savedata,
                   xar_permissioncheck,
                   xar_termslink,
                   xar_soptions)
                VALUES (1,
                        'basic',
                        'Basic contact form',
                        'Thank you for visiting. We appreciate your feedback.\nPlease let us know how we can assist you.',
                        'Contact and Feedback',
                        'Information request,\nGeneral assistance,\nWebsite issue,\nSpam report,\nComplaint,\nThank you!',
                        'Your message has been sent. Thank you for contacting us.',
                        'Dear %%username%%\n\nThis message confirms your email has been sent.\n\nThank you for your feedback.\n\nAdministrator\n%%sitename%%\n\n',
                        '0',
                        '0',
                        ?,
                        'Site Admin',
                        1,
                        0,
                        0,
                        '',
                        ''
                        )";

    $bindvars = array($defaultemail);
    $result = $dbconn->Execute($query,$bindvars);
           if (!$result) {return;}

    /* Set up a table for holding any saved data, if that option is chosen */
    $sitecontactResponseTable = $xarTables['sitecontact_response'];

    /* Get a data dictionary object with all the item create methods in it */
   /* $datadict = xarDBNewDataDict($dbconn, 'ALTERTABLE');

    $fields= "xar_scrid           I      AUTO       PRIMARY,
              xar_scid            I      NotNull    DEFAULT 0,
              xar_username        C(100) NotNull    DEFAULT '',
              xar_useremail       C(254) NotNull    DEFAULT '',
              xar_requesttext     C(150) NotNull    DEFAULT '',
              xar_company         C(150) NotNull    DEFAULT '',
              xar_usermessage     X      NotNull    DEFAULT '',
              xar_useripaddress   C(24)  NotNull    DEFAULT '',
              xar_userreferer     C(254) NotNull    DEFAULT '',
              xar_sendcopy        I1     NotNull    DEFAULT 0,
              xar_permission      I1     NotNull    DEFAULT 0,
              xar_bccrecipients   C(254) NotNull    DEFAULT '',
              xar_ccrecipients    C(254) NotNull    DEFAULT '',
              xar_responsetime   I(10)  NotNull    DEFAULT 0
              ";
            $result = $datadict->changeTable($sitecontactResponseTable, $fields);
           if (!$result) {return;}
    */
    $fields = array(
        'xar_scrid'         => array('type'=>'integer','null'=>FALSE,'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_scid'          => array('type'=>'integer','null'=>FALSE, 'default'=> '0'),
        'xar_username'      => array('type'=>'varchar','size'=>100,'null'=>FALSE,'default'=>''),
        'xar_useremail'     => array('type'=>'varchar','size'=>254,'null'=>FALSE,'default'=>''),
        'xar_requesttext'   => array('type'=>'varchar','size'=>150,'null'=>FALSE,'default'=>''),
        'xar_company'       => array('type'=>'varchar','size'=>150,'null'=>FALSE,'default'=>''),
        'xar_usermessage'   => array('type'=>'text','size'=>'medium'),
        'xar_useripaddress' => array('type'=>'varchar','size'=>24,'null'=>FALSE,'default'=>''),
        'xar_userreferer'   => array('type'=>'varchar','size'=>254,'null'=>FALSE,'default'=>''),
        'xar_sendcopy'      => array('type'=>'integer','size'=>'small','null'=>FALSE,'default'=>'0'),
        'xar_permission'    =>  array('type'=>'integer','size'=>'small','null'=>FALSE,'default'=>'0'),
        'xar_bccrecipients' =>  array('type'=>'varchar','size'=>254,'null'=>FALSE,'default'=>''),
        'xar_ccrecipients'  =>  array('type'=>'varchar','size'=>254,'null'=>FALSE,'default'=>''),
        'xar_responsetime'  =>array('type'=>'integer','null'=>FALSE,'default'=>'0')
    );

    $query = xarDBCreateTable($sitecontactResponseTable,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table and send exception if unsuccessful
    $result = $dbconn->Execute($query);
    if (!$result) return;

    xarModSetVar('sitecontact', 'savedata', 0);
    xarModSetVar('sitecontact', 'termslink', '');
    xarModSetVar('sitecontact', 'soptions', '');
    xarModSetVar('sitecontact', 'permissioncheck', 0);
    xarModSetVar('sitecontact', 'itemsperpage', 10);
    xarModSetVar('sitecontact', 'defaultform',1);
    xarModSetVar('sitecontact', 'defaultsort','scid');
    xarModSetVar('sitecontact', 'scactive', 1);
    xarModSetVar('sitecontact', 'SupportShortURLs', 0);
    xarModSetVar('sitecontact', 'useModuleAlias',0);
    xarModSetVar('sitecontact', 'aliasname','');
    xarModSetVar('sitecontact', 'usehtmlemail', 0);
    xarModSetVar('sitecontact', 'allowcopy', 0); //bug 5800 set it off by default
    xarModSetVar('sitecontact', 'scdefaultemail',xarModGetVar('mail', 'adminmail'));
    xarModSetVar('sitecontact', 'customtitle','Contact and Feedback');
    xarModSetVar('sitecontact', 'customtext',
    'Thank you for visiting. We appreciate your feedback.
    Please let us know how we can assist you.');

    xarModSetVar('sitecontact', 'optiontext',
    'Information request,
General assistance,
Website issue,
Spam/Abuse report,
Complaint, Thank you!');

    xarModSetVar('sitecontact', 'webconfirmtext',
    'Your message has been sent. Thank you for contacting us.
');
    xarModSetVar('sitecontact', 'defaultnote',
    'Dear %%username%%

This message confirms your email has been sent.

Thank you for your feedback.

Administrator
%%sitename%%
');
  xarModSetVar('sitecontact','notetouser',xarModGetVar('sitecontact','defaultnote'));


     // Enable dynamicdata hooks for sitecontact forms
        xarModAPIFunc('modules','admin','enablehooks',
                       array('callerModName' => 'sitecontact', 'hookModName' => 'dynamicdata'));



/* Define instances for sitecontact forms  */

    $query1 = "SELECT DISTINCT xar_scid FROM  $sitecontactTable";
    $instances = array(
                        array('header' => 'Form ID:',
                                'query' => $query1,
                                'limit' => 20
                            )
                    );
    xarDefineInstance('sitecontact', 'ContactForm', $instances);

    // Register our hooks that we are providing to other modules.  The example
    // module shows an example hook in the form of the user menu.
 /*   if (!xarModRegisterHook('item', 'usermenu', 'GUI',
                            'sitecontact', 'user', 'usermenu')) {
        return false;
    }
 */

    /**
     * Register the module components that are privileges objects
     * Format is
     * xarregisterMask(Name,Realm,Module,Component,Instance,Level,Description)
     */

    xarRegisterMask('ReadSiteContactBlock', 'All', 'sitecontact', 'Block', 'All', 'ACCESS_OVERVIEW');
    xarRegisterMask('ViewSiteContact', 'All', 'sitecontact', 'Item', 'All:All:All', 'ACCESS_OVERVIEW');
    xarRegisterMask('ReadSiteContact', 'All', 'sitecontact', 'Item', 'All:All:All', 'ACCESS_READ');
    xarRegisterMask('SubmitSiteContact', 'All', 'sitecontact', 'Item', 'All:All:All', 'ACCESS_COMMENT'); //required where saving forms is done
    xarRegisterMask('EditSiteContact', 'All', 'sitecontact', 'Item', 'All:All:All', 'ACCESS_EDIT');
    xarRegisterMask('AddSiteContact', 'All', 'sitecontact', 'Item', 'All:All:All', 'ACCESS_ADD');
    xarRegisterMask('DeleteSiteContact', 'All', 'sitecontact', 'Item', 'All:All:All', 'ACCESS_DELETE');
    xarRegisterMask('AdminSiteContact', 'All', 'sitecontact', 'Item', 'All:All:All', 'ACCESS_ADMIN');
    // Initialisation successful

    //This initialization takes us to version 0.6.1 - continue in upgrade
    return sitecontact_upgrade('0.6.1');
}

/**
 * upgrade the SiteContact module from an old version
 * This function can be called multiple times
 */
function sitecontact_upgrade($oldversion)
{
    $dbconn = xarDB::getConn();
    $xarTables = xarDB::getTables();
      xarDBLoadTableMaintenanceAPI();
    $sitecontactTable = $xarTables['sitecontact'];
    $sitecontactResponseTable = $xarTables['sitecontact_response'];
    // Upgrade dependent on old version number
    switch ($oldversion) {

       case '0.6.1':
            xarModSetVar('sitecontact','useantibot',true);
       case '1.0.0':
           if (!xarModAPIFunc('blocks', 'admin', 'register_block_type',
                        array('modName' => 'sitecontact',
                              'blockType' => 'sitecontact'))) return;
       case '1.0.1':
           if (!xarModRegisterHook('item', 'waitingcontent', 'GUI',
                           'sitecontact', 'admin', 'waitingcontent')) {
                return false;
           }
       case '1.0.3':
          //overwrite masks that had component 'items' and three parts to instances
          xarRegisterMask('ViewSiteContact',      'All', 'sitecontact', 'ContactForm', 'All', 'ACCESS_OVERVIEW');
          xarRegisterMask('ReadSiteContact',      'All', 'sitecontact', 'ContactForm', 'All', 'ACCESS_READ');
          xarRegisterMask('SubmitSiteContact',    'All', 'sitecontact', 'ContactForm', 'All', 'ACCESS_COMMENT');
          xarRegisterMask('EditSiteContact',      'All', 'sitecontact', 'ContactForm', 'All', 'ACCESS_EDIT');
          xarRegisterMask('AddSiteContact',       'All', 'sitecontact', 'ContactForm', 'All', 'ACCESS_ADD');
          xarRegisterMask('DeleteSiteContact',    'All', 'sitecontact', 'ContactForm', 'All', 'ACCESS_DELETE');
          xarRegisterMask('AdminSiteContact',     'All', 'sitecontact', 'ContactForm', 'All', 'ACCESS_ADMIN');

       case '1.1.0':
          xarModSetVar('sitecontact','adminccs',false);
          xarModSetVar('sitecontact','admincclist','');

        case '1.1.1' : //current version
            //redefine instances - no need to update the privileges themselves only instance
            xarRemoveInstances('sitecontact'); //remove current ones
            //redefine
            $query1 = "SELECT DISTINCT xar_scid, xar_sctypename FROM  $sitecontactTable";
            $instances = array(
                        array('header' => 'Form ID:',
                                'query' => $query1,
                                'limit' => 20
                            )
                    );
            xarDefineInstance('sitecontact', 'ContactForm', $instances);

        case '1.1.3' :
        case '1.2.0' :
        case '1.2.1' : //current version
            //update version to indicate some minor changes in files
        case '1.3.0':
            //update for 1.4.0 core DD changes
            //update for larger IP address field
            //create temp table column
            $query = xarDBAlterTable($sitecontactResponseTable ,
                              array('command' => 'add',
                                    'field'   => 'xar_temp_useripaddress',
                                    'type'    => 'varchar',
                                    'size'    => '40',
                                    'default' => ''));
            $result = $dbconn->Execute($query);
            //copy data from old to temp column
            $query = "SELECT xar_scrid, xar_useripaddress
                     FROM $sitecontactResponseTable";
            $result = $dbconn->Execute($query);

            for (; !$result->EOF; $result->MoveNext()) {
              list($scrid, $userip) = $result->fields;
              // Covert the first field data
                 //Copy to temp fields
              $query1 = "UPDATE $sitecontactResponseTable
                          SET xar_temp_useripaddress = '".$userip."'
                         WHERE xar_scrid  = '".$scrid."'";
                $result2 = $dbconn->Execute($query1);
            }
            //get rid of the original column
            $dropquery="ALTER TABLE $sitecontactResponseTable DROP xar_useripaddress";
            $result = $dbconn->Execute($dropquery);
            //add back the column with correct definition
            $addquery = xarDBAlterTable($sitecontactResponseTable,
                              array('command' => 'add',
                                    'field'   => 'xar_useripaddress',
                                    'type'    => 'varchar',
                                    'size'    => '40',
                                    'default' => ''));
            $result = $dbconn->Execute($addquery);
            if (!$result) return;
            //copy from temp to final table
            $copyquery= "SELECT COUNT(1)
                        FROM $sitecontactResponseTable";
            $result = $dbconn->Execute($copyquery);
            for (; !$result->EOF; $result->MoveNext()) {

               $docopy = "UPDATE $sitecontactResponseTable
                          SET xar_useripaddress  = xar_temp_useripaddress";
               $doupdate = $dbconn->Execute($docopy);
               if (!$doupdate) return;
            }
            //drop the temp table column
            $query="ALTER TABLE $sitecontactResponseTable DROP xar_temp_useripaddress";
            // Pass to ADODB, and send exception if the result isn't valid.
            $result = $dbconn->Execute($query);

            //update with templates for 1.4.0 and status codes
        case '1.3.1':

        case '1.3.2':
        //add new check banned feature
            xarModSetVar('sitecontact','checkbanned', FALSE);
        case '1.3.3': //current version
             break;
    }
    // Update successful
    return true;
}

/**
 * delete the SiteContact module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function sitecontact_delete()
{

    /* Let's clean up - delete the DD objects we have created if any, for each form type */
    $formtypes = xarModAPIFunc('sitecontact','user','getcontacttypes');
    $moduleid= xarModGetIDFromName('sitecontact');

    if (is_array($formtypes)) {
        foreach ($formtypes as $formtype) {
            $objectinfo= xarModAPIFunc('dynamicdata','user','getobjectinfo',
                array('moduleid'=>$moduleid, 'itemtype'=>$formtype['scid']));

            $objectid= $objectinfo['objectid'];

            if (!empty($objectid)) {
                xarModAPIFunc('dynamicdata','admin','deleteobject',array('objectid' => $objectid));
            }
        }
    }


    /* drop the sitecontact table */
    $dbconn = xarDBGetConn();
    $xarTables = xarDBGetTables();

    $sitecontactTable = $xarTables['sitecontact'];
    $datadict = xarDBNewDataDict($dbconn, 'ALTERTABLE');
    $result = $datadict->dropTable($sitecontactTable);

    $sitecontactResponseTable = $xarTables['sitecontact_response'];
    $datadict = xarDBNewDataDict($dbconn, 'ALTERTABLE');
    $result = $datadict->dropTable($sitecontactResponseTable);


    /* Remove any module aliases before deleting module vars */
    $aliasname =xarModGetVar('sitecontact','aliasname');
    $isalias = xarModGetAlias($aliasname);
    if (isset($isalias) && ($isalias =='sitecontact')){
        xarModDelAlias($aliasname,'sitecontact');
    }
    // Delete any module variables
     xarModDelAllVars('sitecontact');
    // UnRegister blocks
    if (!xarModAPIFunc('blocks', 'admin', 'unregister_block_type',
                 array('modName' => 'sitecontact',
                       'blockType' => 'sitecontact'))) return;

    // Remove module hooks
    if (!xarModUnregisterHook('item', 'waitingcontent', 'GUI',
            'sitecontact', 'user', 'waitingcontent')) {
        return false;
    }

    xarRemoveMasks('sitecontact');
    xarRemoveInstances('sitecontact');

    // Deletion successful
    return true;
}

?>