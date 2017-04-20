<?php

/**
 * This is model class
 */
class ScheduleModel {
    private $sOdooUrl = 'http://localhost:8069';
    private $sOdooDb = 'Test';
    private $sOdooUsername = "admin";
    private $sOdooPassword = "12345";
    
    private $iOdooUID;
    private $aOdooUser;
    
    public function __construct() {
    }

    public function getOdooUser() {
        if( $this->aOdooUser ) {
            return $this->aOdooUser;
        }
        return false;
    }
    
    public function setOdooUser( $aUser ) {
        $this->aOdooUser = $aUser;
    }

    public function executeOdooCommand( $sModel, $sAction, $aFilters, $aParams = array() ) {
        if( !$this->getOdooUID() ) {
            return false;
        }
        $oModels = ripcord::client("$this->sOdooUrl/xmlrpc/2/object");

        return $oModels->execute_kw( 
                $this->sOdooDb,
                $this->iOdooUID,
                $this->sOdooPassword,
                $sModel,
                $sAction,
                $aFilters,
                $aParams
        );
    }
    
    public function setSessionToken( $bNew = true ) {
        if( !$bNew ) {
            unset( $_SESSION['loginToken'] );
            return true;
        }
        
        $sGuid = uniqid();
        if( $sGuid ) {
            $_SESSION['loginToken'] = $sGuid;
            $this->bLoginFailed = false;
            return true;
        }
        return false;
    }
    
    public function getSessionToken() {
        if( isset( $_SESSION['loginToken'] ) ) {
            return $_SESSION['loginToken'];
        }
        return false;
    }
    
    public function getOdooUID() {
        if( $this->iOdooUID ) {
            return $this->iOdooUID;
        }
        
        require_once('plugins/ripcord/ripcord.php');

        $oCommon = ripcord::client("$this->sOdooUrl/xmlrpc/2/common");
        $this->iOdooUID = $oCommon->authenticate(
                $this->sOdooDb,
                $this->sOdooUsername,
                $this->sOdooPassword, 
                array()
        );

        if( $this->iOdooUID ) {
            return $this->iOdooUID;
        }
        return false;
    }
}

