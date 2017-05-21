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
}
