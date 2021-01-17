<?php

require_once __DIR__ . '/dbtool.class.php';

class Context {

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

   public $controller;
   public $controller_found = false;

   public $action;

   public $tplDir;

   public $menu_active = array();

   public $eventMessages = array();

   protected $tokenKey = 'ctoken';

	/**
	 * Curent object of page
	 * @var object $object
	 */
   public $object;

   /**
    * Constructeur de la classe
    *
    * @param void
    * @return void
    */
   private function __construct() {
       global $db, $conf, $user;

       $this->dbTool = new ExternalDbTool($db) ;

       $this->tplDir = __DIR__.'/../';

       $this->getRootUrl();

       $this->topMenu = new stdClass();

       $this->tplPath = realpath ( __DIR__ .'/../tpl');

       $this->controller = GETPOST('controller', 'aZ09'); // for sécurity, limited to 'aZ09'
       $this->action = GETPOST('action', 'aZ09');// for sécurity, limited to 'aZ09'

	   $this->iframe = GETPOST('iframe', 'int');
	   $this->iframe = (bool)$this->iframe;

       if(empty($this->controller)){
           $this->controller = 'default';
       }

	   $this->generateNewToken();


	   // Init de l'url de base
       if(!empty($conf->global->EACCESS_ROOT_URL))
       {
           $this->rootUrl = $conf->global->EACCESS_ROOT_URL;
           if(substr($this->rootUrl, -1) !== '/') $this->rootUrl .= '/';
       }
       else
       {
           $this->rootUrl = dol_buildpath('/externalaccess/www/',2);
       }
   }

   /**
    * Méthode qui crée l'unique instance de la classe
    * si elle n'existe pas encore puis la retourne.
    *
    * @param void
    * @return Context Instance
    */
   public static function getInstance() {

     if(is_null(self::$_instance)) {
         self::$_instance = new Context();
     }
     return self::$_instance;
   }


   public function setControllerFound() {
	   $this->controller_found = true;
   }


   public function getRootUrl($controller = '', $moreparams = '')
   {
		$url = $this->rootUrl;

		if (!empty($controller)){
			$url .= '?controller='.$controller;

			// added to remove somme part on iframe calls
			if(!empty($this->iframe)){
				$url .= '&iframe=1';
			}
		}

		if (!empty($moreparams))
		{
			if (empty($controller))
			{
				if ($moreparams[0] !== '?') $url .= '?';
				if ($moreparams[0] === '&') $moreparams = substr($moreparams, 1);
			}
			$url .= $moreparams;
		}

		return $url;
   }

	static public function urlOrigin($withRequestUri = true, $use_forwarded_host = false)
	{
		$s = $_SERVER;

		$ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
		$sp       = strtolower( $s['SERVER_PROTOCOL'] );
		$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
		$port     = $s['SERVER_PORT'];
		$port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
		$host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
		$host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;

		$url = $protocol . '://' . $host;

		if($withRequestUri){
			$url.=$s['REQUEST_URI'];
		}

		return $url;
	}


   public function userIsLog()
   {
       // apparement dolibarr se sert de ça pour savoir si l'internaute est log
       if(!empty($_SESSION["dol_login"])){
           return true;
       }
       else{
           return false;
       }
   }


   function menuIsActive($menuName)
   {
       return in_array($menuName, $this->menu_active);
   }

   public function setError($errors)
   {
	   if (!is_array($errors)) $errors = array($errors);
	   if (!isset($_SESSION['EA_errors'])) $_SESSION['EA_errors'] = array();
	   foreach ($errors as $msg)
	   {
		   if (!in_array($msg, $_SESSION['EA_errors'])) $_SESSION['EA_errors'][] = $msg;
	   }
   }

   public function getErrors()
   {
	   if (!empty($_SESSION['EA_errors']))
	   {
		   $this->errors = array_values($_SESSION['EA_errors']);
		   return count($this->errors);
	   }

	   return 0;
   }

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
	public function setEventMessages($mesgs, $style='mesgs')
	{
		$TAcceptedStyle = array('mesgs', 'warnings', 'errors');

		if(!in_array($style, $TAcceptedStyle)){
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

	public function loadEventMessages()
	{
		if (!empty($_SESSION['EA_events']))
		{
			$this->eventMessages = $_SESSION['EA_events'];
			return 1;
		}

		return 0;

	}

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
	 * @param false|string $controller
	 * @return  string
	 */
	function newToken($controller = false)
	{
		if(empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }

		if(!isset($_SESSION['controllers_tokens'][$controller]['newToken'])){
			$this->generateNewToken($controller);
		}

		return $_SESSION['controllers_tokens'][$controller]['newToken'];
	}

	/**
	 * generate new token.
	 *
	 * @param false|string $controller
	 * @return  string
	 */
	protected function generateNewToken($controller = false)
	{
		if(empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }


		if(empty($_SESSION['controllers_tokens'])){$_SESSION['controllers_tokens'] = array();}
		if(empty($_SESSION['controllers_tokens'][$controller])){$_SESSION['controllers_tokens'][$controller] = array();}


		// Creation of a token against CSRF vulnerabilities
		if (!defined('NOTOKENRENEWAL')) {

			// Rolling token at each call ($_SESSION['token'] contains token of previous page)
			if (isset($_SESSION['controllers_tokens'][$controller]['newToken'])) {
				$_SESSION['controllers_tokens'][$controller]['token'] = $_SESSION['controllers_tokens'][$controller]['newToken'];
			}


			// Save what will be next token. Into forms, we will add param $context->newToken();
			$token = dol_hash(uniqid(mt_rand(), TRUE)); // Generat
			$_SESSION['controllers_tokens'][$controller]['newToken'] = $token;

			return $token;

		}
		else{
			return newToken($controller);
		}
	}

	/**
	 * Return the value of token currently saved into session with name 'token'.
	 *
	 * @return  string
	 */
	function currentToken($controller = false)
	{
		if(empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }
		return isset($_SESSION['controllers_tokens'][$controller]['token'])?$_SESSION['controllers_tokens'][$controller]['token']:false;
	}

	/**
	 * @param false $controller
	 * @param bool  $erase
	 * @return bool
	 */
	function validateToken($controller = false, $erase = true){

		$token = GETPOST($this->tokenKey, 'aZ09');

		if(empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }
		$currentToken = $this->currentToken($controller);
		if(empty($currentToken)) return false;
		if(empty($token)) return false;
		if($currentToken === $token){
			if($erase){
				unset($_SESSION['controllers_tokens'][$controller]['token']);
			}
			return true;
		}
	}

	/**
	 * @param false|string $controller
	 * @return string|null
	 */
	function getUrlToken($controller = false){
		if(empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }
		$token = $this->newToken($controller);
		if($token){
			return '&'.$this->tokenKey.'='.$this->newToken($controller);
		}
	}

	/**
	 * @param false|string $controller
	 * @return string|null
	 */
	function getFormToken($controller = false){
		if(empty($controller)){ $controller = !empty($this->controller)?$this->controller:'default'; }
		$token = $this->newToken($controller);
		if($token) {
			return '<input type="hidden" name="' . $this->tokenKey . '" value="' . $this->newToken($controller) . '" />';
		}
	}
}

