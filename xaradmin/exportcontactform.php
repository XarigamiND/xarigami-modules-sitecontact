<?php
/**
 * Articles module
 *
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami SiteContact Module
 * @copyright (C)  2004-2011 2skies.com
 * @link http://xarigami.com/project/sitecontact
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * export the definition of a contact form and output to pseudo DD format
 */
function sitecontact_admin_exportcontactform($args)
{
    extract($args);

    // Get parameters
    if (!xarVarFetch('scid','isset', $scid, NULL, XARVAR_DONT_SET)) {return;}

    if (!xarSecurityCheck('AdminSiteContact')) return xarResponseForbidden();

    $sctypes = xarModAPIFunc('sitecontact','user','getcontacttypes');

    if (empty($scid) || empty($sctypes[$scid])) {
        $msg = xarML('Invalid sitecontact form #(1)',
                     xarVarPrepForDisplay($scid));
        throw new BadParameterException(null,$msg);
    }
    $scform = $sctypes[$scid];

    // Initialise the template variables
    $data = array();
        //common menulink
    $data['menulinks'] = xarModAPIFunc('sitecontact','admin','getmenulinks');
    $data['sctypedesc'] = $scform['sctypedesc'];


    // Configurable fields for contactform
    $configfields = array('sctypedesc','customtext','customtitle','optiontext','webconfirmtext','notetouser','allowcopy', 'usehtmlemail', 
                        'scdefaultemail', 'scdefaultname', 'scactive', 'savedata','permissioncheck','termslink', 'soptions'
                    );

    // Start the dynamic object definition (cfr. DD export)
    $data['xml'] = '<object name="' . $scform['sctypename'] . '">
    <label>' . xarVarPrepForDisplay($scform['sctypedesc']) . '</label>
    <moduleid>' . xarModGetIDFromName('sitecontact') . '</moduleid>
    <itemtype>' . $scid . '</itemtype>
    <urlparam>scrid</urlparam>
    <maxid>0</maxid>
    <config>
    ';
    
    foreach ($configfields as $config) {
        if (!isset($scform[$config])) continue;
         $data['xml'] .= "    <$config>$scform[$config]</$config>\n";
    }
 // Check if we're using this as an alias for short URLs
    if (xarModGetAlias($scform['sctypename']) == 'sitecontact') {
        $isalias = 1;
    } else {
        $isalias = 0;
    }

    $data['xml'] .= '  </config>
    <isalias>' . $isalias . '</isalias> 
      <properties>
        <property name="scrid">
          <id>1</id>
          <label>Response ID</label>
          <type>itemid</type>
          <default></default>
          <source>xar_sitecontact_response.xar_scrid</source>
          <status>1</status>
        </property>
        <property name="scid">
          <id>2</id>
          <label>Contactform Type</label>
          <type>itemtype</type>
          <default>1</default>
          <source>xar_sitecontacth_response.xar_scid</source>
          <status>1</status>
        </property>
    ';

     // property fields for contactform
    $propfields = array('username'=>array('label'=>'User Name','format'=>'textbox', 'input'=>1),
                    'useremail'=>array('label'=>'User email','format'=>'email', 'input'=>1),
                    'requesttext'=>array('label'=>'Subject','format'=>'textbox', 'input'=>1),
                    'company'=>array('label'=>'Company','format'=>'textbox', 'input'=>1),
                    'usermessage'=>array('label'=>'Message','format'=>'textarea_medium', 'input'=>1),
                    'userreferer'=>array('label'=>'Referer','format'=>'textbox', 'input'=>0),
                    'sendcopy'=>array('label'=>'Copy to user','format'=>'checkbox', 'input'=>1),
                    'permission'=>array('label'=>'Permission','format'=>'checkbox', 'input'=>1),
                    'bccrecipients'=>array('label'=>'BC Recipients','format'=>'textarea_small', 'input'=>1),
                    'ccrecipients'=>array('label'=>'CC Recipients','format'=>'textarea_small', 'input'=>1),
                    'responsetime'=>array('label'=>'Response time','format'=>'textbox', 'input'=>0),
                    'useripaddress'=>array('label'=>'User IP','format'=>'textbox', 'input'=>1)
                    );
                    
 
    $id = 3;
    foreach ($propfields as $field=>$specs) {
        if (empty($specs['label'])) {
            $specs['label'] = ucwords($field);
            $status = 0;
        } elseif ($field == 'customtext') {
            $status = 2;
        } else {
            $status = 1;
        }

        if (empty($specs['input'])) {
            $specs['input'] = 0;
        } else {
            $specs['input'] = 1;
        }
        if (!isset($specs['validation'])) {
            $specs['validation'] = '';
        }
        if (is_array($specs['validation'])) $specs['validation'] = serialize($specs['validation']);
        $data['xml'] .= '    <property name="' . $field . '">
      <id>' . $id . '</id>
      <label>' . $specs['label'] . '</label>
      <type>' . $specs['format'] . '</type>
      <default></default>
      <source>xar_sitecontact_response.xar_' . $field . '</source>
      <input>' . $specs['input'] . '</input>
      <status>' . $status . '</status>
      <validation>' . $specs['validation'] . '</validation>
    </property>
';
        // $specs['type'] = fixed for articles fields + unused in DD
        $id++;
    }
    // Retrieve any dynamic object for this formtype, or create a dummy one
    $object = xarModAPIFunc('dynamicdata','user','getobject',
                             array('name'     => $scform['sctypename'],
                                   'label'    => $scform['sctypedesc'],
                                   'moduleid' => xarModGetIDFromName('sitecontact'),
                                   'itemtype' => $scid,
                                   'urlparam' => 'scid',
                                   'isalias'  => 0));

    if (isset($object) && count($object->properties) > 0) {
        $proptypes = xarModAPIFunc('dynamicdata','user','getproptypes');
        $prefix = xarDBGetSystemTablePrefix();
        $prefix .= '_';
        $keys = array('id','label','type','default','source','status','order','validation');

        foreach (array_keys($object->properties) as $name) {
            $info = array();
            foreach ($keys as $key) {
                if (isset($object->properties[$name]->$key)) {
                    $info[$key] = $object->properties[$name]->$key;
                } else {
                    $info[$key] = '';
                }
            }
            // replace numeric property type with text version
            if (isset($proptypes[$info['type']])) {
                $info['type'] = $proptypes[$info['type']]['name'];
            }
            // replace local table prefix with default xar_* one
            $info['source'] = preg_replace("/^$prefix/",'xar_',$info['source']);
            if (is_array($info['validation'])) $info['validation'] = serialize($info['validation']);
            $data['xml'] .= '    <property name="' . $name . '">
      <id>' . $info['id'] . '</id>
      <label>' . $info['label'] . '</label>
      <type>' . $info['type'] . '</type>
      <default>' . $info['default'] . '</default>
      <source>' . $info['source'] . '</source>
      <status>' . $info['status'] . '</status>
      <order>' . $info['order'] . '</order>
      <validation>' . $info['validation'] . '</validation>
    </property>
';
        }
    }

    $data['xml'] .= "  </properties>
</object>\n";


    // Prepare the XML stuff for output in a textarea (for copy & paste)
    $data['xml'] = xarVarPrepForDisplay($data['xml']);

    // Return the template variables defined in this function
    return $data;
}

?>
