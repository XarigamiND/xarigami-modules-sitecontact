<?php
/**
 * Sitecontact itemtypes
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
 * get the name and description of all Sitecontact page scid types
 * @returns array
 */
function sitecontact_userapi_getcontacttypes($args)
{
    extract($args);

    if (!isset($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = -1;
    }
   if (isset($args['sort'])) {
        $sort = $args['sort'];
    } else {
        $sort = xarModGetVar('sitecontact','defaultsort');
    }
    if (empty($sort)) {
        $sort = 'scid';
    }
  $where ='';
    $bindvars=array();
     if (isset($sctypename) && !empty($sctypename)) {
       $where = ' xar_sctypename = ?';
       $bindvars[]=$sctypename;
    }
    if (isset($scid) && !empty($scid)) {
       if (isset($sctypename)) {
        $where .= ' AND xar_scid = ? ';
       } else {
       $where = ' xar_scid = ?';
       }
       $bindvars[]=$scid;
    }

    if (!isset($scid) && !isset($sctypename)) {
     $where ='';
    }

    // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $sitecontactTable = $xartable['sitecontact'];

    // Get item
    $query = "SELECT xar_scid,
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
              xar_soptions
            FROM $sitecontactTable ";
    if (!empty($where)) {
        $query .= "WHERE $where ";
    }
    switch ($sort) {
        case 'name':
            $query .= " ORDER BY xar_sctypename ASC";
            break;
        case 'desc':
            $query .= " ORDER BY xar_sctypedesc ASC";
            break;
        case 'scid':
        default:
            $query .= " ORDER BY xar_scid ASC";
            break;
    }

    $result = $dbconn->SelectLimit($query, $numitems, $startnum-1,$bindvars );

    if (!$result) return;
    $sctypes=array();

    while (!$result->EOF) {
        list($scid, $sctypename, $sctypedesc, $customtext, $customtitle, $optiontext,
             $webconfirmtext, $notetouser, $allowcopy, $usehtmlemail, $scdefaultemail, $scdefaultname, $scactive,
             $savedata,$permissioncheck,$termslink,$soptions) = $result->fields;
            $sctypes[$scid] =     array('scid'     => (int)$scid,
                                   'sctypename'     => $sctypename,
                                   'sctypedesc'     => $sctypedesc,
                                   'customtext'     => $customtext,
                                   'customtitle'    => $customtitle,
                                   'optiontext'     => $optiontext,
                                   'webconfirmtext' => $webconfirmtext,
                                   'notetouser'     => $notetouser,
                                   'allowcopy'      => $allowcopy,
                                   'usehtmlemail'  => $usehtmlemail,
                                   'scdefaultemail' => $scdefaultemail,
                                   'scdefaultname'  => $scdefaultname,
                                   'scactive'       => (int)$scactive,
                                   'savedata'       => (int)$savedata,
                                   'permissioncheck'=> (int)$permissioncheck,
                                   'termslink'      => $termslink,
                                   'soptions'       => $soptions);
        $result->MoveNext();
    }

    return $sctypes;
}
?>