<?php

/**
 * This is model class
 */
class ScheduleModel {

    /*private $sOdooUrl = 'http://localhost:8069';
    private $sOdooDb = 'Scheduling';
    private $sOdooUsername = "admin";
    private $sOdooPassword = "12345";*/
    private $sOdooUrl = 'https://127.0.0.1:8069';
    private $sOdooDb = 'odoo_db';
    private $sOdooUsername = "admin";
    private $sOdooPassword = "Luca2017";
    private $iOdooUID;
    private $aOdooUser;
    private $aOdooProfile;
    private $sBaseUrl = "/Scheduling/index.php";
    private $aBookingDetail = array();

    public function __construct() {
	
    }
    
    public function getBaseUrl() {
	return $this->sBaseUrl;
    }

    public function getOdooUser() {
	if ($this->aOdooUser) {
	    return $this->aOdooUser;
	} else if( isset( $_SESSION['odooUser'] ) ) {
	    $this->aOdooUser = $_SESSION['odooUser'];
	    return $this->aOdooUser;
	}
	return false;
    }

    public function setOdooUser($aUser) {
	$this->aOdooUser = $aUser;
	$_SESSION['odooUser'] = $aUser;
    }
    
    public function updateInfo( $aInfo ) {
	$oUser = $this->getOdooUser();
	if( $oUser ) {
	    $this->aOdooUser['credit'] = $aInfo['credit'];
	    $this->aOdooUser['status'] = $aInfo['status'];
	    $this->aOdooUser['start'] = $aInfo['start'];
	    $this->aOdooUser['end'] = $aInfo['end'];
	    $this->setOdooUser($this->aOdooUser);
	    return true;
	}
	return false;
    }
    
    public function getBookingDetails() { 
	if( $this->aBookingDetail ) {
	    return $this->aBookingDetail;
	}
	if( empty( $this->aBookingDetail ) && $_SESSION['bookingDetails'] ) {
	    $this->aBookingDetail = $_SESSION['bookingDetails'];
	    return $this->aBookingDetail;
	}
	return false;
    }
    
    public function setBookingDetails( $aDetails ) {
	$this->aBookingDetail = $aDetails;
	$_SESSION['bookingDetails'] = $aDetails;
    }

    public function executeOdooCommand($sModel, $sAction, $aFilters, $aParams = array()) {
	if (!$this->getOdooUID()) {
	    return false;
	}
	try {
	    $oModels = ripcord::client("$this->sOdooUrl/xmlrpc/2/object");

	    $vRes = $oModels->execute_kw(
			    $this->sOdooDb, $this->iOdooUID, $this->sOdooPassword, $sModel, $sAction, $aFilters, $aParams
	    );
	    if( array_key_exists( 'faultCode', $vRes) ) {
		return false;
	    }

	    return $vRes;
	} catch( Exception $e) {
	    return false;
	}
    }

    public function setSessionToken($bNew = true) {
	if (!$bNew) {
	    unset($_SESSION['loginToken']);
	    unset($_SESSION['odooUser']);
	    return true;
	}

	$sGuid = uniqid();
	if ($sGuid) {
	    $_SESSION['loginToken'] = $sGuid;
	    $this->bLoginFailed = false;
	    return true;
	}
	return false;
    }

    public function getSessionToken() {
	if (isset($_SESSION['loginToken'])) {
	    return $_SESSION['loginToken'];
	}
	return false;
    }

    public function getOdooUID() {
	if ($this->iOdooUID) {
	    return $this->iOdooUID;
	}

	require_once('plugins/ripcord/ripcord.php');

	$oCommon = ripcord::client("$this->sOdooUrl/xmlrpc/2/common");
	$this->iOdooUID = $oCommon->authenticate(
		$this->sOdooDb, $this->sOdooUsername, $this->sOdooPassword, array()
	);

	if ($this->iOdooUID) {
	    return $this->iOdooUID;
	}
	return false;
    }
    
    public function setOdooProfile( $vVal ) {
	$this->aOdooProfile = $vVal;
    }
    
    public function getOdooProfile() {
	if( $this->aOdooProfile ) {
	    return $this->aOdooProfile;
	}
	return false;
    }
    
    public function toCurrency( $fNumber ) {
	$fNumber = number_format( $fNumber, 2 );
	return (string) $fNumber . "€";
    }

}
