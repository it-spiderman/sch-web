<?php

class ScheduleController {
    private $mModel;
    
    private $bLoginFailed;
    
    public function __construct( $oModel ) {
        $this->mModel = $oModel;
        $this->bLoginFailed = false;
    }
    
    public function login( $sMail, $sPassword ) {
        $vRes = $this->mModel->executeOdooCommand( 'res.partner', 'search_read',
                array( array( array('email', '=', $sMail), array('zip', '=', $sPassword ) ) ),
                array( 'fields' => array( 'name' ), 'limit' => 1 )
        );

        if( is_array( $vRes ) && count( $vRes ) == 1 ) {
            $this->mModel->setOdooUser( $vRes[0] );
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
}
