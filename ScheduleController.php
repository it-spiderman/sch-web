<?php

class ScheduleController {
    private $mModel;
    
    private $bLoginFailed;
    
    public function __construct( $oModel ) {
        $this->mModel = $oModel;
        $this->bLoginFailed = false;
    }
    
    public function login( $sMail, $sPassword ) {
        $vRes = $this->mModel->executeOdooCommand( 'res.partner', 'authenticate_web_user',
                array( array( 'uname' => $sMail, 'pass' => $sPassword ) )
	);

        if( $vRes ) {
            $this->mModel->setOdooUser( $vRes );
            $this->mModel->setSessionToken();
            return true;
        }
        $this->bLoginFailed = true;
        return false;
    }
    
    public function logout() {
        $this->mModel->setSessionToken( false );
    }
    
    public function isLoginFail() {
        return $this->bLoginFailed;
    }
    
    public function getProfile() {
	$aOdooUser = $this->mModel->getOdooUser();
	if( !$aOdooUser ) {
	    return false;
	}
	$vRes = $this->mModel->executeOdooCommand( 'res.partner', 'get_profile_info',
		array( array( 'user_id' => $aOdooUser['id'] ) ) );

	if( $vRes ) {
	    $this->mModel->setOdooProfile( $vRes );
	    return true;
	}
	return false;
	
    }

    public function getResourcesForDate( $sDate ) {
	$vRes = $this->mModel->executeOdooCommand( 'membership_lite.resource', 'get_resource_for_date',
		array( array( 'date' => $sDate ) ) );
	
	return $vRes;
	
    }
    
    public function getName( $sResource ) {
	$vRes = $this->mModel->executeOdooCommand( 'membership_lite.resource', 'get_name',
		array( array( 'resource' => $sResource ) ) );

	if( !$vRes ) {
	    return false;
	}
	return $vRes['name'];
    }
    
    public function getWorkhours( $aParams ) {
	if( !isset( $aParams['date'] ) || !isset( $aParams['resource'] ) ) {
	    return [];
	}
	$vRes = $this->mModel->executeOdooCommand( 'membership_lite.resource', 'get_hours',
		array( array( 'date' => $aParams['date'], 'resource' => $aParams['resource'] ) ) );
	
	if( !$vRes ) {
	    return false;
	}

	$fDayStart = $vRes['day_start'];
	$fDayEnd = $vRes['day_end'];
	$aHours = [];
	$fControl = $fDayStart;
	while( $fControl < $fDayEnd ) {
	    $fStart = $fControl;
	    $fControl += 0.5;
	    $fEnd = $fControl;
	    $sNonAvailableReason = '';
	    $bAvailable = $this->isAvailable( $vRes['hours'], $vRes['bookings'], $fStart, $fEnd, $sNonAvailableReason ) ? 1 : 0;
	    $aHours[] = array(
		'start' => $this->hourize( $fStart ),
		'start_f' => $fStart,
		'end' => $this->hourize( $fEnd ),
		'end_f' => $fEnd,
		'available' => $bAvailable, 'reason' => $sNonAvailableReason 
	    );
	}
	return $aHours;
    }
    
    protected function isAvailable( $aHours, $aBookings, $fStart, $fEnd, &$sReason ) {
	$bAvaiable = false;
	foreach( $aHours as $aHour ) {
	    if( $fStart >= $aHour['from'] && $fEnd <= $aHour['to'] ) {
		$bAvaiable = true;
	    }
	}
	if( !$bAvaiable ) {
	    $sReason = 'Closed';
	    return false;
	}

	foreach( $aBookings as $aBooking ) {
	    if( ($fStart >= $aBooking['from'] && $fStart < $aBooking['to'] ) ||
		    ($fEnd <= $aBooking['to'] && $fEnd > $aBooking['from'] )){
		$bAvaiable = false;
	    }
	}
	
	if( !$bAvaiable) {
	    $sReason = 'Booked';
	    return false;
	}
	
	return $bAvaiable;
    }
    
    public function makeBooking( $aParams ) {
	//TODO: SECURITY!
	$aOdooUser = $this->mModel->getOdooUser();
	$aParams['user'] = $aOdooUser['id'];
	
	$vRes = $this->mModel->executeOdooCommand( 'membership_lite.booking', 'make_booking',
		array( $aParams ) );

	if( !$vRes ) {
	    return false;
	}
	$this->updateInfo( $aOdooUser['id'] );
	$this->getProfile();
	return $vRes;
    }
    
    public function updateInfo( $iUserId ) {
	$vRes = $this->mModel->executeOdooCommand( 'res.partner', 'get_info',
		array( array( 'user' => $iUserId ) ) );

	if( !$vRes ) {
	    return false;
	}
	return $this->mModel->updateInfo( $vRes );

	return false;
    }
    
    public function hourize( $fTime ) {
	$fWholeHour = floor( $fTime );
	if( !$fWholeHour ) {
	    return $fTime;
	}
	$fRemainder = $fTime - $fWholeHour;
	
	$sHour = $fWholeHour < 10 ? '0' . $fWholeHour : (string)$fWholeHour;
	$fMinute = $fRemainder * 60;
	$sMinute = $fMinute < 10 ? '0' . $fMinute : (string)$fMinute;
	
	return $sHour . ':' . $sMinute;
	
    }
}
