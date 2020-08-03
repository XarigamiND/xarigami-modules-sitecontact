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
function sitecontact_adminapi_importcontactform($args)
{
    // Security check - we require ADMIN rights here
    if (!xarSecurityCheck('AdminSitecontact')) return xarResponseForbidden();

    extract($args);

    if (empty($xml) && empty($file)) {
        $msg = xarML('Missing import file or XML content');
         throw new BadParameterException(null,$msg);
    } elseif (!empty($file) && (!file_exists($file) || !preg_match('/\.xml$/',$file)) ) {
        $msg = xarML('Invalid import file');
         throw new BadParameterException(null,$msg);
    }

    $formtypes = xarModAPIFunc('sitecontact','user','getcontacttypes');

    $proptypes = xarModAPIFunc('dynamicdata','user','getproptypes');
    $name2id = array();
    foreach ($proptypes as $propid => $proptype) {
        $name2id[$proptype['name']] = $propid;
    }

    $prefix = xarDBGetSystemTablePrefix();
    $prefix .= '_';

    if (!empty($file)) {
        $fp = @fopen($file, 'r');
        if (!$fp) {
            $msg = xarML('Unable to open import file');
             throw new BadParameterException(null,$msg);
        }
    } else {
        $lines = preg_split("/\r?\n/", $xml);
        $maxcount = count($lines);
    }

    $what = '';
    $count = 0;
    $scid = 0;
    $objectname2objectid = array();
    $objectcache = array();
    $objectmaxid = array();
     $closetag = 'N/A';
    while ( (!empty($file) && !feof($fp)) || (!empty($xml) && $count < $maxcount) ) {
        if (!empty($file)) {
            $line = fgets($fp, 4096);
        } else {
            $line = $lines[$count];
        }
        $count++;
        if (empty($what)) {
            if (preg_match('#<object name="(\w+)">#',$line,$matches)) { // in case we import the object definition
                $object = array();
                $object['name'] = $matches[1];
                $what = 'object';
            } elseif (preg_match('#<items>#',$line)) { // in case we only import data
                $what = 'item';
            }

         } elseif ($what == 'object') {
            if (preg_match('#<([^>]+)>(.*)</\1>#',$line,$matches)) {
                $key = $matches[1];
                $value = $matches[2];
                if (isset($object[$key])) {
                    $msg = xarML('Duplicate definition for #(1) key #(2) on line #(3)','object',xarVarPrepForDisplay($key),$count);
                    if (!empty($file)) fclose($fp);
                     throw new DuplicateException(null,$msg);
                    return;
                }
                $object[$key] = $value;
            } elseif (preg_match('#<config>#',$line)) {
                if (isset($object['config'])) {
                    $msg = xarML('Duplicate definition for #(1) key #(2) on line #(3)','object','config',$count);
                    if (!empty($file)) fclose($fp);
                     throw new DuplicateException(null,$msg);
                    return;
                }
                $config = array();
                $what = 'config';
            } elseif (preg_match('#<properties>#',$line)) {
                if (empty($object['name']) || empty($object['moduleid'])) {
                    $msg = xarML('Missing keys in object definition');
                    if (!empty($file)) fclose($fp);
                    throw new DuplicateException(null,$msg);
                    return;
                }
                // make sure we drop the object id, because it might already exist here
                unset($object['objectid']);

                $properties = array();
                $what = 'property';
            } elseif (preg_match('#<items>#',$line)) {
                $what = 'item';
            } elseif (preg_match('#</object>#',$line)) {
                $what = '';
            } else {
                // multi-line entries not relevant here
            }
        } elseif ($what == 'config') {

           
            if (preg_match('#<([^>]+)>(.*)</\1>#s',$line,$matches)) {
                $key = $matches[1];
                $value = $matches[2];
                if (isset($config[$key])) {
                    $msg = xarML('Duplicate definition for #(1) key #(2) on line #(3)','config',xarVarPrepForDisplay($key),$count);
                    if (!empty($file)) fclose($fp);
                    throw new DuplicateException(null,$msg);
                    return;
                }
                $config[$key] = $value;
                   
                 $closetag = 'N/A';
            } elseif (preg_match('#<([^/>]+)>(.*)#',$line,$matches)) {
            // multi-line entries *are* relevant here
                $key = $matches[1];
                $value = $matches[2];
                if (isset($config[$key])) {
                    $msg = xarML('Duplicate definition for #(1) key #(2)','item',xarVarPrepForDisplay($key));
                    if (!empty($file)) fclose($fp);
                     throw new DuplicateException(null,$msg);
                    return;
                }
                $config[$key] = $value;
               $closetag = $key;
                 
                 $what = 'config';
                 //   $closetag = 'N/A';
            } elseif (preg_match("#(.*)</$closetag>#",$line,$matches)) {
                // multi-line entries *are* relevant here
                $value = $matches[1];
                if (!isset($config[$closetag])) {
                    $msg = xarML('Undefined #(1) key #(2)','item',xarVarPrepForDisplay($closetag));
                    if (!empty($file)) fclose($fp);
                    return;
                }
                $config[$closetag] .= $value;
                $closetag = 'N/A';
                  $what = 'config';
            } elseif ($closetag != 'N/A') {
                // multi-line entries *are* relevant here
                if (!isset($config[$closetag])) {
                    $msg = xarML('Undefined #(1) key #(2)','item',xarVarPrepForDisplay($closetag));
                    if (!empty($file)) fclose($fp);
                    throw new BadParameterException(null,$msg);;
                    return;
                }
                $config[$closetag] .= $line;
                 $what = 'config';
            } elseif (preg_match('#</config>#',$line)) {
                // $object['config'] = serialize($config);
                //$config = array();
                $what = 'object';
            
            } else {
                // multi-line entries not relevant here
            }

         
        } elseif ($what == 'property') {
             
            if (preg_match('#<property name="(\w+)">#',$line,$matches)) {
                $property = array();
                $property['name'] = $matches[1];
            } elseif (preg_match('#</property>#',$line)) {
                if (empty($property['name']) || empty($property['type'])) {
                    $msg = xarML('Missing keys in property definition');
                    if (!empty($file)) fclose($fp);
                     throw new DuplicateException(null,$msg);
                    return;
                }
                // make sure we drop the property id, because it might already exist here
                unset($property['id']);

            // TODO: watch out for multi-sites
                // replace default xar_* table prefix with local one
                $property['source'] = preg_replace("/^xar_/",$prefix,$property['source']);

                // add this property to the list
                $properties[] = $property;

            } elseif (preg_match('#<([^>]+)>(.*)</\1>#',$line,$matches)) {
                $key = $matches[1];
                $value = $matches[2];
                if (isset($property[$key])) {
                    $msg = xarML('Duplicate definition for #(1) key #(2) on line #(3)','property',xarVarPrepForDisplay($key),$count);
                    if (!empty($file)) fclose($fp);
                     throw new DuplicateException(null,$msg);
                    return;
                }
                $property[$key] = $value;
            } elseif (preg_match('#</properties>#',$line)) {

                // 1. make sure we have a unique scform name
                foreach ($formtypes as $scid => $formtype) {
                    if ($object['name'] == $formtype['sctypename']) {
                        $object['name'] .= '_' . time();
                        break;
                    }
                }
                // 2. fill in the other formtype fields
                $fields = array();
                $extra = array();
                foreach ($properties as $property) {
                    $field = $property['name'];
                    switch($field) {
                        case 'scrid':
                        case 'scid':
                            // skip these
                            break;

                        case 'username':
                        case 'useremail':
                        case 'requesttext':
                        case 'company':
                        case 'usermessage':
                        case 'userreferer':
                        case 'sendcopy':
                        case 'permission':
                        case 'bccrecipients':
                        case 'ccrecipients':
                        case 'responsetime':
                        case 'useripaddress':
                          //we don't need this for now.
                          /*   // convert property type to string if necessary
                            if (is_numeric($property['type'])) {
                                if (isset($proptypes[$property['type']])) {
                                    $property['type'] = $proptypes[$property['type']]['name'];
                                } else {
                                    $property['type'] = 'static';
                                }
                            }
                            // reset disabled field labels to empty
                            if (empty($property['status'])) {
                                $property['label'] = '';
                            }
                            if (!isset($property['validation'])) {
                                $property['validation'] = '';
                            }*/
                             // skip these too as they are only relevant for responses and we are not importing them yet
                        break;

                        default:
                            // convert property type to numeric if necessary
                            if (!is_numeric($property['type'])) {
                                if (isset($name2id[$property['type']])) {
                                    $property['type'] = $name2id[$property['type']];
                                } else {
                                    $property['type'] = 1;
                                }
                            }
                            $extra[] = $property;
                            break;
                    }

                }

                // 3. create the contact form type

                $scidcreate = xarModAPIFunc('sitecontact','admin','createsctype',
                                      array('sctypename'    => $object['name'],
                                            'sctypedesc'    => $object['label'],
                                            'customtext'    => $config['customtext'],
                                            'customtitle'   => $config['customtitle'],
                                            'optiontext'    => $config['optiontext'],
                                            'webconfirmtext'=> $config['webconfirmtext'],
                                            'notetouser'    => $config['notetouser'],
                                            'allowcopy'     => $config['allowcopy'],
                                            'usehtmlemail'  => $config['usehtmlemail'],
                                            'scdefaultemail'=> $config['scdefaultemail'],
                                            'scdefaultname' => $config['scdefaultname'],
                                            'scactive' => $config['scactive'],
                                            'savedata'  => $config['savedata'],
                                            'permissioncheck'=> $config['permissioncheck'],
                                            'termslink'=> $config['termslink'],
                                            'soptions'=> $config['soptions']
                                            )
                                        );
                if (empty($scidcreate)) {
                  
                return;
                }
                $scid= $scidcreate['sctypeid'];
                // 4. set the module variables
                //none yet

                // 5. create a dynamic object if necessary
                if (count($extra) > 0) {
                    $object['itemtype'] = $scid;
                    $object['config'] = '';
                    $object['isalias'] = 0;
                    $objectid = xarModAPIFunc('dynamicdata','admin','createobject',
                                              $object);
                    if (!isset($objectid)) {
                        if (!empty($file)) fclose($fp);
                        return;
                    }

                    // 6. create the dynamic properties
                    foreach ($extra as $property) {
                        $property['objectid'] = $objectid;
                        $property['moduleid'] = $object['moduleid'];
                        $property['itemtype'] = $object['itemtype'];

                        $prop_id = xarModAPIFunc('dynamicdata','admin','createproperty',
                                                 $property);
                        if (!isset($prop_id)) {
                            if (!empty($file)) fclose($fp);
                            return;
                        }
                    }

                    // 7. check if we need to enable DD hooks for this pubtype
                    if (!xarModIsHooked('dynamicdata','sitecontact')) {
                        xarModAPIFunc('modules','admin','enablehooks',
                                      array('callerModName' => 'sitecontact',
                                            'callerItemType' => scid,
                                            'hookModName' => 'dynamicdata'));
                    }
                }

                $properties = array();
                $what = 'object';
            } elseif (preg_match('#<items>#',$line)) {
                $what = 'item';
            } elseif (preg_match('#</object>#',$line)) {
                $what = '';
            } else {
                // multi-line entries not relevant here
            }
       
        } elseif ($what == 'item') {
            if (preg_match('#</items>#',$line)) {
                $what = 'object';
            } elseif (preg_match('#</object>#',$line)) {
                $what = '';
            } else {
            }
            print_r('5 '.$key);
        } else {
        }
    }
    if (!empty($file)) {
        fclose($fp);
    }
    return $scid;
}

?>
