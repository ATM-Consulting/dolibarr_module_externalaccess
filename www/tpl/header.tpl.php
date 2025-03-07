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
$metaTitle.= getDolGlobalString('EACCESS_TITLE',getDolGlobalString('MAIN_INFO_SOCIETE_NOM'));

$canonicalUrl = '';
$curentUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$curentUrlParsed = parse_url($curentUrl);
if (isset($curentUrlParsed['query'])) {
	parse_str($curentUrlParsed['query'], $curentUrlParams);
	if (isset($context->tokenKey) && isset($curentUrlParams[$context->tokenKey])) {
		unset($curentUrlParams[$context->tokenKey]);
		$newUrlQuery = http_build_query($curentUrlParams);
		$canonicalUrl = $curentUrlParsed['scheme'] . '://' . $curentUrlParsed['host'] . $curentUrlParsed['path'] . (!empty($newUrlQuery)?'?'.$newUrlQuery:'');
	}
}


?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo dol_htmlentities($context->meta_desc, ENT_QUOTES); ?>">
    <meta name="author" content="<?php echo dol_htmlentities(getDolGlobalString('MAIN_INFO_SOCIETE_NOM'), ENT_QUOTES); ?>">
    <?php if(!empty($canonicalUrl)) print '<link rel="canonical" href="'.dol_escape_htmltag($canonicalUrl).'" />';  ?>
	<title><?php echo dol_htmlentities($metaTitle); ?></title>

    <!-- Bootstrap core CSS -->
    <link href="<?php print $context->getControllerUrl(); ?>vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Custom fonts for this template -->
    <link href="<?php print $context->getControllerUrl(); ?>vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>


    <!-- Plugin CSS -->
    <link href="<?php print $context->getControllerUrl(); ?>vendor/magnific-popup/magnific-popup.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php print $context->getControllerUrl(); ?>css/creative.css.php" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="<?php print $context->getControllerUrl(); ?>vendor/data-tables/datatables.min.css"/>


    <!-- Bootstrap core JavaScript -->
    <script src="<?php print $context->getControllerUrl(); ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?php print $context->getControllerUrl(); ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

	<!-- bootbox code -->
	<script src="<?php print $context->getControllerUrl(); ?>vendor/bootbox/bootbox.all.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="<?php print $context->getControllerUrl(); ?>vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php print $context->getControllerUrl(); ?>vendor/scrollreveal/scrollreveal.min.js"></script>
    <script src="<?php print $context->getControllerUrl(); ?>vendor/magnific-popup/jquery.magnific-popup.min.js"></script>
    <script src="<?php print $context->getControllerUrl(); ?>vendor/data-tables/datatables.min.js"></script>
    <script src="<?php print $context->getControllerUrl(); ?>vendor/data-tables/jquery.dataTables.min.js"></script>
    <script src="<?php print $context->getControllerUrl(); ?>vendor/data-tables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php print $context->getControllerUrl(); ?>vendor/data-tables/Buttons-1.5.1/js/buttons.print.js"></script>

    <!-- Plugin Notify -->
    <script src="<?php print $context->getControllerUrl(); ?>vendor/noty/noty.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php print $context->getControllerUrl(); ?>vendor/noty/noty.css"/>
    <link rel="stylesheet" type="text/css" href="<?php print $context->getControllerUrl(); ?>vendor/noty/themes/metroui.css"/>

    <!-- Plugin Select -->
    <script src="<?php print $context->getControllerUrl(); ?>vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php print $context->getControllerUrl(); ?>vendor/bootstrap-select/dist/css/bootstrap-select.min.css"/>

    <!-- Custom scripts for this template -->
    <script src="<?php print $context->getControllerUrl(); ?>js/creative.js"></script>

	<!-- Custom include ckeditor -->
	<script src="<?php print $context->getControllerUrl(); ?>vendor/ckeditor/ckeditor.js"></script>

<?php

    if (isset($conf->global->EACCESS_FAVICON_URL)) $favicon = getDolGlobalString('EACCESS_FAVICON_URL');

	if (empty($favicon) && getDolGlobalString('MAIN_FAVICON_URL')){
		$favicon=getDolGlobalString('MAIN_FAVICON_URL');
	}

    if (! empty($favicon)){
        print '	<link rel="icon" type="image/png" href="'.$favicon.'">' . "\r\n";
    }

    // Mobile appli like icon
    print '	<link rel="manifest" href="'.$context->getControllerUrl().'manifest.json.php'.'" />' . "\r\n";

	$primaryColor = '#' . getDolGlobalString('EACCESS_PRIMARY_COLOR','F05F40');
    $backgroundColor = (!empty(getDolGlobalString('EACCESS_APPLI_COLOR'))) ? '#' . getDolGlobalString('EACCESS_APPLI_COLOR') : $primaryColor;
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
