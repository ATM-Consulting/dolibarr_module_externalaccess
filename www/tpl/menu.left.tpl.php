<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
?>
<!-- Navigation left -->

<div class="list-group">
<?php 

if(!empty($conf->global->EACCESS_ACTIVATE_INVOICES))
{
    $active = $context->menuIsActive('invoices')?'active':'';
    print '<a href="'.$context->rootUrl.'" class="list-group-item '.$active.'">'. $langs->trans('Invoices').'</a>';
}

?>
</div>

