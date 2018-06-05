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

    <title><?php echo $context->meta_title?$context->title:$context->meta_title; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="<?php ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    

    <!-- Plugin CSS -->
    <link href="vendor/magnific-popup/magnific-popup.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/creative.css.php" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="<?php print $context->getRootUrl(); ?>vendor/data-tables/datatables.min.css"/>
 


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
  
  
  
  
  