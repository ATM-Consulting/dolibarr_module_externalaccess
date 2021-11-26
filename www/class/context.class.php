<?php

require_once __DIR__ . '/dbtool.class.php';
require_once __DIR__ . '/controller.class.php';

/**
 * Class Context
 */
class Context
{

	/**
	 * @var Singleton
	 * @access private
	 * @static
	 */
	private static $_instance = null;

	public $title;
	public $desc;

	public $meta_title;
	public $meta_desc;

	/**
	 * The application name
	 * @var $appliName
	 */
	public $appliName;

	public $controller;
	public $controller_found = false;

	/**
	 * @var stdClass[]
	*/
	private $controllers = array();

	/**
	  * @var Controller $controllerInstance
	 */
	public $controllerInstance;

	/**
	* for internal error msg
	* @var string error
	*/
	public $error;

	public $action;

	public $tplDir;

	public $menu_active = array();

	public $eventMessages = array();

	public $tokenKey = 'ctoken';

	/**
	 * Curent object of page
	 * @var object $object
	 */
	public $object;

	/**
	 * Constructeur de la classe
	 *
	 * @return void
	 */
	private function __construct()
	{
		global $db, $conf, $user;

		// small retrocompatibility fix hack TODO : remove it when external access will be min version of Dolibarr 12
		if (empty($user->socid)){
			$user->socid = $user->societe_id; // For compatibility support
		}

		$this->dbTool = new ExternalDbTool($db);

		$this->tplDir = __DIR__.'/../';

		$this->getRootUrl();

		$this->topMenu = new stdClass();

		$this->tplPath = realpath(__DIR__ .'/../tpl');

		$this->controller = GETPOST('controller', 'aZ09'); // for sécurity, limited to 'aZ09'
		$this->action = GETPOST('action', 'aZ09');// for sécurity, limited to 'aZ09'

		$this->iframe = GETPOST('iframe', 'int');
		$this->iframe = (bool) $this->iframe;

		if (empty($this->controller)){
			$this->controller = 'default';
		}


		$this->appliName = !empty($conf->global->EACCESS_TITLE)?$conf->global->EACCESS_TITLE:$conf->global->MAIN_INFO_SOCIETE_NOM;

		$this->generateNewToken();

		$this->initController();

		// Init de l'url de base
		if (!empty($conf->global->EACCESS_ROOT_URL))
		{
			$this->rootUrl = $conf->global->EACCESS_ROOT_URL;
			if (substr($this->rootUrl, -1) !== '/') $this->rootUrl .= '/';
		}
		else {
			$this->rootUrl = dol_buildpath('/externalaccess/www/', 2);
		}
	}


	/**
	 * Méthode qui crée l'unique instance de la classe
	 * si elle n'existe pas encore puis la retourne.
	 *
	 * @return Context Instance
	 */
	public static function getInstance()
	{

		if (is_null(self::$_instance)) {
			 self::$_instance = new Context();
		}
		return self::$_instance;
	}

	/**
	 * @return bool
	 */
	public function initController()
	{
		global $db, $conf, $user, $langs;

		$defaultControllersPath = __DIR__ . '/../controllers/';

		// define controllers definition
		$this->addControllerDefinition('default', $defaultControllersPath.'default.controller.php', 'DefaultController');
		$this->addControllerDefinition('forgottenpassword', $defaultControllersPath.'forgotten_password.controller.php', 'ForgottenPasswordController');
		$this->addControllerDefinition('personalinformations', $defaultControllersPath.'personalinformations.controller.php', 'PersonalInformationsController');
		$this->addControllerDefinition('invoices', $defaultControllersPath.'invoices.controller.php', 'InvoicesController');
		$this->addControllerDefinition('orders', $defaultControllersPath.'orders.controller.php', 'OrdersController');
		$this->addControllerDefinition('propals', $defaultControllersPath.'propals.controller.php', 'PropalsController');
		$this->addControllerDefinition('projects', $defaultControllersPath.'projects.controller.php', 'ProjectsController');
		$this->addControllerDefinition('tasks', $defaultControllersPath.'tasks.controller.php', 'TasksController');
		$this->addControllerDefinition('expeditions', $defaultControllersPath.'expeditions.controller.php', 'ExpeditionsController');
		$this->addControllerDefinition('supplier_invoices', $defaultControllersPath.'supplier_invoices.controller.php', 'SupplierInvoicesController');
		$this->addControllerDefinition('tickets', $defaultControllersPath.'tickets.controller.php', 'TicketsController');

		// Appel des triggers
		include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		$interface=new Interfaces($db);
		$interface->run_triggers('externalAccessInitController', $this, $user, $langs, $conf);
		// Fin appel triggers

		// search for controller

		$this->controllerInstance = new Controller();
		if (isset($this->controllers[$this->controller]) && file_exists($this->controllers[$this->controller]->path)){
			require_once $this->controllers[$this->controller]->path;

			if (class_exists($this->controllers[$this->controller]->class)){
				$this->controllerInstance = new $this->controllers[$this->controller]->class();
				$this->setControllerFound();
			}
		}
	}

	/**
	 * @param $controller Controller
	 * @param $path Path
	 * @param $className className
	 * @return bool
	 */
	public function addControllerDefinition($controller, $path, $className)
	{

		$fileName = basename($path);
		$needle = '.controller.php';
		$length = strlen($needle);
		$isControllerFile = $length > 0 ? substr($fileName, -$length) === $needle : true;
		if (!$isControllerFile){
			$this->setError('Error: controller definition '.$fileName);
			return false;
		}

		$this->controllers[$controller] = new stdClass();
		$this->controllers[$controller]->path = $path;
		$this->controllers[$controller]->class = $className;

		return true;
	}

	/**
	 * setControllerFound
	 *
	 * @return void
	 */
	public function setControllerFound()
	{
		$this->controller_found = true;
	}

	/**
	 * @param string       $controller controller
	 * @param string|array $moreParams  moreParams
	 * @param bool         $addToken add token hash only if $controller is setted
	 * @return string
	 */
	public function getRootUrl($controller = false, $moreParams = '', $addToken = true)
	{
		$url = $this->rootUrl;

		if (empty($controller)){
			// because can be called without params to get only rootUrl
			return $url;
		}

		$Tparams = array();

		if (!empty($controller)){
			$Tparams['controller'] = $controller;
			// added to remove somme part on iframe calls
			if (!empty($this->iframe)){
				$Tparams['iframe'] = 1;
			}

			if (!empty($addToken)){
				$Tparams[$this->tokenKey] = $this->newToken();
			}
		}

		// if $moreParams is an array
		if (!empty($moreParams) && is_array($moreParams)){
			if (isset($moreParams['controller'])) unset($moreParams['controller']);
			if (!empty($moreParams)){
				foreach ($moreParams as $paramKey => $paramVal){
					$Tparams[$paramKey] = $paramVal;
				}
			}
		}

		if (!empty($Tparams)){
			$TCompiledAttr = array();
			foreach ($Tparams as $key => $value) {
				$TCompiledAttr[] = $key.'='.$value;
			}
			$url .= '?'.implode("&", $TCompiledAttr);
		}

		// if $moreParams is a string
		if (!empty($moreParams) && !is_array($moreParams))
		{
			if (empty($Tparams))
			{
				if ($moreParams[0] !== '?') $url .= '?';
				if ($moreParams[0] === '&') $moreParams = substr($moreParams, 1);
			}
			$url .= $moreParams;
		}

		return $url;
	}

	/**
	 * @param bool $withRequestUri withRequestUri
	 * @param false $use_forwarded_host use_forwarded_host
	 * @return string
	 */
	static public function urlOrigin($withRequestUri = true, $use_forwarded_host = false)
	{
		$s = $_SERVER;

		$ssl      = ( ! empty($s['HTTPS']) && $s['HTTPS'] == 'on' );
		$sp       = strtolower($s['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . ( ( $ssl ) ? 's' : '' );
		$port     = $s['SERVER_PORT'];
		$port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
		$host     = ( $use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST']) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null );
		$host     = isset($host) ? $host : $s['SERVER_NAME'] . $port;

		$url = $protocol . '://' . $host;

		if ($withRequestUri){
			$url.=$s['REQUEST_URI'];
		}

		return $url;
	}

	/**
	 * @return bool
	 */
	public function userIsLog()
	{
		// apparement dolibarr se sert de ça pour savoir si l'internaute est log
		if (!empty($_SESSION["dol_login"])){
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @param $menuName menuName
	 * @return bool
	 */
	public function menuIsActive($menuName)
	{
		return in_array($menuName, $this->menu_active);
	}

	/**
	 * @param $errors errors
	 *
	 * @return void
	 */
	public function setError($errors)
	{
		if (!is_array($errors)) $errors = array($errors);
		if (!isset($_SESSION['EA_errors'])) $_SESSION['EA_errors'] = array();
		foreach ($errors as $msg)
		{
			if (!in_array($msg, $_SESSION['EA_errors'])) $_SESSION['EA_errors'][] = $msg;
		}
	}

	/**
	 * @return int
	 */
	public function getErrors()
	{
		if (!empty($_SESSION['EA_errors']))
		{
			$this->errors = array_values($_SESSION['EA_errors']);
			return count($this->errors);
		}

		return 0;
	}

	/**
	 *  clearErrors
	 * @return void
	 */
	public function clearErrors()
	{
		unset($_SESSION['EA_errors']);
		$this->errors = array();
	}


	/**
	 *	Set event messages in dol_events session object. Will be output by calling dol_htmloutput_events.
	 *  Note: Calling dol_htmloutput_events is done into pages by standard llxFooter() function.
	 *
	 *	@param	string	$mesgs			Message string or array
	 *  @param  string	$style      	Which style to use ('mesgs' by default, 'warnings', 'errors')
	 *  @return	void
	 */
	public function setEventMessages($mesgs, $style = 'mesgs')
	{
		$TAcceptedStyle = array('mesgs', 'warnings', 'errors');

		if (!in_array($style, $TAcceptedStyle)){
			$style='mesgs';
		}

		if (!is_array($mesgs)) $mesgs = array($mesgs);
		if (!isset($_SESSION['EA_events'])){
			$_SESSION['EA_events'] = array(
				'mesgs' => array(), 'warnings' => array(), 'errors' => array()
			);
		}

		foreach ($mesgs as $msg)
		{
			if (!in_array($msg, $_SESSION['EA_events'][$style])) $_SESSION['EA_events'][$style][] = $msg;
		}
	}

	/**
	 * @return int
	 */
	public function loadEventMessages()
	{
		if (!empty($_SESSION['EA_events']))
		{
			$this->eventMessages = $_SESSION['EA_events'];
			return 1;
		}

		return 0;
	}

	/**
	 * clearEventMessages
	 * @return void
	 */
	public function clearEventMessages()
	{
		unset($_SESSION['EA_events']);
		$this->eventMessages = array();
	}

	/**
	 * Return the value of token currently saved into session with name 'newToken'.
	 * This token must be send by any POST as it will be used by next page for comparison with value in session.
	 * This token depend of controller
	 *
	 * @param false|string $controller controller
	 * @param bool         $generateIfNull generateIfNull
	 * @return  string
	 */
	public function newToken($controller = false, $generateIfNull = true)
	{
		if (empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }

		if (!isset($_SESSION['controllers_tokens'][$controller]['newToken'])
			&& $generateIfNull){
			$this->generateNewToken($controller);
		}

		return !empty($_SESSION['controllers_tokens'][$controller]['newToken'])?$_SESSION['controllers_tokens'][$controller]['newToken']:'';
	}

	/**
	 * generate new token.
	 *
	 * @param false|string $controller controller
	 * @return  string
	 */
	protected function generateNewToken($controller = false)
	{
		if (empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }


		if (empty($_SESSION['controllers_tokens'])){$_SESSION['controllers_tokens'] = array();}
		if (empty($_SESSION['controllers_tokens'][$controller])){$_SESSION['controllers_tokens'][$controller] = array();}


		// Creation of a token against CSRF vulnerabilities
		if (!defined('NOTOKENRENEWAL')) {
			// Rolling token at each call ($_SESSION['token'] contains token of previous page)
			if (isset($_SESSION['controllers_tokens'][$controller]['newToken'])) {
				$_SESSION['controllers_tokens'][$controller]['token'] = $_SESSION['controllers_tokens'][$controller]['newToken'];
			}


			// Save what will be next token. Into forms, we will add param $context->newToken();
			$token = dol_hash(uniqid(mt_rand(), true)); // Generat
			$_SESSION['controllers_tokens'][$controller]['newToken'] = $token;

			return $token;
		}
		else {
			return $this->newToken($controller, false);
		}
	}

	/**
	 * Return the value of token currently saved into session with name 'token'.
	 *
	 * @param bool $controller controller
	 * @return  string
	 */
	public function currentToken($controller = false)
	{
		if (empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }
		return isset($_SESSION['controllers_tokens'][$controller]['token'])?$_SESSION['controllers_tokens'][$controller]['token']:false;
	}

	/**
	 * @param false $controller controller
	 * @param bool  $erase erase
	 * @return bool
	 */
	public function validateToken($controller = false, $erase = true)
	{

		$token = GETPOST($this->tokenKey, 'aZ09');

		if (empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }
		$currentToken = $this->currentToken($controller);
		if (empty($currentToken)) return false;
		if (empty($token)) return false;
		if ($currentToken === $token){
			if ($erase){
				unset($_SESSION['controllers_tokens'][$controller]['token']);
			}
			return true;
		}
	}

	/**
	 * @param false|string $controller controller
	 * @return string|null
	 */
	public function getUrlToken($controller = false)
	{
		if (empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }
		$token = $this->newToken($controller);
		if ($token){
			return '&'.$this->tokenKey.'='.$this->newToken($controller);
		}
	}

	/**
	 * @param false|string $controller controller
	 * @return string|null
	 */
	public function getFormToken($controller = false)
	{
		if (empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }
		$token = $this->newToken($controller);
		if ($token) {
			return '<input type="hidden" name="' . $this->tokenKey . '" value="' . $this->newToken($controller) . '" />';
		}
	}
}
