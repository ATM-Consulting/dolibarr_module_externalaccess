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
   public $action;
   
   public $tplDir = __DIR__.'/../';
   
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
       $this->conf =& $conf;
       $this->user =& $user;
       
       $this->getRootUrl();
       
       $this->topMenu = new stdClass();
       
       $this->tplPath = realpath ( __DIR__ .'/../tpl');
       
       $this->controller = GETPOST('controller', 'aZ09'); // for sécurity, limited to 'aZ09'
       $this->action = GETPOST('action', 'aZ09');// for sécurity, limited to 'aZ09'
       
       if(empty($this->controller)){
           $this->controller = 'default';
       }
   }
 
   /**
    * Méthode qui crée l'unique instance de la classe
    * si elle n'existe pas encore puis la retourne.
    *
    * @param void
    * @return Singleton
    */
   public static function getInstance() {
 
     if(is_null(self::$_instance)) {
         self::$_instance = new Context();  
     }
     return self::$_instance;
   }
   
   
   
   
   public function getRootUrl($controller = '')
   {
       
       if(!empty($this->conf->global->EACCESS_ROOT_URL))
       {
           $this->rootUrl = $this->conf->global->EACCESS_ROOT_URL;
           if(substr($url, -1))
           {
               $this->rootUrl .= '/';
           }
       }
       else 
       {
           $this->rootUrl = dol_buildpath('/externalaccess/www/',2);
       }
       
       
       
       return $this->rootUrl.(!empty($controller)?'?controller='.$controller : '');
   }
   
   
   public function userIsLog()
   {
       
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
   
   

}
 
?>