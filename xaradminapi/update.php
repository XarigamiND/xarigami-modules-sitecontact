<?php
/**
 * Create a response record
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/**
 * Update a response record
 * Usage : $scrid = xarModAPIFunc('sitecontact', 'admin', update', $response);
 *
 * Create a new response record for a user and specific form - only the scid is required
 * as we must have a way to allow for users that disagree to saving data in the db but we still allow to fill in
 * these are treated as 'missing values' rather than no response at all, for statistical reasons if needed
 *
 * @param $args['username'] name of the respondent
 * @param $args['useremail'] email address of the respondent
 * @param $args['requesttext'] email subject
 * @param $args['company'] company name
 * @param $args['usermessage'] message text
 * @param $args['scid'] the specific site contact form id (required)

 * @return response item ID on success, false on failure
 */
function sitecontact_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check (all the rest is optional, and set to defaults below)
    if (empty($scid) || !is_int($scid)) {
        throw new EmptyParameterException('scid');
    }
    // Argument check (all the rest is optional, and set to defaults below)
    if (empty($scrid) || !is_int($scrid)) {
        throw new EmptyParameterException('scrid');
    }
    

     // Security check

     if(!xarSecurityCheck('DeleteSiteContact', 0, 'ContactForm', "$scid:All:All")) return; // we don't want to error display here and distrupt

    // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $sitecontactResponseTable = $xartable['sitecontact_response'];

    // Add item
    $query = "UPDATE $sitecontactResponseTable 
              SET xar_scid =?,
              xar_username=?,
              xar_useremail = ?,
              xar_requesttext = ?,
              xar_company =?,
              xar_usermessage =?
              
             WHERE xar_scrid = ?";
    $bindvars = array( (int)     $scid,
                      (string)  $username,
                      (string)  $useremail,
                      (string)  $requesttext,
                      (string)  $company,
                                $usermessage,
                      (int)     $scrid);
    $result = $dbconn->Execute($query,$bindvars);
    if (!$result) return;

    // Call modify hooks for dynamic data but only if permission given

       $args['scrid'] = $scrid;
       $args['module'] = 'sitecontact';
       $args['itemtype'] = $scid;
       $args['itemid'] = $scrid;
       $args['objectid'] = $scrid;
       xarModCallHooks('item', 'update', $scrid, $args);

    return true;
}

?>