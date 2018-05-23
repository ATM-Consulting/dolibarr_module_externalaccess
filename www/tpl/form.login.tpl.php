<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
?>
<div class="container">
	<div class="row  ">
        <div class="card card-container col-lg-6  ">
            
            <img id="profile-img" class="profile-img-card" src="<?php print $context->rootUrl ?>img/avatar.png" />
            <p id="profile-name" class="profile-name-card"></p>
			<form class="form-signin" id="login" name="login" method="post" action="<?php echo $php_self; ?>">
                <span id="reauth-email" class="reauth-email"></span>
                <input type="text"  name="username" id="inputUsername" class="form-control" placeholder="<?php echo $langs->trans("Login"); ?>" required autofocus>
                <input type="password" name="password" id="inputPassword" class="form-control" placeholder="<?php echo $langs->trans("Password"); ?>" required>
                 
                 
                <div id="remember" class="checkbox">
                    <label>
                        <input type="checkbox" value="remember-me"> <?php print $langs->trans('Rememberme'); ?>
                    </label>
                </div>
                
                <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit"><?php print $langs->trans('SignIn'); ?></button>
            
                            
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
                <input type="hidden" name="dol_hide_topmenu" id="dol_hide_topmenu" value="<?php echo $dol_hide_topmenu; ?>" />
                <input type="hidden" name="dol_hide_leftmenu" id="dol_hide_leftmenu" value="<?php echo $dol_hide_leftmenu; ?>" />
                <input type="hidden" name="dol_optimize_smallscreen" id="dol_optimize_smallscreen" value="<?php echo $dol_optimize_smallscreen; ?>" />
                <input type="hidden" name="dol_no_mouse_hover" id="dol_no_mouse_hover" value="<?php echo $dol_no_mouse_hover; ?>" />
                <input type="hidden" name="dol_use_jmobile" id="dol_use_jmobile" value="<?php echo $dol_use_jmobile; ?>" />
                 
                <?php         
                /*
                if ($captcha) {
                	// Add a variable param to force not using cache (jmobile)
                	$php_self = preg_replace('/[&\?]time=(\d+)/','',$php_self);	// Remove param time
                	if (preg_match('/\?/',$php_self)) $php_self.='&time='.dol_print_date(dol_now(),'dayhourlog');
                	else $php_self.='?time='.dol_print_date(dol_now(),'dayhourlog');
                	// TODO: provide accessible captcha variants
                ?>
                	<!-- Captcha -->
                	<tr>
                	<td class="nowrap none center">
                
                	<table class="login_table_securitycode centpercent"><tr>
                	<td>
                	<span class="span-icon-security">
                	<input id="securitycode" placeholder="<?php echo $langs->trans("SecurityCode"); ?>" class="flat input-icon-security" type="text" size="12" maxlength="5" name="code" tabindex="3" />
                	</span>
                	</td>
                	<td><img src="<?php echo DOL_URL_ROOT ?>/core/antispamimage.php" border="0" width="80" height="32" id="img_securitycode" /></td>
                	<td><a href="<?php echo $php_self; ?>" tabindex="4" data-role="button"><?php echo $captcha_refresh; ?></a></td>
                	</tr></table>
                
                	</td></tr>
                <?php } */ ?>            
            
            </form><!-- /form -->
            <a href="#" class="forgot-password">
                <?php print $langs->trans('ForgotThePassword'); ?>
            </a>
        </div><!-- /card-container -->
   </div>
</div><!-- /container -->