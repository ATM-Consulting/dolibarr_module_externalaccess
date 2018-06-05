<?php // Protection to avoid direct call of template
if (empty($conf) || ! is_object($conf))
{
	print "Error, template page can't be called as URL";
	exit;
}
?>
<div class="container ">
	<div class="row">
		<div class="card card-container col-lg-4 ">
              <div class="panel-body">
                <div class="text-center">
                  <h3><i class="fa fa-lock fa-4x"></i></h3>
                  <h2 class="text-center"><?php echo $langs->trans("ForgotPassword"); ?></h2>
                  <p><?php echo $langs->trans("YouCanResetYourPasswordHere"); ?>.</p>
                  <div class="panel-body">
    
                    <form id="register-form" role="form" autocomplete="off" class="form" method="post"  action="<?php echo $_SERVER['PHP_SELF']; ?>#forgottenpassword">
    
                        <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">
                        <input type="hidden" name="action" value="buildnewpassword">
    
                      <div class="form-group">
                        <div class="input-group">
                          <span class="input-group-addon"><i class="glyphicon glyphicon-envelope color-blue"></i></span>
                          <input type="text" placeholder="<?php echo $langs->trans("Login"); ?>" <?php echo $disabled; ?> id="username" name="username" class="flat input-icon-user form-control" size="20" value="<?php echo dol_escape_htmltag($username); ?>" tabindex="1" />
                        </div>
                      </div>
                      <div class="form-group">
                        <button name="recover-submit" class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $langs->trans("Renew"); ?></button>
                      </div>
                      
                    </form>

                  </div>
                </div>
            </div>
          </div>
	</div>

	<div class="text-center" >
        <?php if ($mode == 'dolibarr' || ! $disabled) { ?>
        	<span class="passwordmessagedesc">
        	<?php echo $langs->trans('SendNewPasswordDesc'); ?>
        	</span>
        <?php }else{ ?>
        	<div class="warning" align="center">
        	<?php echo $langs->trans('AuthenticationDoesNotAllowSendNewPassword', $mode); ?>
        	</div>
        <?php } ?>
	</div>
</div>