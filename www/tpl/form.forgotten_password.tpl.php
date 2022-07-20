<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}

$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

?>
<div class="container ">
	<div class="row">
		<div class="card card-container col-lg-6 ">
              <div class="panel-body">
                <div class="text-center">
                  <h3><i class="fa fa-lock fa-4x"></i></h3>
                  <h2 class="text-center"><?php echo $langs->trans("ForgotPassword"); ?></h2>
                  <p><?php echo $langs->trans("YouCanResetYourPasswordHere"); ?>.</p>
                  <div class="panel-body">

                    <form id="register-form" role="form" autocomplete="off" class="form" method="post"  action="<?php echo $context->getRootUrl('forgottenpassword'); ?>">

                        <input type="hidden" name="token" value="<?php echo $newToken; ?>">
                        <input type="hidden" name="action" value="buildnewpassword">

						<div class="form-group">
							<div class="input-group">
								<input type="text" placeholder="<?php echo $langs->trans("Login"); ?>" id="username" name="username" class="flat input-icon-user form-control" size="20" value="<?php echo dol_escape_htmltag($username); ?>" tabindex="1" />
								<div ><?php print $this->tpl->message; ?></div>
							</div>

						</div>

						<?php if ($this->tpl->captcha) :

							// Add a variable param to force not using cache (jmobile)
							$php_self = preg_replace('/[&\?]time=(\d+)/', '', $php_self); // Remove param time
							if (preg_match('/\?/', $php_self)) $php_self .= '&time='.dol_print_date(dol_now(), 'dayhourlog');
							else $php_self .= '?time='.dol_print_date(dol_now(), 'dayhourlog');
							// TODO: provide accessible captcha variants

							?>
							<!-- Captcha -->
							<div class="form-group form-inline">
								<div class="input-group">
									<input id="securitycode" placeholder="<?php echo $langs->trans("SecurityCode"); ?>" class="flat input-icon-user form-control" type="text" maxlength="5" name="code" tabindex="3" />
								</div>
								<div class="form-check">
									<i class="fa fa-arrow-left" ></i>
									<span class="nowrap inline-block">
										<img class="inline-block valignmiddle" src="<?php print $context->getRootUrl().'/script/script.php?action=antispamimage&time='.microtime(); ?>" border="0" width="80" height="32" id="img_securitycode" />
									</span>
								</div>
							</div>
						<?php endif; ?>



						<div class="form-group">
							<button name="recover-submit" class="btn btn-lg btn-primary btn-strong btn-block" type="submit"><?php echo $langs->trans("Renew"); ?></button>
							<br/><a href="<?php print $context->getRootUrl('login') ?>" ><i class="fa fa-arrow-left" ></i> <?php echo $langs->trans("GoBackToLoginPage"); ?></a>
						</div>

                    </form>

                  </div>
                </div>
            </div>

			<div class="text-center" >
				<?php if ($this->tpl->mode == 'dolibarr') { ?>
					<span class="passwordmessagedesc">
        				<?php echo $langs->trans('SendNewPasswordDesc'); ?>
        			</span>
				<?php }else{ ?>
					<div class="warning" align="center">
						<?php echo $langs->trans('AuthenticationDoesNotAllowSendNewPassword', $this->tpl->mode); ?>
					</div>
				<?php } ?>
			</div>
          </div>
	</div>
</div>
