<?php

require_once __DIR__ . '/../class/user_external_access.class.php';

class ForgottenPasswordController extends Controller
{
	/**
	 * Store var for template
	 * @var stdClass
	 */
	public $tpl;

	public function __construct() {
		$this->accessNeedLoggedUser = false;
		$this->tpl = new stdClass();
	}

	/**
	 * check current access to controller
	 *
	 * @param void
	 * @return  bool
	 */
	public function checkAccess() {
		global $conf;
		$this->accessRight = !empty($conf->global->EACCESS_ACTIVATE_FORGOT_PASSWORD_FEATURE);
		return parent::checkAccess();
	}

	/**
	 * action method is called before html output
	 * can be used to manage security and change context
	 *
	 * @param void
	 * @return void
	 */
	public function action(){
		global $langs, $conf,$dolibarr_main_authentication, $db, $user;
		$context = Context::getInstance();

		$context->doNotDisplayHeaderBar = 1;
		$context->doNotDisplayMenu = 0;


		// Load translation files required by page
		$langs->loadLangs(array('errors', 'users', 'companies', 'ldap', 'other'));


		$this->username = GETPOST('username', 'alphanohtml');
		$this->passwordhash = GETPOST('passwordhash', 'alpha');

		$parameters = array('username' => $this->username);

		$hookRes = $this->hookDoAction($parameters);


		// init tpl vars;
		$this->tpl->message = '';

		$this->tpl->mode = $dolibarr_main_authentication;
		if (!$this->tpl->mode) $this->tpl->mode = 'http';

		// Security graphical code
		$this->tpl->captcha;
		if (function_exists("imagecreatefrompng")){
			$this->tpl->captcha = 1;
		}


		if(empty($hookRes)){
			$context->title = $langs->trans('Welcome');
			$context->desc = $langs->trans('WelcomeDesc');

			/*  */


			// Validate new password
			if ($context->action == 'validatenewpassword' && $this->username && $this->passwordhash)
			{
				$edituser = new UserExternalAccess($db);
				$result = $edituser->fetch('', $_GET["username"]);

				if ($result < 0)
				{
					$this->tpl->message.= '<div class="text-danger">'.dol_escape_htmltag($langs->trans("ErrorLoginDoesNotExists", $this->username)).'</div>';
				} else {
					if (dol_verifyHash($edituser->pass_temp, $this->passwordhash))
					{
						// Clear session
						unset($_SESSION['dol_login']);
						$_SESSION['dol_loginmesg'] = $langs->trans('NewPasswordValidated'); // Save message for the session page

						$newpassword = $edituser->setPassword($user, $edituser->pass_temp, 0);
						dol_syslog("passwordforgotten.php new password for user->id=".$edituser->id." validated in database");
						header("Location: ".$context->getRootUrl());
						exit;
					} else {
						$langs->load("errors");
						$this->tpl->message.= '<div class="text-danger">'.$langs->trans("ErrorFailedToValidatePasswordReset").'</div>';
					}
				}
			}
			// Action modif mot de passe
			if ($context->action == 'buildnewpassword' && $this->username)
			{
				$sessionkey = 'dolexternal_antispam_value';
				$ok = (array_key_exists($sessionkey, $_SESSION) === true && (strtolower($_SESSION[$sessionkey]) == strtolower($_POST['code'])));

				// Verify code
				if (!$ok)
				{
					$this->tpl->message.= '<div class="text-danger">'.$langs->trans("ErrorBadValueForCode").'</div>';
				}
				else
				{
					$isanemail = preg_match('/@/', $this->username);

					$edituser = new UserExternalAccess($db);
					$result = $edituser->fetch('', $this->username, '', 1);
					if ($result == 0 && $isanemail)
					{
						$result = $edituser->fetch('', '', '', 1, -1, $this->username);
					}

					$this->tpl->message.= '<div class="margin-top-if-not-empty">';
					if ($result <= 0 && $edituser->error == 'USERNOTFOUND')
					{
						$this->tpl->message.= '<div class="alert alert-info">';
						if (!$isanemail) {
							$this->tpl->message .= $langs->trans("IfLoginExistPasswordRequestSent");
						} else {
							$this->tpl->message .= $langs->trans("IfEmailExistPasswordRequestSent");
						}
						$this->tpl->message .= '</div>';
						$this->username = '';
					} else {
						if (!$edituser->email)
						{
							$this->tpl->message.= '<div class="alert alert-danger">'.$langs->trans("ErrorLoginHasNoEmail").'</div>';
						}
						else
						{
							$newpassword = $edituser->setPassword($user, '', 1);
							if ($newpassword < 0)
							{
								// Failed
								$this->tpl->message.= '<div class="alert alert-danger">'.$langs->trans("ErrorFailedToChangePassword").'</div>';
							} else {
								// Success
								if ($edituser->send_password($user, $newpassword, 1) > 0)
								{
									$this->tpl->message.= '<div class="alert alert-info">';
									if (!$isanemail) {
										$this->tpl->message.= $langs->trans("IfLoginExistPasswordRequestSent");
									} else {
										$this->tpl->message.= $langs->trans("IfEmailExistPasswordRequestSent");
									}
									$this->tpl->message.= '</div>';
									$this->username = '';
								} else {
									$this->tpl->message.= '<div class="text-danger">'.$edituser->error.'</div>';
								}
							}
						}
					}
					$this->tpl->message.= '</div>';
				}
			}
		}
	}


	/**
	 *
	 * @param void
	 * @return void
	 */
	public function display(){
		global $conf, $langs;

		$this->loadTemplate('header');

		$hookRes = $this->hookPrintPageView();

		if(empty($hookRes)){

			?>
			<header class="masthead text-center  d-flex">
				<div class="container my-auto">
					<?php
					$this->loadTemplate('form.forgotten_password');
					?>
				</div>
			</header>
			<?php

		}

		$this->loadTemplate('footer');
	}
}
