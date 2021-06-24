<?php
require __DIR__ .'/config.php';

/** @var Context $context */

/*
 * Action
 */


$context->controllerInstance->action();

/*
 * View
 */

$context->controllerInstance->display();


