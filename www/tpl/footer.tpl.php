<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
    print "Error, template page can't be called as URL";
    exit;
}

// Google Analytics (need Google module)
if (! empty($conf->google->enabled) && ! empty($conf->global->MAIN_GOOGLE_AN_ID))
{
	if (empty($conf->dol_use_jmobile))
	{
		print "\n";
		print '<script type="text/javascript">'."\n";
		print '  var _gaq = _gaq || [];'."\n";
		print '  _gaq.push([\'_setAccount\', \''.$conf->global->MAIN_GOOGLE_AN_ID.'\']);'."\n";
		print '  _gaq.push([\'_trackPageview\']);'."\n";
		print ''."\n";
		print '  (function() {'."\n";
		print '    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;'."\n";
		print '    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';'."\n";
		print '    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);'."\n";
		print '  })();'."\n";
		print '</script>'."\n";
	}
}
?>

    <!-- Bootstrap core JavaScript -->
    <script src="<?php print $context->getRootUrl(); ?>vendor/jquery/jquery.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="<?php print $context->getRootUrl(); ?>vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/scrollreveal/scrollreveal.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/magnific-popup/jquery.magnific-popup.min.js"></script>
    <script src="<?php print $context->getRootUrl(); ?>vendor/data-tables/datatables.min.js"></script>

    <!-- Custom scripts for this template -->
    <script src="<?php print $context->getRootUrl(); ?>js/creative.min.js"></script>

  </body>

</html>