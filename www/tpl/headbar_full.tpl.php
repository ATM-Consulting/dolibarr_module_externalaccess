<?php // Protection to avoid direct call of template

if (empty($context) || ! is_object($context))
{
	print "Error, template page can't be called as URL";
	exit;
}
global $langs;
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
            <a class="btn btn-strong btn-primary btn-xl js-scroll-trigger" href="#services-title"><i class="fa fa-arrow-right" aria-hidden="true"></i> <?php echo $langs->trans('MySpace'); ?> <i class="fa fa-arrow-left" aria-hidden="true"></i></a>
          </div>
          <?php } ?>
        </div>
      </div>
    </header>
