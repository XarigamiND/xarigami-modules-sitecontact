<?php
/**
 * Utility function to retrieve the list of item types
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
 * Utility function to retrieve the list of item types of this module (if any)
 *
 * @author jojodee
 * @return array containing the item types and their description
 */
function sitecontact_userapi_getitemtypes($args)
{
    $itemtypes = array();

   // Get contact page types
   $contacttypes = xarModAPIFunc('sitecontact','user','getcontacttypes');
   
   foreach ($contacttypes as $id => $contacttype) {
       $itemtypes[$contacttype['scid']] = array('label' => xarVarPrepForDisplay($contacttype['sctypedesc']),
                                'title' => xarVarPrepForDisplay(xarML('Display #(1)',$contacttype['sctypename'])),
                                'url'   => xarModURL('sitecontact','user','view',array('scid' => $contacttype['scid']))
                               );
   }

   return $itemtypes;
}
?>