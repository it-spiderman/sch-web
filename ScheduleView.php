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
    
    private function getSidebar( $sPageClass = '' ) {
	$aUser = $this->mModel->getOdooUser();
	$sHTML = "<div class='sidebar'><div class='content'>";
	$sHTML .= "<ul class='main-menu'>";
	$sHTML .= "<li id='menu-balance'><p>Membership overview</p></li>";
	$sHTML .= "<li id='menu-booking'><p>Make a booking</p></li>";
	$sHTML .= "</ul>";
	
	$sHTML .= "<ul class='status-menu'>";
	$sHTML .= "<li>Membership status:&nbsp<b>" . ucFirst( $aUser['status'] ) . "</b></li>";
	if( $aUser['status'] == 'paid' ) {
	    $sHTML .= "<li>Valid until:&nbsp" . $aUser['end'] . "</li>";
	}
	if( $aUser['credit'] != 0 ) {
	    $sHTML .= "<li id='credit-status'>Credit:&nbsp<b>"
		     . $this->mModel->toCurrency( $aUser['credit'] ) . "</b></li>";
	}
	
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
	
	$sHTML .= $this->getFooter();
	return $sHTML;
    }
    
    public function getBooking( $aParams ) {
	$sHTML = $this->getHeader();
	$sHTML .= $this->getSidebar();

	$sHTML .= "<div class='contentField'><div class='content'>";
	$sHTML .= "<p class='bookingTitle'>Booking</p>";
	if( empty( $aParams ) ) {
	    $sHTML .= $this->getDateForm();
	} else if( isset( $aParams['date'] ) && !isset( $aParams['resource'] ) ) {
	    $sHTML .= $this->getResourceForm( $aParams['date'] );
	} else if( isset( $aParams['date'] ) && isset( $aParams['resource'] )
		&& !isset( $aParams['from'] ) && !isset( $aParams['to'] )) {
	    $sHTML .= $this->getFinalForm( $aParams );
	} else if( isset( $aParams['date'] ) && isset( $aParams['resource'] ) 
		&& isset( $aParams['from'] ) && isset( $aParams['to'] ) ) {
	    $vBookingDetails = $this->mController->makeBooking( $aParams);

	    if( !$vBookingDetails ) {
		return "<div id='bookingSubmitError'></div>";
	    }
	    $this->mModel->setBookingDetails( $vBookingDetails );
	    return "<div id='bookingSubmitSuccess'></div>";
	} else if( isset( $aParams['error'] ) ) {
	    $sHTML .= "<p>Theres been an error!</p>";
	} else if( isset( $aParams['success'] ) ) {
	    $sHTML .= var_export($this->mModel->getBookingDetails(), true);
	}
	
	$sHTML .= "</div></div>";
	
	$sHTML .= $this->getFooter();
	return $sHTML;
    }
    
    protected function getDateForm() {
	$sHTML = "<p class='booking-command'>Select the date:<p>";
	$sHTML .= "<input name='date' type='date' id='booking-date'/>";
	return $sHTML;
    }
    
    protected function getResourceForm( $sDate ) {
	$sHTML = "<p class='booking-info'>Date:&nbsp" . $sDate . "</p>";
	$sHTML .= "<p class='booking-command'>Select the court:<p>";
	$aCourts = $this->mController->getResourcesForDate( $sDate );
	if( empty( $aCourts ) ) {
	    $sHTML .= "<p id='no-courts'>No courts are available<p>";
	    return $sHTML;
	}
	$sHTML .= "<ul class='resource-selection'>";
	foreach( $aCourts as $aCourt ) {
	    $sHTML .= "<li class='resource-selection-item' data-resource='" . $aCourt['id'] . "'>" . $aCourt['name'] . "</li>";
	}
	$sHTML .= "</ul>";
	return $sHTML;
    }
    
    protected function getFinalForm( $aParams ) {
	$sHTML = "<div class='booking-left'>";
	$sHTML .= "<p class='booking-info'>Date:&nbsp" . $aParams['date'] . "</p>";
	$sHTML .= "<p class='booking-info'>Court:&nbsp" . $this->mController->getName( $aParams['resource'] ) . "</p>";
	$sHTML .= "<div class='reservedHours'>Please select the time</div>";
	$sHTML .= "<div id='errorBooking'></div>";
	$sHTML .= "<div id='submitBooking'>Book!</div>";
	$sHTML .= "</div><div class='booking-right'>";
	$aHours = $this->mController->getWorkhours( $aParams );
	if( empty( $aHours ) ) {
	    $sHTML .= "<p class='booking-info'>There are no available booking for this date</p></div><div class='clear'></div>";
	    
	    return $sHTML;
	}
	
	$sHTML .= "<ul class='day-schedule'>";
	foreach( $aHours as $aHour ) {
	    $sHTML .= "<li class='hour hour" . $aHour['reason'] . "' "
		    . "data-start='" . $aHour['start_f'] . "' data-end='" . $aHour['end_f'] . "' "
		    . "data-available='" . $aHour['available'] . "'>"
		    . "<p>" . $aHour['start'] . " - " . $aHour['end'] . "</p><span>" . $aHour['reason'] . "</span>"
		    . "<div class='clear'></div></li>";
	}
	$sHTML .= "</ul>";
	$sHTML .= "</div><div class='clear'></div>";
	return $sHTML;
    }
    
    protected function getFooter() {
	return '';
	return "<div class='clear'></div><div class='footer'></div>";
    }
    
    public function error() {
	return "<p class='error'>Error has occured, please try later</p>";
    }
    
}
