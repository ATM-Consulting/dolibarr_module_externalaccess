<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}



global $conf,$user,$langs, $hookmanager;

$Tmenu=$TGroupMenu=array();

$maxTopMenu = getDolGlobalString('EACCESS_MAX_TOP_MENU',0);

if ($context->userIsLog())
{

	// TODO : check if is it possible to use checkAccess() of controller
	$Tmenu['services'] = array(
		'id' => 'services',
		'rank' => 70,
		'url' => $_SERVER['PHP_SELF'] . '#services',
		'name' => $langs->trans('MyServices'),
		'group' => 'technical' // group identifier for the group if necessary
	);

    $Tmenu['user'] = array(
        'id' => 'user',
        'rank' => 100,
        'url' => '',
        'name' => '<i class="fa fa-user"></i> ' . $user->login,
    );

    $Tmenu['user']['children']['personalinformations'] = array(
        'id' => 'personalinformations',
        'rank' => 10,
        'url' => $context->getControllerUrl('personalinformations'),
        'name' => '<i class="fa fa-user"></i> '.$langs->trans('PersonalInformations'),
    );

    $Tmenu['user']['children']['logout'] = array(
        'id' => 'logout',
        'separator' => 1,
        'rank' => 100,
        'url' => $context->getControllerUrl().'logout.php',
        'name' => '<i class="fa fa-sign-out"></i> '.$langs->trans('Logout'),
    );
}


if (getDolGlobalString('EACCESS_GOBACK_URL')){
    $Tmenu['gobackurl'] = array(
        'id' => 'gobackurl',
        'rank' => 90,
        'url' => getDolGlobalString('EACCESS_GOBACK_URL'),
        'name' => '<i class="fa fa-external-link"></i> '.$langs->trans('EALINKNAME_gobackurl'),
    );
}


// GROUP MENU
$TGroupMenu = array(
	'administrative' => array(
		'id' => 'administrative',
		'rank' => -1, // negative value for undefined, it will be set by the min item rank for this group
		'url' => '',
		'name' => $langs->trans('GroupMenuAdministrative'),
		'children' => array()
	),
	'technical' => array(
		'id' => 'technical',
		'rank' => -1, // negative value for undefined, it will be set by the min item rank for this group
		'url' => '',
		'name' => $langs->trans('GroupMenuTechnical'),
		'children' => array()
	),
);

$parameters=array(
    'controller' => $context->controller,
    'Tmenu' =>& $Tmenu,
    'TGroupMenu' =>& $TGroupMenu,
	'maxTopMenu' =>& $maxTopMenu
);

$reshook=$hookmanager->executeHooks('PrintTopMenu', $parameters, $context, $context->action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)){
    if (!empty($hookmanager->resArray)){
        $Tmenu = array_replace($Tmenu, $hookmanager->resArray);
    }

    if (!empty($Tmenu)){
		// Sorting
		uasort($Tmenu, 'menuSortInv');

		if (!empty($maxTopMenu) && $maxTopMenu < count($Tmenu)){
			// AFFECT MENU ITEMS TO GROUPS
			foreach ($Tmenu as $menuId => $menuItem){
				// affectation des items de menu au groupement
				if (!empty($menuItem['group']) && !empty($TGroupMenu[$menuItem['group']])){
					$goupId = $menuItem['group'];

					// Affectation de l'item au groupe
					$TGroupMenu[$goupId]['children'][$menuId] = $menuItem;

					// Application du rang
					if (!empty($TGroupMenu[$goupId]['rank']) && $TGroupMenu[$goupId]['rank']>0){
						// le rang mini des items du groupe défini le rang du groupe
						$TGroupMenu[$goupId]['rank'] = min(abs($TGroupMenu[$goupId]['rank']), abs($menuItem['rank']));
					}
				}
			}

			// INSERTION DES GROUPES DANS LE MENU
			foreach ($TGroupMenu as $groupId => $groupItem){
				// If group have more than 1 item, group is valid
				if (!empty($groupItem['children']) && count($groupItem['children']) > 1){
					// ajout du group au menu
					$Tmenu[$groupId] = $groupItem;

					// suppression des items enfant du group du menu
					foreach ($groupItem['children'] as $menuId => $menuItem){
						if (isset($Tmenu[$menuId])){ unset($Tmenu[$menuId]); }
					}
				}
			}

			// final sorting
			uasort($Tmenu, 'menuSortInv');
		}

		?>
<!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top <?php print !empty($context->topMenu->shrink)?'navbar-shrink':''; ?>" id="mainNav" <?php print !empty($context->topMenu->shrink)?'data-defaultshrink="1"':''; ?> >
      <div class="container">
        <a class="navbar-brand js-scroll-trigger" href="<?php print $context->getControllerUrl();  ?>">
			<?php
			$brandTitle = getDolGlobalString('EACCESS_TITLE',getDolGlobalString('MAIN_INFO_SOCIETE_NOM'));
			if (getDolGlobalString('EACCESS_TOP_MENU_IMG')){
				print '<img class="logo" id="logo" title="'.htmlentities($brandTitle, ENT_QUOTES).'" src="' . getDolGlobalString('EACCESS_TOP_MENU_IMG') . '" />';
				print '<img class="logo" id="logoshrink" title="'.htmlentities($brandTitle, ENT_QUOTES).'" src="' . (getDolGlobalString('EACCESS_TOP_MENU_IMG_SHRINK',getDolGlobalString('EACCESS_TOP_MENU_IMG'))) . '" />';
			}
			else {
				print $brandTitle;
			}
			?>
		</a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
          <ul class="navbar-nav ml-auto">
            <?php
                print getNav($Tmenu);
            ?>
          </ul>
        </div>
      </div>
    </nav>

		<?php
    }
}
