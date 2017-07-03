<?php
	require_once('ScheduleModel.php');
	
	$oModel = new ScheduleModel();
error_log("INN THIS  READINNN");	
	if ( isset( $_GET['rfid'] ) ) {
		error_log("SCANNING: " . $_GET['rfid']);
	    $vRes = $oModel->executeOdooCommand('membership_lite.domoticz', 'open_gate', array(array( 'rfid' => $_GET['rfid'] ) ) );
	    echo( var_export($vRes, true) );
		error_log(var_export($vRes, true));

	}
