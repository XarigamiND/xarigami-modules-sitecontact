<?php
/**
 * Code creation
 *
 * @subpackage Xarigami Sitecontact
 * @copyright (C) 2010-2011 2skies.com
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/*
 * Author jojodee
 * Copied from Recommend module createidcode - review and see if we can put this somewhere common
*/
define('_SYLLABLES', "*abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz0123456789");
define('_MAKEPASS_BOX', 5000);
define('_MAKEPASS_LEN', 8);
define('SALT_LENGTH', 9);
function _make_seed()
{
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

function sitecontact_userapi_createidcode()
{
    $result = '';
    mt_srand(_make_seed());
    $syllables = _SYLLABLES;
    $len = strlen($syllables) - 1;
    $box = ""; 

    // create box
    for($i = 0; $i < _MAKEPASS_BOX; $i++) {
        $ch = $syllables[mt_rand(0, $len)];
        // about 20% upper case letters
        if (mt_rand(0, $len) % 5 == 1) {
            $ch = strtoupper($ch);
        }
        // filling up the box with random chars
        $box .= $ch;
    }


    for($i = 0; $i < _MAKEPASS_LEN; $i++) {
        $result .= $box[mt_rand(0, (_MAKEPASS_BOX - 1))];
    }    
    
    $pepper = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
    
    $pepper = substr($result, 0, SALT_LENGTH);

    $validationcode = sha1($pepper . $result);

    return $validationcode;
}
?>