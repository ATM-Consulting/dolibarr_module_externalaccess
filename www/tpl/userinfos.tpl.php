<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}
global $langs, $user, $conf;

$mode = 'readonly';
if($user->rights->externalaccess->edit_user_personal_infos  && ($context->action == 'edit' || $context->action == 'saveError')){
    $mode = 'edit';
}

print '<section id="section-personalinformations"  class="type-content"  ><div class="container">';
//var_dump($user);



if($user->rights->externalaccess->edit_user_personal_infos && $mode=='readonly'){
    print '<a class="btn btn-primary btn-strong pull-right btn-top-section" href="'.$context->getRootUrl('personalinformations').'&amp;action=edit"  ><i class="fa fa-pencil"></i> '.$langs->trans('exa_Edit').'</a>';
}

/*
print '<h3 class="text-center">'.$langs->trans('YourPersonnalInformations').'</h3>';
print '<hr/>';
print '<h6 class="text-center">'.$user->firstname .' '. $user->lastname.'</h6>';
*/

print '<h5 class="text-center text-primary">'.$langs->trans('YourPersonnalInformations').'</h5>';


if($context->action=='saved'){
    print '<div class="alert alert-success" role="alert">'.$langs->trans('Saved').'</div>';
}

if($context->action=='saveError'){
    print '<div class="alert alert-danger" role="alert">'.$langs->trans('ErrorDetected').'</div>';
}

print '<form method="post" action="'.$context->getRootUrl('personalinformations').'&amp;action=save">';
//print '<div class="card" >';
//


print '<div class="row">';

print '<div class="col-md-6"><div class="card"><div class="card-body">';

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



print '</div></div></div>';


print '<div class="col-md-6"><div class="card"><div class="card-body">';

if(!empty($user->socid)){
    $userSoc = new Societe($db);

    if($userSoc->fetch($user->socid) > 0){
        // Societe
        stdFormHelper('societe', $langs->trans('Company'), $userSoc->name, 'readonly', 1);
    }
}

// email
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('email' , $langs->trans('Email'), $user->email, $mode, 1, $param);

// User_mobile
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('user_mobile' , $langs->trans('User_mobile'), $user->user_mobile, $mode, 1, $param);

// office_phone
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('office_phone' , $langs->trans('Office_phone'), $user->office_phone, $mode, 1, $param);

// office_fax
$param = array('valid'=>0, 'feedback' => '');
stdFormHelper('office_fax' , $langs->trans('Office_fax'), $user->office_fax, $mode, 1, $param);

print '</div></div></div>';

print '</div>';
//var_dump($user);


if($mode=='edit'){

    print '<button class="btn btn-primary btn-strong pull-right" type="submit" name="save" value="1" >'.$langs->trans('Save').'</button>';

    print '<a class="btn btn-secondary btn-strong" href="'.$context->getRootUrl('personalinformations').'"  >'.$langs->trans('Cancel').'</a>';
}
else{
    print '<p>'.$conf->global->EACCESS_RGPD_MSG.'</p>';
}

//print '<!-- /card-body --></div>';
//print '<!-- /card --></div>';
print '</form>';


print '</div></section>';

