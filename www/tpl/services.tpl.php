<?php // Protection to avoid direct call of template
if (empty($context) || ! is_object($context))
{
    print "Error, template page can't be called as URL";
    exit;
    // Note: use fontawesome v4.7.0 : https://fontawesome.com/v4.7.0/
}

global $langs;
?>

	<section id="services">
      <div class="container">
        <div class="row">
          <div class="col-lg-12 text-center">
            <h2 class="section-heading"><?php print $langs->trans('Services');  ?></h2>
            <hr class="my-4">
          </div>
        </div>
      </div> 
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-6 text-center">
            <div class="service-box mt-5 mx-auto">
              <i class="fa fa-4x fa-pencil text-primary mb-3 sr-icons"></i>
              <h3 class="mb-3"><?php  print $langs->trans('Quotations') ?></h3>
              <p class="text-muted mb-0"><?php  print $langs->trans('QuotationsDesc') ?></p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 text-center">
            <div class="service-box mt-5 mx-auto">
              <i class="fa fa-4x fa-file-text-o text-primary mb-3 sr-icons"></i>
              <h3 class="mb-3"><?php  print $langs->trans('Orders') ?></h3>
              <p class="text-muted mb-0"><?php  print $langs->trans('OrdersDesc') ?></p>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 text-center">
            <div class="service-box mt-5 mx-auto">
              <i class="fa fa-4x fa-file-text text-primary mb-3 sr-icons"></i>
              <h3 class="mb-3"><?php  print $langs->trans('Invoices') ?></h3>
              <p class="text-muted mb-0"><?php  print $langs->trans('InvoicesDesc') ?></p>
            </div>
          </div>
          
        </div>
      </div>
    </section>