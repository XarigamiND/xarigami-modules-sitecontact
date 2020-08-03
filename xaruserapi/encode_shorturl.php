<?php
/**
 * Encode short urls
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
 * return the path for a short URL to xarModURL for this module
 *
 * @author Jo Dalle Nogare
 * @param  $args the function and arguments passed to xarModURL
 * @return string path to be added to index.php for a short URL, or empty if failed
 */
function sitecontact_userapi_encode_shorturl($args)
{
    /* Get arguments from argument array */
    extract($args);

    /* Check if we have something to work with */
    if (!isset($func)) {
        return;
    }
    $aliasisset = xarModGetVar('sitecontact', 'useModuleAlias');
    $aliasname = xarModGetVar('sitecontact','aliasname');
    if (($aliasisset) && isset($aliasname)) {
        $usealias   = true;
    } else{
        $usealias = false;
    }

    $path = '';
    /* if we want to add some common arguments as URL parameters below */
    $join = '?';
    /* we can't rely on xarModGetName() here -> you must specify the modname */
    $module = 'sitecontact';
    $alias = xarModGetAlias($module);
    if (isset($sctypename) && !isset($scform)) {
         $scform=$sctypename;
    }
    /* specify some short URLs relevant to your module */
    if ($func == 'main') {
        if (($module == $alias) && ($usealias)){
            /* OK, we can use a 'fake' module name here */
            $path = '/' . $aliasname . '/';
            if (isset($scid) && isset($message))  {
                $path = '/' . $aliasname . '/' . $message .'/' . $scid;
            } elseif (isset($message) && (!isset($scid))) {
                $path = '/' . $aliasname . '/' . $message;
            } elseif (isset($scform) && is_string($scform) && !isset($message)) {
               $path = '/' . $aliasname . '/' . $scform;
            }
        }else {
            $path = '/' . $module . '/';
            if (isset($scid) && isset($message))  {
                $path = '/' . $module . '/' . $message .'/' . $scid;
            } elseif (isset($message) && (!isset($scid))) {
                $path = '/' . $module . '/' . $message;
            } elseif (isset($scform) && is_string($scform) && !isset($message)) {
               $path = '/' . $module . '/' . $scform;
            }
        }
    } elseif ($func == 'contactus' || $func == 'respond') {
          if (($module == $alias) && ($usealias)){
              $path = '/' . $aliasname . '/respond';

              if (isset($message)&& is_numeric($message) && isset($scid) && is_numeric($scid)) {
                  $path = '/' .$aliasname  . '/respond/' . $message.'/'. $scid;
              } elseif (isset($message) && is_numeric($message) && !isset($scid)) {
                     $path = '/' .$aliasname  . '/respond/' .$message;
              }
          }else {
           if (isset($message) && is_numeric($message) && isset($scid) && is_numeric($scid)) {
                  $path = '/' .$module  . '/respond/' .$message.'/'. $scid;
              } elseif (!isset($message) && isset($scid)){
                    $path = '/' .$module  . '/respond/' .$scform;
              } elseif (isset($message) && !isset($scid)) {
                     $path = '/' .$module  . '/respond/'. $message;
              }
          }
    } 
    /* add some other module arguments as standard URL parameters */
    if (!empty($path)) {
        $pathExtras = array();

        if (isset($startnum)) {
            $pathExtras[] = 'startnum=' . $startnum;
        }

        if (!empty($catid)) {
            $pathExtras[] = 'catid=' . $catid;
        } elseif (!empty($cids) && count($cids) > 0) {
            if (!empty($andcids)) {
                $catid = join('+', $cids);
            } else {
                $catid = join('-', $cids);
            }
            $pathExtras[] = 'catid=' . $catid;
        }

        if (!empty($company)) {
            $pathExtras[] = 'company=' . urlencode($company);
        }

        if (!empty($usermessage)) {
            $pathExtras[] = 'usermessage=' . urlencode($usermessage);
        }

        if (!empty($requesttext)) {
            $pathExtras[] = 'requesttext=' . urlencode($requesttext);
        }

        if (!empty($antibotinvalid)) {
            $pathExtras[] = 'antibotInavlid=' . urlencode($antibotinvalid);
        }

        if (!empty($pathExtras)) {
            $path .= '?' . implode('&', $pathExtras);
        }

    }

    return $path;
}
?>
