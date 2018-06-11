<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
    print "Error, template page can't be called as URL";
    exit;
    // Note: use fontawesome v4.7.0 : https://fontawesome.com/v4.7.0/
}

global $langs;
?>

	 <section id="contact" class="bg-dark text-white">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 mx-auto text-center">
            <h2 class="section-heading"><?php echo $langs->trans('GetInTouch'); ?></h2>
            <hr class="my-4">
            <p class="mb-5"><?php echo $langs->trans('GetInTouchDesc'); ?></p>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-4 ml-auto text-center">
            <i class="fa fa-phone fa-3x mb-3 sr-contact"></i>
            <p><a href="tel:<?php print preg_replace("/[^0-9\+]/", "", $conf->global->EACCESS_PHONE); ?>"><?php print $conf->global->EACCESS_PHONE; ?></a></p>
          </div>
          <div class="col-lg-4 mr-auto text-center">
            <i class="fa fa-envelope-o fa-3x mb-3 sr-contact"></i>
            <p>
              <a href="mailto:<?php print $conf->global->EACCESS_EMAIL; ?>"><?php print $conf->global->EACCESS_EMAIL; ?></a>
            </p>
          </div>
        </div>
      </div>
    </section>