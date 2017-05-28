<?php

require_once('ScheduleController.php');
require_once('ScheduleModel.php');
require_once('ScheduleView.php');

session_start();

$oModel = new ScheduleModel();
$oController = new ScheduleController($oModel);
$oView = new ScheduleView($oModel, $oController);

$aOdooUser = $oModel->getOdooUser();
if (empty($aOdooUser)) {
    error_log("NO USER");
    return ['error' => 'You are not logged in'];
}

if (isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'paypal') {
    if (isset($_POST['paypal']) && !empty($_POST['paypal'])) {
	echo $oController->saveCredit($_POST['paypal']);
	die();
    }
}

