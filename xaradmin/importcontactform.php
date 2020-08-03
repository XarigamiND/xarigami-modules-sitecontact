<?php
/**
 * Sitecontact module
 *
 * @package Xarigami
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C) 2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Import an object definition or an object item from XML
 */
function sitecontact_admin_importcontactform($args)
{
    if (!xarSecurityCheck('AdminSitecontact')) return xarResponseForbidden();

    if(!xarVarFetch('import', 'isset', $import,  NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('xml', 'isset', $xml,  NULL, XARVAR_DONT_SET)) {return;}

    extract($args);

    $data = array();
   //common menu link
    $data['menulinks'] = xarModAPIFunc('sitecontact','admin','getmenulinks');

    $data['menutitle'] = xarML('Sitecontact Import Utilities');

    $data['warning'] = '';
    $data['options'] = array();

    $basedir = sys::code().'modules/sitecontact';
    $filetype = 'xml';
    $files = xarModAPIFunc('dynamicdata','admin','browse',
                           array('basedir' => $basedir,
                                 'filetype' => $filetype));
    if (!isset($files) || count($files) < 1) {
        $files = array();
        $data['warning'] = xarML('There are currently no XML files available for import in "#(1)"',$basedir);
    }

    if (!empty($import) || !empty($xml)) {
        if (!xarSecConfirmAuthKey()) return;

        if (!empty($import)) {
            $found = '';
            foreach ($files as $file) {
                if ($file == $import) {
                    $found = $file;
                    break;
                }
            }
            if (empty($found) || !file_exists($basedir . '/' . $file)) {
                $msg = xarML('File not found');
                 throw new BadParameterException(null,$msg);
            }
            $scid = xarModAPIFunc('sitecontact','admin','importcontactform',
                                  array('file' => $basedir . '/' . $file));
        } else {
            $scid = xarModAPIFunc('sitecontact','admin','importcontactform',
                                  array('xml' => $xml));
        }
        if (empty($scid)) return;

        $data['warning'] = xarML('Sitecontact form  type #(1) was successfully imported',$scid);
        xarTplSetMessage($data['warning'],'status');
    }

    natsort($files);
    array_unshift($files,'');
    foreach ($files as $file) {
         $data['options'][] = array('id' => $file,
                                    'name' => $file);
    }

    $data['authid'] = xarSecGenAuthKey();
    return $data;
}

?>
