<?php
/**
 * Delete
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Delete a response
 *
 * Standard function to delete a module item
 *
 * @param  $args ['scrid'] ID of the item
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function sitecontact_adminapi_delete($args)
{
    extract($args);
    if (!isset($scrid) || !is_numeric($scrid)) {
        throw new BadParameterException('scrid');
    }
    $item = xarModAPIFunc('sitecontact','user','get', array('scrid' => $scrid));
    if (!isset($item) || !is_array($item)) {
        $msg = xarML('Item with scrid  response id #(1) not found',$scrid);
            return xarResponseNotFound($msg);
    }
    $scid=$item['scid'];

    if (!xarSecurityCheck('DeleteSitecontact',1)) {
        return;
    }
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $sitecontactResponseTable = $xartable['sitecontact_response'];

    $query = "DELETE FROM $sitecontactResponseTable WHERE xar_scrid = ?";

    /* The bind variable $exid is directly put in as a parameter. */
    $result = $dbconn->Execute($query,array($scrid));

    if (!$result) return;
    $item['module'] = 'sitecontact';
    $item['itemtype'] = $scid;
    $item['itemid'] = $scrid;
    xarModCallHooks('item', 'delete', $scrid, $item);

    /* Let the calling process know that we have finished successfully */
    return true;
}
?>