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
}
 
