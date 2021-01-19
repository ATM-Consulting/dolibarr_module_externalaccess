<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}
?>
<!-- Navigation left -->

<ul class="nav flex-column">
<?php 

if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES))
{
    $active = $context->menuIsActive('invoices')?'active':'';
    print ' <li class="nav-item"><a href="'.$context->rootUrl.'" class="nav-link '.$active.'">'. $langs->trans('Invoices').'</a></li>';
}

?>
</ul>

