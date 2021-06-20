<?php
require __DIR__ .'/config.php';



/*
 * Action
 */

/** @var Context $context */
$context->controllerInstance->action();



/*
 * View
 */

$context->controllerInstance->display();


