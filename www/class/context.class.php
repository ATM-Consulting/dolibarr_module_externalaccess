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
   
   
   
   
   public function getRootUrl()
   {
       if(!empty($this->conf->global->EACCESS_ROOT_URL))
       {
           $this->rootUrl = $conf->global->EACCESS_ROOT_URL;
           if(substr($url, -1))
           {
               $this->rootUrl .= '/';
           }
       }
       else 
       {
           $this->rootUrl = dol_buildpath('/externalaccess/',2);
       }
       
       
       
       return $this->rootUrl;
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