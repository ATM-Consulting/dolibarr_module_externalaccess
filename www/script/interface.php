<?php 

require __DIR__ .'/../config.php'; 


// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('externalaccessinterface','externalaccess'));

// IMPORTANT : YOU NEED TO CHECK IF USER IS LOG

$parameters=array();
$reshook=$hookmanager->executeHooks('doActionInterface',$parameters,$context, $context->action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');

if (!empty($reshook))
{
    
}