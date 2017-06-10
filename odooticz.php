<?php
	require_once('ScheduleModel.php');
	
	$oModel = new ScheduleModel();
	
	if ( isset( $_GET['rfid'] ) ) {
	    $vRes = $oModel->executeOdooCommand('membership_lite.domoticz', 'open_gate', array(array( 'rfid' => $_GET['rfid'] ) ) );
	    echo( var_export($vRes, true) );

	}
