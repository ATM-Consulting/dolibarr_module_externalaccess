<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}
global $langs, $user;


print '<section id="section-personalinformations" ><div class="container">';
//var_dump($user);



if($context->action=='saved'){
    print '<div class="alert alert-success" role="alert">'.$langs->trans('Saved').'</div>';
}

if($context->action=='saveError'){
    print '<div class="alert alert-danger" role="alert">'.$langs->trans('ErrorDetected').'</div>';
}

$mode = 'readonly';
if($user->rights->externalaccess->edit_user_personal_infos  && ($context->action == 'edit' || $context->action == 'saveError')){
    $mode = 'edit';
}

if($user->rights->externalaccess->edit_user_personal_infos && $mode=='readonly'){
    print '<a class="btn btn-primary pull-right" href="'.$context->getRootUrl('personalinformations').'&amp;action=edit"  ><i class="fa fa-pencil"></i> '.$langs->trans('exa_Edit').'</a>';
}
print '<h5 class="card-title">'.$langs->trans('YourPersonnalInformations').'</h5>';


print '<form method="post" action="'.$context->getRootUrl('personalinformations').'&amp;action=save">';
//print '<div class="card" >';
//

//print '<div class="card-body" >';
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

// email
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('email' , $langs->trans('email'), $user->email, $mode, 1, $param);

// User_mobile
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('user_mobile' , $langs->trans('User_mobile'), $user->user_mobile, $mode, 1, $param);

// office_phone
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('office_phone' , $langs->trans('Office_phone'), $user->office_phone, $mode, 1, $param);

// office_fax
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('office_fax' , $langs->trans('Office_fax'), $user->office_fax, $mode, 1, $param);


//var_dump($user);


if($mode=='edit'){
    
    print '<button class="btn btn-primary pull-right" type="submit" name="save" value="1" >'.$langs->trans('Save').'</button>';
    
    print '<a class="btn btn-secondary" href="'.$context->getRootUrl('personalinformations').'"  >'.$langs->trans('Cancel').'</a>';
}

//print '<!-- /card-body --></div>';
//print '<!-- /card --></div>';
print '</form>';


print '</div></section>';

