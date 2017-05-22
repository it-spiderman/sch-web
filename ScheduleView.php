<?php

class ScheduleView {
    
    private $mModel;
    private $mController;
    
    public function __construct( $oModel, $oController ) {
        $this->mModel = $oModel;
        $this->mController = $oController;
    }
 
    public function getMain() {
	return $this->getBalance();
    }
    
    public function getLoginForm() {
        $sLoginText = '<p>Log in</p>';
        if( $this->mController->isLoginFail() ) {
            $sLoginText = "<p class='loginFail'>Login failed.<br/>Please check your email and password!</p>";
        }
        
        return '<div class="container">
            <div class="login">' . $sLoginText . 
            '<form action="/Scheduling/index.php" method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="mail">Email</label>
                <input type="text" class="form-control" id="mail" name="mail" placeholder="email">
    
            </div>
            <div class="form-group">
                <label for="uname">Password</label>
                <input type="password" class="form-control" id="pass" placeholder="password" name="pass">
    
            </div>
            <input type="submit" class="btn btn-success btn-lg btn-login" value="Login">
            </form></div></div>';
    }
    
    private function getHeader() {
        $aUser = $this->mModel->getOdooUser();

        $sHTML = "<div class='header'><div class='content'><div class='companyLogo'><img src='images/logo.png'/></div>";
        $sHTML .= "<ul class='userPrefs'><li><p class='userName'>";
        $sHTML .= $aUser['name'];
        $sHTML .= "</p></li><li><form action='/Scheduling/index.php' method='POST'>";
        $sHTML .= '<input type="hidden" name="action" value="logout">';
        $sHTML .= '<input type="submit" class="btn" value="Logout"></form></li></ul>';
        $sHTML .= '<div class="clear"></div></div></div>';
        return $sHTML;
    }
    
    private function getSidebar() {
	$aUser = $this->mModel->getOdooUser();
	
	$sHTML = "<div class='sidebar'><div class='content'><div class='membership-info'>"
		. "<span class='label label-info'>Current status:&nbsp<b>";
	$sHTML .= ucfirst ( $aUser['status'] ) . "</b></span>";
	if( $aUser['status'] == 'paid' ) {
	    $sHTML .= "<span class='label'>Valid until:&nbsp" . $aUser['end'] . "</span>";
	}
	$sHTML .= "</div>";
	if( $aUser['credit'] != 0 ) {
	    $sHTML .= "<a href='index.php?title=balance'><span class='label label-success credit'>Credit balance: &nbsp"
		     . $this->mModel->toCurrency( $aUser['credit'] ) . "</span></a>";
	}
	$sHTML .= "<a href='index.php?title=booking'><span class='label label-success'>Make a booking!</span></a>";
	$sHTML .= "</div></div>";
	return $sHTML;
    }
    
    public function getBalance() {
	$sHTML = $this->getHeader();
	$sHTML .= $this->getSidebar();
	
	$aProfile = $this->mModel->getOdooProfile();
	$sHTML .= "<div class='contentField'><div class='content'>";
	$sHTML .= "<div class='memberships'><h2>Memberships</h2>";
	$sHTML .= "<table class='table table-hover table-bordered'>"
		. "<tr><th>Date</th><th>Name</th><th>Price</th><th>Start</th><th>End</th></tr>";
	foreach( $aProfile['m_lines'] as $vValue ) {
	    $sHTML .= "<tr>";
	    foreach( $vValue as $sField ) {
		$sHTML .= "<td>" . $sField . "</td>";
	    }
	    $sHTML .= "</tr>";
	}
	$sHTML .= "</table></div>";
	
	$sHTML .= "<div class='credit_lines'><h2>Credit history</h2>";
	$sHTML .= "<table class='table table-hover table-bordered'>"
		. "<tr><th>Date</th><th>Amount</th><th>Method</th><th>Direction</th>"
		. "<th>Transfer ID</th><th>Note</th></tr>";
	foreach( $aProfile['c_lines'] as $vValue ) {
	    $sHTML .= "<tr>";
	    foreach( $vValue as $sField ) {
		$sHTML .= "<td>" . $sField . "</td>";
	    }
	    $sHTML .= "</tr>";
	}
	$sHTML .= "</table></div>";
	
	$sHTML .= "</div></div>";
	
	
	return $sHTML;
    }
    
    public function getBooking( $aParams ) {
	$sHTML = $this->getHeader();
	$sHTML .= $this->getSidebar();
	
	$aProfile = $this->mModel->getOdooProfile();
	$sHTML .= "<div class='contentField'><div class='content'>";
	$sHTML .= "<p>Book an appointment</p>";
	$sHTML .= "<form method='GET' action='index.php'>";
	$sHTML .= "<input name='title' value='booking' type='hidden'>";
	if( empty( $aParams ) ) {
	    $sHTML .= $this->getDateForm();
	} else if( isset( $aParams['date'] ) && !isset( $aParams['resource'] ) ) {
	    $sHTML .= $this->getResourceForm( $aParams['date'] );
	}
	
	$sHTML .= "</div></div>";
	
	
	return $sHTML;
    }
    
    protected function getDateForm() {
	$sHTML = "<form method='GET' action='index.php'>";
	$sHTML .= "<input name='title' value='booking' type='hidden'>";
	$sHTML .= "<input name='date' type='date'/>";
	$sHTML .= "<input type='submit' value='Next step'><form>";
	return $sHTML;
    }
    
    protected function getResourceForm( $sDate ) {
	$sHTML = "<p>Pick a resource<p>";
	$sHTML .= "<form method='GET' action='index.php'>";
	$sHTML .= "<input name='title' value='booking' type='hidden'>";
	$sHTML .= "<input name='date' type='date' readonly='true' value='$sDate'/>";
	$sHTML .= "<input name='resource' type='text'/>";
	$sHTML .= "<input type='submit' value='Next step'><form>";
	return $sHTML;
    }
    
    
    
    public function error() {
	return "<p class='error'>Error has occured, please try later</p>";
    }
    
}
