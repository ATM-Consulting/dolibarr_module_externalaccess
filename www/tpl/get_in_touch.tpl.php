<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
    print "Error, template page can't be called as URL";
    exit;
    // Note: use fontawesome v4.7.0 : https://fontawesome.com/v4.7.0/
}

global $langs, $user;

$signature = '';
if(!empty($user->socid) && !empty($conf->global->EACCESS_ADD_INFOS_COMMERCIAL_BAS_DE_PAGE)) {

	require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
	$soc = new Societe($db);
	$soc->fetch($user->socid);
	$TSalesRep = $soc->getSalesRepresentatives($user);
	if(!empty($TSalesRep)) {

		$signature = $langs->trans('YourSalesRep', $conf->global->MAIN_INFO_SOCIETE_NOM).'<br />';
		if(count($TSalesRep) > 1) $signature = $langs->trans('YourSalesRepMultiple', $conf->global->MAIN_INFO_SOCIETE_NOM).'<br />';

		foreach ($TSalesRep as $TData) {
			$u = new User($db);
			$u->fetch($TData['id']);
			$signature.= nl2br(strip_tags($u->signature)).'<br /><br />';
		}

	}

}

?>

	 <section id="contact" class="bg-dark text-white">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            <h2 class="section-heading"><?php echo $langs->trans('GetInTouch'); ?></h2>
            <hr class="my-4">
            <p class="mb-5">
				<?php
					if (!empty($signature)) print $signature;
					echo $langs->trans('GetInTouchDesc');
				?>
			</p>
          </div>
        </div>
        <div class="row">
<?php if(!empty($conf->global->EACCESS_PHONE)): ?>
          <div class="col-lg-4 ml-auto text-center">
            <i class="fa fa-phone fa-3x mb-3 sr-contact"></i>
            <p><a href="tel:<?php print preg_replace("/[^0-9\+]/", "", $conf->global->EACCESS_PHONE); ?>"><?php print $conf->global->EACCESS_PHONE; ?></a></p>
          </div>
<?php endif; ?>

<?php if(!empty($conf->global->EACCESS_EMAIL)):
	$link = $conf->global->EACCESS_EMAIL;
	$linkName = $langs->trans("ContactUs");
	if(filter_var($conf->global->EACCESS_EMAIL, FILTER_VALIDATE_EMAIL)){
		$link = "mailto:".$conf->global->EACCESS_EMAIL;
		$linkName = $conf->global->EACCESS_EMAIL;
	}
?>
          <div class="col-lg-4 mr-auto text-center">
            <i class="fa fa-envelope-o fa-3x mb-3 sr-contact"></i>
            <p>
              <a href="<?php print $link; ?>"><?php print $linkName; ?></a>
            </p>
          </div>
<?php endif; ?>
        </div>
      </div>
    </section>
