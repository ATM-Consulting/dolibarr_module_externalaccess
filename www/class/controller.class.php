<?php


class Controller {


	/**
	 * if this controller need logged user or not
	 * @var bool
	 */
	public $accessNeedLoggedUser = true;

	/**
	 * define current user access
	 * @var bool
	 */
	public $accessRight = false;

	/**
	 * If controller is active
	 * @var bool
	 */
	public $controllerStatus = true;


	/**
	 * Constructeur de la classe
	 *
	 * @param void
	 * @return void
	 */
	public function __construct() {
		global $hookmanager;
		// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
		$hookmanager->initHooks(array('externalaccesspage','externalaccess'));
	}

	/**
	 * action method is called before html output
	 * can be used to manage security and change context
	 *
	 * @param void
	 * @return  int		< 0 on error, 0 on success, 1 to replace standard code
	 */
	public function action(){
		return $this->hookDoAction();
	}


	/**
	 * check current access to controller
	 *
	 * @param void
	 * @return  bool
	 */
	public function checkAccess() {

		$context = Context::getInstance();

		if($this->accessNeedLoggedUser){
			if (! $context->userIslog()) return false;
		}

		if(!$this->accessRight){
			return false;
		}

		return true;
	}


	/**
	 *
	 * @param void
	 * @return void
	 */
	public function display(){
		$context = Context::getInstance();

		$this->loadTemplate('header');

		$this->hookPrintPageView();

		if(!$context->controller_found) $this->loadTemplate('404');

		$this->loadTemplate('footer');
	}

	/**
	 *
	 * @param void
	 * @return void
	 */
	public function display404(){
		$this->loadTemplate('header');
		$this->loadTemplate('404');
		$this->loadTemplate('footer');
	}



	/**
	 * @param array $parameters
	 * @return  int		< 0 on error, 0 on success, 1 to replace standard code
	 */
	public function hookDoAction($parameters = array()){
		global $hookmanager;

		$context = Context::getInstance();

		/* Use $context singleton to modify menu, */
		$parameters['controller'] = $context->controller;

		$reshook=$hookmanager->executeHooks('doActions',$parameters,$context, $context->action);    // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			$context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');
		}

		return $reshook;
	}

	/**
	 * @param array $parameters
	 * @return  int		< 0 on error, 0 on success, 1 to replace standard code
	 */
	public function hookPrintPageView($parameters = array()){
		global $hookmanager;

		$context = Context::getInstance();

		/* Use $context singleton to modify menu, */
		$parameters['controller'] = $context->controller;

		$reshook=$hookmanager->executeHooks('PrintPageView',$parameters,$context, $context->action);    // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			$context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');
		}
		return $reshook;
	}

	/**
	 * @param string $templateName
	 * @param mixed $vars data to transmit to template
	 */
	public function loadTemplate($templateName, $vars = false){
		global $conf, $langs, $hookmanager, $db; // load for tpl
		$context = Context::getInstance(); // load for tpl

		if (!preg_match('/^[0-9\.A-ZaZ_\-]*$/ui', $templateName)) {
			return;
		}

		$tplPath = $context->tplPath . '/' . $templateName.'.tpl.php';

		if(!file_exists($tplPath)){ print 'ERROR TPL NOT FOUND : '.$templateName; return; }

		include $tplPath;
	}


}
