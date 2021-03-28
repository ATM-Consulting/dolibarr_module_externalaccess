<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
/** @var HookManager $hookmanager */

$metaTitle = '';
if (!empty($context->meta_title)) { $metaTitle = $context->meta_title; }
elseif (!empty($context->title)){ $metaTitle = $context->title; }
if (!empty($metaTitle)) { $metaTitle.= ' - '; }
$metaTitle.= !empty($conf->global->EACCESS_TITLE)?$conf->global->EACCESS_TITLE:$conf->global->MAIN_INFO_SOCIETE_NOM;



?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo dol_htmlentities($context->meta_desc, ENT_QUOTES); ?>">
    <meta name="author" content="<?php echo dol_htmlentities($conf->global->MAIN_INFO_SOCIETE_NOM, ENT_QUOTES); ?>">

    <title><?php echo dol_htmlentities($metaTitle); ?></title>

    <!-- Bootstrap core CSS -->
    <link href="<?php print $context->getRootUrl(); ?>vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Custom fonts for this template -->
    <link href="<?php print $context->getRootUrl(); ?>vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>


    <!-- Plugin CSS -->
    <link href="<?php print $context->getRootUrl(); ?>vendor/magnific-popup/magnific-popup.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php print $context->getRootUrl(); ?>css/creative.css.php" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="<?php print $context->getRootUrl(); ?>vendor/data-tables/datatables.min.css"/>


    <!-- Bootstrap core JavaScript -->
    <script src="<?php print $context->getRootUrl(); ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

	<!-- bootbox code -->
	<script src="<?php print $context->getRootUrl(); ?>vendor/bootbox/bootbox.all.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="<?php print $context->getRootUrl(); ?>vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/scrollreveal/scrollreveal.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/magnific-popup/jquery.magnific-popup.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/datatables.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/jquery.dataTables.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/Buttons-1.5.1/js/buttons.print.js"></script>

    <!-- Plugin Notify -->
    <script src="<?php print $context->getRootUrl(); ?>vendor/noty/noty.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php print $context->getRootUrl(); ?>vendor/noty/noty.css"/>
    <link rel="stylesheet" type="text/css" href="<?php print $context->getRootUrl(); ?>vendor/noty/themes/metroui.css"/>

    <!-- Plugin Select -->
    <script src="<?php print $context->getRootUrl(); ?>vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php print $context->getRootUrl(); ?>vendor/bootstrap-select/dist/css/bootstrap-select.min.css"/>

    <!-- Custom scripts for this template -->
    <script src="<?php print $context->getRootUrl(); ?>js/creative.js"></script>

	<!-- Custom include ckeditor -->
	<script src="<?php print $context->getRootUrl(); ?>vendor/ckeditor/ckeditor.js"></script>

<?php

	$favicon = $conf->global->EACCESS_FAVICON_URL;
	if (empty($favicon) && ! empty($conf->global->MAIN_FAVICON_URL)){
		$favicon=$conf->global->MAIN_FAVICON_URL;
	}

    if (! empty($favicon)){
        print '	<link rel="icon" type="image/png" href="'.$favicon.'">' . "\r\n";
    }

    // Mobile appli like icon
    print '	<link rel="manifest" href="'.$context->getRootUrl().'manifest.json.php'.'" />' . "\r\n";

	$primaryColor = !empty($conf->global->EACCESS_PRIMARY_COLOR)?$conf->global->EACCESS_PRIMARY_COLOR:'#F05F40';
	$backgroundColor = !empty($conf->global->EACCESS_APPLI_COLOR)?$conf->global->EACCESS_APPLI_COLOR:$primaryColor;
	print '	<meta name="theme-color" content="'.$backgroundColor.'">' . "\r\n";


 	print "\r\n" . ' 	<!-- Custom head from hooks -->' . "\r\n";
	$parameters=array();
	$reshook=$hookmanager->executeHooks('externalAccessAddHtmlHeader',$parameters,$context, $context->action);    // same as Dolibarr addHtmlHeader hook but avoid missconfigured module to execute this hook
	print $hookmanager->resPrint;


?>
  </head>

  <body id="page-top" class="<?php print $context->iframe?'iframe':''; ?>" >

  <?php

  if(empty($context->doNotDisplayMenu) && empty($context->iframe))
  {
      include __DIR__ . '/menu.tpl.php';
  }

  if(empty($context->doNotDisplayHeaderBar) && empty($context->iframe))
  {
      include __DIR__ . '/headbar.tpl.php';
  }
