<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}
global $langs, $user;


print '<section id="section-personalinformations"><div class="container">';
//var_dump($user);

$mode = 'readonly';


print '<form>';

// Firstname
$param = array('required'=> true, 'valid'=>0, 'feedback' => '');
stdFormHelper('firstname', $langs->trans('firstname'), $user->firstname, $mode, 1, $param);

// LastName
$param = array('required'=> true, 'valid'=>0, 'feedback' => '');
stdFormHelper('lastname' , $langs->trans('lastname'), $user->lastname, $mode, 1, $param);

// Address
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('address' , $langs->trans('address'), $user->address, $mode, 1, $param);

// zip
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('addresszip' , $langs->trans('addresszip'), $user->zip, $mode, 1, $param);

// town
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('town' , $langs->trans('town'), $user->town, $mode, 1, $param);


  
print '</form>';


print '</div></section>';

