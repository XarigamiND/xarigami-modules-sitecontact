<?php
/**
 * Add sitecontact tables to the tables table array
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/**
 * Return sitecontact table names 
 *
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 * @access private
 * @return array sitecontact table information
 */
function sitecontact_xartables()
{ 
    // Initialise table array
    $xarTables = array();

    $sitecontactTable          = xarDBGetSiteTablePrefix() . '_sitecontact';
    $xarTables['sitecontact']  = $sitecontactTable;

    $sitecontactResponseTable          = xarDBGetSiteTablePrefix() . '_sitecontact_response';
    $xarTables['sitecontact_response'] = $sitecontactResponseTable;
    
    // Return the table information
    return $xarTables;
} 
?>