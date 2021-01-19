<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
    print "Error, template page can't be called as URL";
    exit;
}
global $langs;

if(empty($context->iframe)) {
	include __DIR__ . '/get_in_touch.tpl.php';
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


$context->loadEventMessages();
print '<script type="text/javascript">'."\n"; // 'mesgs', 'warnings', 'errors'

if(!empty($context->eventMessages['mesgs'])){
    foreach ($context->eventMessages['mesgs'] as $mesg)
    {
		print 'new Noty({
		    timeout: 3000,
		    type: "success",
		    closeWith: [\'button\',\'click\'],
            theme: "metroui",
            text: "'.addslashes(preg_replace("/\r|\n/", "", nl2br($mesg))).'"
        }).show();';
    }
}

if(!empty($context->eventMessages['warnings'])){
	foreach ($context->eventMessages['warnings'] as $mesg)
	{
		print 'new Noty({
		    timeout: 5000,
		    type: "warning",
		    closeWith: [\'button\'],
            theme: "metroui",
            text: "'.addslashes(preg_replace("/\r|\n/", "", nl2br($mesg))).'"
        }).show();';
	}
}

if(!empty($context->eventMessages['errors'])){
	foreach ($context->eventMessages['errors'] as $mesg)
	{
		print 'new Noty({
		    timeout: 7000,
		    closeWith: [\'button\'],
		    type: "error",
            theme: "metroui",
            text: "'.addslashes(preg_replace("/\r|\n/", "", nl2br($mesg))).'"
        }).show();';
	}
}



print '</script>'."\n";

$context->clearEventMessages();


if($context->getErrors()) 
{
	include __DIR__ . '/errors.tpl.php'; 
}
?>

    <div class="clearboth" ></div>
  </body>

</html>