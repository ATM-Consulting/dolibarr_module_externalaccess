<?php

require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';

/**
 *	Class to manage Dolibarr users
 */
class UserExternalAccess extends User
{


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Send new password by email
	 *
	 *  @param	User	$user           Object user that send the email (not the user we send too)
	 *  @param	string	$password       New password
	 *	@param	int		$changelater	0=Send clear passwod into email, 1=Change password only after clicking on confirm email. @todo Add method 2 = Send link to reset password
	 *  @return int 		            < 0 si erreur, > 0 si ok
	 */
	public function send_password($user, $password = '', $changelater = 0)
	{

		$context = Context::getInstance();


		// phpcs:enable
		global $conf, $langs;
		global $dolibarr_main_url_root;

		require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';

		$msgishtml = 0;

		// Define $msg
		$mesg = '';

		$outputlangs = new Translate("", $conf);

		if (isset($this->conf->MAIN_LANG_DEFAULT)
			&& $this->conf->MAIN_LANG_DEFAULT != 'auto') {	// If user has defined its own language (rare because in most cases, auto is used)
			$outputlangs->getDefaultLang($this->conf->MAIN_LANG_DEFAULT);
		}

		if (!empty($this->conf->MAIN_LANG_DEFAULT)) {
			$outputlangs->setDefaultLang($this->conf->MAIN_LANG_DEFAULT);
		} else {	// If user has not defined its own language, we used current language
			$outputlangs = $langs;
		}

		// Load translation files required by the page
		$outputlangs->loadLangs(array("main", "errors", "users", "other"));


		$subject = $outputlangs->transnoentitiesnoconv("SubjectNewPassword", $context->appliName);

		// Define $urlwithroot


		if (!$changelater) {
			$mesg .= $outputlangs->transnoentitiesnoconv("RequestToResetPasswordReceived").".\n";
			$mesg .= $outputlangs->transnoentitiesnoconv("NewKeyIs")." :\n\n";
			$mesg .= $outputlangs->transnoentitiesnoconv("Login")." = ".$this->login."\n";
			$mesg .= $outputlangs->transnoentitiesnoconv("Password")." = ".$password."\n\n";
			$mesg .= "\n";

			$mesg .= $outputlangs->transnoentitiesnoconv("ClickHereToGoTo", $context->appliName).': '.$context->getRootUrl()."\n\n";
			$mesg .= "--\n";
			$mesg .= $user->getFullName($outputlangs); // Username that send the email (not the user for who we want to reset password)

			dol_syslog(get_class($this)."::send_password changelater is off, url=".$context->getRootUrl());
		} else {
			$url = $context->getRootUrl('forgottenpassword', '&action=validatenewpassword&username='.urlencode($this->login)."&passwordhash=".dol_hash($password), false);

			$mesg .= $outputlangs->transnoentitiesnoconv("RequestToResetPasswordReceived")."\n";
			$mesg .= $outputlangs->transnoentitiesnoconv("NewKeyWillBe")." :\n\n";
			$mesg .= $outputlangs->transnoentitiesnoconv("Login")." = ".$this->login."\n";
			$mesg .= $outputlangs->transnoentitiesnoconv("Password")." = ".$password."\n\n";
			$mesg .= "\n";
			$mesg .= $outputlangs->transnoentitiesnoconv("YouMustClickToChange")." :\n";
			$mesg .= $url."\n\n";
			$mesg .= $outputlangs->transnoentitiesnoconv("ForgetIfNothing")."\n\n";

			dol_syslog(get_class($this)."::send_password changelater is on, url=".$url);
		}

		$trackid = 'use'.$this->id;

		$mailfile = new CMailFile(
			$subject,
			$this->email,
			$conf->global->MAIN_MAIL_EMAIL_FROM,
			$mesg,
			array(),
			array(),
			array(),
			'',
			'',
			0,
			$msgishtml,
			'',
			'',
			$trackid
		);

		if ($mailfile->sendfile()) {
			return 1;
		} else {
			$langs->trans("errors");
			$this->error = $langs->trans("ErrorFailedToSendPassword").' '.$mailfile->error;
			return -1;
		}
	}
}
