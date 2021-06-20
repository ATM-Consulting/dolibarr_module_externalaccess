<?php


class Controller {

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

	public function loadTemplate($templateName){
		global $conf, $langs, $hookmanager; // load for tpl
		$context = Context::getInstance(); // load for tpl

		if(!ctype_alnum($templateName)){ return; }

		$tplPath = $context->tplPath . '/' . $templateName.'.tpl.php';

		if(!file_exists($tplPath)){ print 'ERROR TPL NOT FOUND : '.$templateName; return; }

		include $tplPath;
	}


}
