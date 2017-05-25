<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Scheduling system</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap -->
        <link href="plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="style.css" type="text/css" rel="stylesheet"/>
	<link href="booking.css" type="text/css" rel="stylesheet"/>
    </head>
    <body>
	<?php
	require_once('ScheduleController.php');
	require_once('ScheduleModel.php');
	require_once('ScheduleView.php');

	session_start();

	$oModel = new ScheduleModel();
	$oController = new ScheduleController($oModel);
	$oView = new ScheduleView( $oModel, $oController );

	$bLoggedIn = false;

	if (isset($_POST['action']) && !empty($_POST['action'])) {
	    $sAction = $_POST['action'];
	    switch ($sAction) {
		case 'login':
		    $oController->login($_POST['mail'], $_POST['pass']);
		    break;
		case 'logout':
		    $oController->logout();
		    break;
		default:
		    break;
	    }
	}

	if ($oModel->getOdooUser()) {
	    $bLoggedIn = true;
	}

	if (!$bLoggedIn) {
	    echo $oView->getLoginForm();
	    die();
	}

	if (isset($_GET['title'])) {
	    $sTitle = $_GET['title'];
	    switch ($sTitle) {
		case 'booking':
		    $aParams = [];
		    if( isset( $_GET['date'] ) ) {
			$aParams['date'] = $_GET['date'];
		    }
		    if( isset( $_GET['resource'] ) ) {
			$aParams['resource'] = $_GET['resource'];
		    }
		    if( isset( $_GET['from'] ) && isset( $_GET['to'] ) ) {
			$aParams['from'] = $_GET['from'];
			$aParams['to'] = $_GET['to'];
		    }
		    if( isset( $_GET['error'] ) ) {
			$aParams['error'] = true;
		    }
		    if( isset( $_GET['success'] ) ) {
			$aParams['success'] = true;
		    }

		    echo $oView->getBooking( $aParams );
		    break;
		case 'balance':
		    echo $oView->getBalance();
		    break;
		default:
		    die();
	    }
	} else {
	    echo $oView->getMain();
	}

	?>
        <script src="http://code.jquery.com/jquery.js"></script>
        <script src="plugins/bootstrap/js/bootstrap.min.js"></script>
	<script src="behaviour.js"></script>
    </body>
</html>
