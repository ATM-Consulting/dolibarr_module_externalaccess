<?php 
require __DIR__ .'/config.php'; 


// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('externalaccesspage','externalaccess'));



/*
 * Use $context singleton to modify menu, 
 */
$parameters=array(
    'controller' => $context->controller
);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$context, $context->action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');



/*
 * View 
 */
include __DIR__ .'/tpl/header.tpl.php';




$parameters=array(
    'controller' => $context->controller, // $context->controller is get by $context->construct()
);
$reshook=$hookmanager->executeHooks('PrintPageView',$parameters,$context, $context->action);    // Note that $action and $object may have been modified by hook
if ($reshook < 0) $context->setEventMessages($hookmanager->error,$hookmanager->errors,'errors');

if(!$context->controller_found) include __DIR__ .'/tpl/404.tpl.php';

include __DIR__ .'/tpl/footer.tpl.php';