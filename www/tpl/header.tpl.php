<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo empty($context->meta_title)?$context->title:$context->meta_title;?> - <?php echo !empty($conf->global->EACCESS_TITLE)?$conf->global->EACCESS_TITLE:$conf->global->MAIN_INFO_SOCIETE_NOM; ?></title>

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

    <!-- Plugin JavaScript -->
    <script src="<?php print $context->getRootUrl(); ?>vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/scrollreveal/scrollreveal.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/magnific-popup/jquery.magnific-popup.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/datatables.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/jquery.dataTables.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/dataTables.bootstrap4.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/Buttons-1.5.1/js/buttons.print.js"></script>

    <!-- Custom scripts for this template -->
    <script src="<?php print $context->getRootUrl(); ?>js/creative.js"></script>
 
 <?php 
    if (! empty($conf->global->MAIN_FAVICON_URL)){
        $favicon=$conf->global->MAIN_FAVICON_URL;
        print '<link rel="icon" type="image/png" href="'.$favicon.'">';
    }
 ?>


  </head>

  <body id="page-top">

  <?php 
  
  if(empty($context->doNotDisplayMenu))
  {
      include __DIR__ . '/menu.tpl.php';
  }
  
  if(empty($context->doNotDisplayHeaderBar)) 
  {
      include __DIR__ . '/headbar.tpl.php'; 
  }
  