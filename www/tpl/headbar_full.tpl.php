  <?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
?>

    <header class="masthead text-center text-white d-flex">
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-10 mx-auto">
            <h1 class="text-uppercase">
              <strong><?php echo $context->title; ?></strong>
            </h1>
            <hr>
          </div>
          <?php if(!empty($context->desc)) { ?>
          <div class="col-lg-8 mx-auto">
            <p class="text-faded mb-5"><?php echo $context->desc; ?></p>
          </div>
          <?php } ?>
        </div>
      </div>
    </header>
   