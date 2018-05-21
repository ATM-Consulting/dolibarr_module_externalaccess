<?php
 
class Context {
 
  /**
   * @var Singleton
   * @access private
   * @static
   */
   private static $_instance = null;
   
   
 
   /**
    * Constructeur de la classe
    *
    * @param void
    * @return void
    */
   private function __construct() {  
       global $db, $conf, $user;
       
       $this->db =& $db;
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
           $url = $_SERVER['REQUEST_URI']; //returns the current URL
           $parsedUrl = parse_url($url);
           $this->rootUrl =  $parsedUrl['path'];
       }
       
       
       
       return $this->rootUrl;
   }
   
   
   public function userIsLog()
   {
       
       if(!empty($this->conf->authmode)){
           return true;
       }
       else{
           return false;
       }
   }
   
   
}
 
?>