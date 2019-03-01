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

		if (!empty($controller)) $url .= '?controller='.$controller;
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
}
 
