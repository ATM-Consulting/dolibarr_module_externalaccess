<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
$context = Context::getInstance();
?>
<div class="container">
	<div class="row  ">
        <div class="card card-container col-lg-6  ">
<?php
        // Default center logo is defined on admin panel, then if empty we use company default logo
        $urllogo = !empty($conf->global->EACCESS_LOGIN_IMG)?$conf->global->EACCESS_LOGIN_IMG:'';
        if($urllogo == '') {
            if ( (! empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
                || (! empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
                )
            {
                $urllogo=$context->getRootUrl().'/script/script.php?action=getlogo';
            }
        }

        if($urllogo != "") {
            print '<div class="text-center login-logo-container"><img alt="" src="'.$urllogo.'" id="img_login_logo" /></div>';
        }
		else
		{
		    //print '<img id="profile-img" class="profile-img-card" src="'.$context->rootUrl.'img/avatar.png" />';
		    $name = !empty($conf->global->EACCESS_TITLE)?$conf->global->EACCESS_TITLE:$conf->global->MAIN_INFO_SOCIETE_NOM;
		    print '<h4>'.$name.'</h4>';
		}


		print '<p id="profile-name" class="profile-name-card"></p>';

		// Show error message if defined
		if (! empty($_SESSION['dol_loginmesg']))
		{

            	print '<div class="alert alert-danger" role="alert">';
            	print $_SESSION['dol_loginmesg'];
            	print '</div>';

        }

?>


			<form class="form-signin" id="login" name="login" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <span id="reauth-email" class="reauth-email"></span>
                <input type="text"  name="username" id="inputUsername" class="form-control" placeholder="<?php echo $langs->trans("Login"); ?>" required autofocus>
                <input type="password" name="password" id="inputPassword" class="form-control" placeholder="<?php echo $langs->trans("Password"); ?>" required>
                <input type="hidden" name="urlfrom" value="<?php print Context::urlOrigin(); ?>"/>

                <?php if(!empty($conf->global->EACCESS_ACTIVATE_FORGOT_PASSWORD_FEATURE)){ ?>
                <div id="forgot-password" class="checkbox text-right">
                    <label>
                        <a href="<?php print $context->getRootUrl('forgottenpassword', '&action=forgot-password') ?>" ><?php print $langs->trans('AskForgotPassword'); ?></a>
                    </label>
                </div>
                <?php } ?>

                <button class="btn btn-lg btn-primary btn-strong btn-block btn-signin" type="submit"><?php print $langs->trans('SignIn'); ?></button>


                <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
                <input type="hidden" name="loginfunction" value="loginfunction" />
                <!-- Add fields to send local user information -->
                <input type="hidden" name="tz" id="tz" value="" />
                <input type="hidden" name="tz_string" id="tz_string" value="" />
                <input type="hidden" name="dst_observed" id="dst_observed" value="" />
                <input type="hidden" name="dst_first" id="dst_first" value="" />
                <input type="hidden" name="dst_second" id="dst_second" value="" />
                <input type="hidden" name="screenwidth" id="screenwidth" value="" />
                <input type="hidden" name="screenheight" id="screenheight" value="" />


                <?php
                /*<input type="hidden" name="dol_hide_topmenu" id="dol_hide_topmenu" value="<?php echo $dol_hide_topmenu; ?>" />
                 <input type="hidden" name="dol_hide_leftmenu" id="dol_hide_leftmenu" value="<?php echo $dol_hide_leftmenu; ?>" />
                 <input type="hidden" name="dol_optimize_smallscreen" id="dol_optimize_smallscreen" value="<?php echo $dol_optimize_smallscreen; ?>" />
                 <input type="hidden" name="dol_no_mouse_hover" id="dol_no_mouse_hover" value="<?php echo $dol_no_mouse_hover; ?>" />
                 <input type="hidden" name="dol_use_jmobile" id="dol_use_jmobile" value="<?php echo $dol_use_jmobile; ?>" />*/


                if (! empty($conf->global->MAIN_SECURITY_ENABLECAPTCHA)) {
                	// Add a variable param to force not using cache (jmobile)
                	$php_self = preg_replace('/[&\?]time=(\d+)/','',$php_self);	// Remove param time
                	if (preg_match('/\?/',$php_self)) $php_self.='&time='.dol_print_date(dol_now(),'dayhourlog');
                	else $php_self.='?time='.dol_print_date(dol_now(),'dayhourlog');
                	// TODO: provide accessible captcha variants
                ?>
                	<!-- Captcha -->

                	<span class="span-icon-security">
                	<input id="securitycode" placeholder="<?php echo $langs->trans("SecurityCode"); ?>" class="flat input-icon-security" type="text" size="12" maxlength="5" name="code" tabindex="3" />
                	</span>
                	<img src="<?php echo DOL_URL_ROOT ?>/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" />
                	<a href="<?php echo $php_self; ?>" tabindex="4" data-role="button"><?php echo $captcha_refresh; ?></a>

                <?php }  ?>

            </form><!-- /form -->
            <?php /*if(!$conf->global->MAIN_SECURITY_DISABLEFORGETPASSLINK){ ?>
            <a href="#forgottenpassword" class="forgot-password">
                <?php print $langs->trans('ForgotThePassword'); ?>
            </a>
        	<?php }*/ ?>
        </div><!-- /card-container -->
   </div>

<?php
if(!empty($conf->global->EACCESS_LOGIN_EXTRA_HTML)){
    print '<div class="row  "><div class="card card-container col-lg-6 ">';
    print $conf->global->EACCESS_LOGIN_EXTRA_HTML;
    print '</div></div>';

}


?>

</div><!-- /container -->
