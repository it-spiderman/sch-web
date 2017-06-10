<?php

class ScheduleController {

    private $mModel;
    private $bLoginFailed;

    public function __construct($oModel) {
	$this->mModel = $oModel;
	$this->bLoginFailed = false;
    }

    public function login($sMail, $sPassword) {
	$vRes = $this->mModel->executeOdooCommand('res.partner', 'authenticate_web_user', array(array('uname' => $sMail, 'pass' => $sPassword))
	);

	if ($vRes) {
	    $this->mModel->setOdooUser($vRes);
	    $this->mModel->setSessionToken();
	    return true;
	}
	$this->bLoginFailed = true;
	return false;
    }

    public function logout() {
	$this->mModel->setSessionToken(false);
    }

    public function isLoginFail() {
	return $this->bLoginFailed;
    }

    public function getProfile() {
	$aOdooUser = $this->mModel->getOdooUser();
	if (!$aOdooUser) {

	    return false;
	}

	$vRes = $this->mModel->executeOdooCommand('res.partner', 'get_profile_info', array(array('user_id' => $aOdooUser['id'])));

	if ($vRes) {
	    $this->mModel->setOdooProfile($vRes);
	    return $this->mModel->getOdooProfile();
	}

	return false;
    }

    public function getResourcesForDate($sDate) {
	$aUser = $this->mModel->getOdooUser();
	$vRes = $this->mModel->executeOdooCommand('membership_lite.resource', 'get_resource_for_date', array(array('date' => $sDate, 'user' => $aUser['id'] ) ) );

	return $vRes;
    }

    public function getName($sResource) {
	$vRes = $this->mModel->executeOdooCommand('membership_lite.resource', 'get_name', array(array('resource' => $sResource)));

	if (!$vRes) {
	    return false;
	}
	return $vRes['name'];
    }

    public function getWorkhours($aParams) {
	if (!isset($aParams['date']) || !isset($aParams['resource'])) {
	    return [];
	}
	$aOdooUser = $this->mModel->getOdooUser();
	$vRes = $this->mModel->executeOdooCommand('membership_lite.resource', 'get_hours', array(array('user' => $aOdooUser['id'], 'date' => $aParams['date'], 'resource' => $aParams['resource'])));

	if (!$vRes) {
	    return false;
	}

	$aHours = [];
	$aRawHours = $vRes['hours'];
	
	foreach( $aRawHours as $aRawHour ) {
	    $aHours[] = array(
		'start' => $this->hourize( $aRawHour['from'] ),
		'start_f' => $aRawHour['from'] ,
		'end' => $this->hourize( $aRawHour['to']  ),
		'end_f' => $aRawHour['to'] ,
		'available' => $aRawHour['available'] ? 1 : 0, 'reason' => $aRawHour['reason'],
		'price_message' => $aRawHour['price_message'],
		'price' => $aRawHour['price'],
		'lprice' => $aRawHour['lprice'],
		'lprice_message' => $aRawHour['lprice_message']
	    );
	}

	return $aHours;
    }

    public function getDisabledDates() {
	$vRes = $this->mModel->executeOdooCommand('membership_lite.resource', 'get_disabled_dates', array(array()));

	if (!$vRes) {
	    return false;
	}
	return $vRes;
    }

    public function saveCredit($aDetails) {
	$aOdooUser = $this->mModel->getOdooUser();
	$vRes = $this->mModel->executeOdooCommand('membership_lite.credit_line', 'add_credit', array(array('user' => $aOdooUser['id'], 'paypal' => $aDetails)));

	if (!$vRes) {
	    return ScheduleView::getErrorPayment();
	}
	if (array_key_exists('error', $vRes)) {
	    return ScheduleView::getErrorPayment($vRes['error']);
	}
	if (array_key_exists('success', $vRes)) {
	    return ScheduleView::getSuccessPayment();
	}
    }

    public function makeBooking($aParams) {
	//TODO: SECURITY!
	$aOdooUser = $this->mModel->getOdooUser();
	$aParams['user'] = $aOdooUser['id'];
	
	$sFunction = 'make_booking';
	if( $aParams['long'] == true ) {
	    $sFunction = 'make_long_booking';
	}

	$vRes = $this->mModel->executeOdooCommand('membership_lite.booking', $sFunction, array($aParams));
	error_log(var_export($vRes,true));
	if (!$vRes) {
	    return false;
	}
	$this->updateInfo($aOdooUser['id']);
	$this->getProfile();
	return $vRes;
    }

    public function updateInfo($iUserId) {
	$vRes = $this->mModel->executeOdooCommand('res.partner', 'get_info', array(array('user' => $iUserId)));

	if (!$vRes) {
	    return false;
	}
	return $this->mModel->updateInfo($vRes);

	return false;
    }

    public function hourize($fTime) {
	$fWholeHour = floor($fTime);
	if (!$fWholeHour) {
	    return $fTime;
	}
	$fRemainder = $fTime - $fWholeHour;

	$sHour = $fWholeHour < 10 ? '0' . $fWholeHour : (string) $fWholeHour;
	$fMinute = $fRemainder * 60;
	$sMinute = $fMinute < 10 ? '0' . $fMinute : (string) $fMinute;

	return $sHour . ':' . $sMinute;
    }

    public function formatCreditLine($aLine) {
	$aRes = [];
	$sClasses = '';
	$aRes[] = $this->formatDate($aLine['date'], true);
	if ($aLine['desc'] == false) {
	    $aRes[] = 'N/A';
	} else {
	    $aRes[] = $aLine['desc'];
	}
	$aRes[] = $this->mModel->toCurrency($aLine['amount']);
	$aRes[] = ucfirst($aLine['method']);
	if ($aLine['amount'] < 0) {
	    $sClasses .= ' red';
	}
	return ['classes' => $sClasses, 'lines' => $aRes];
    }

    public function formatMembershipLine($aLine) {
	$aRes = [];
	$sClasses = '';
	$aRes[] = $aLine['profile'];
	$aRes[] = $this->formatDate($aLine['date']);
	$aRes[] = $this->mModel->toCurrency($aLine['price']);
	$aRes[] = $this->formatDate($aLine['start']);
	$aRes[] = $this->formatDate($aLine['end']);
	if ($aLine['is_current']) {
	    $sClasses .= " current";
	}
	$aIncludes = implode( '|', $aLine['includes'] );
	return ['classes' => $sClasses, 'lines' => $aRes, 'includes' => $aIncludes ];
    }
    
    public function formatBookingLine($aLine) {
	$aRes = [];
	$sClasses = '';
	$aRes[] = $this->formatDate($aLine['date']);
	$aRes[] = $this->hourize($aLine['from']);
	$aRes[] = $this->hourize($aLine['to']);
	$aRes[] = $aLine['resource'];
	return ['classes' => $sClasses, 'lines' => $aRes];
    }

    public function formatDate($sDate, $bDateTime = false) {
	if ($bDateTime) {
	    $oDate = DateTime::createFromFormat('Y-m-d G:i:s', $sDate);
	    return $oDate->format('d.m.Y G:i:s');
	} else {
	    $oDate = DateTime::createFromFormat('Y-m-d', $sDate);
	    return $oDate->format('d.m.Y');
	}

	return false;
    }

}
