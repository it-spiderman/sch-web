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
    
    public function buyCredit() {
	$sHTML = "<script src='https://www.paypalobjects.com/api/checkout.js'></script>";
	$sHTML .= $this->getHeader();
	$sHTML .= $this->getSidebar();
	
	$aOdooUser = $this->mModel->getOdooUser();
	
	$sHTML .= "<div class='contentField'><div class='content'>";
	$sHTML .= "<h2>Buy credit over PayPal</h2>";
	$sHTML .= "<div id='paymentContainer'>";
	$sHTML .= "<p class='credit-info'>Your current credit balance: " . $this->mModel->toCurrency($aOdooUser['credit']) . "</p>";
	$sHTML .= "<p class='label'>1. Enter the desired amount:</p></br>";
	$sHTML .= "<input type='number' min='0' step='0.01' id='paypal-credit-amount'>";
	$sHTML .= "<p class='label paypal-button-label'>2. Click on the button below to make the transaction:</p></br>";
	$sHTML .= "<div id='paypal-button-container'></div>";
	$sHTML .= "</div>";
	$sHTML .= "<div id='payment-result'></div>";
	$sHTML .= "<div id='payment-details'></div>";
	$sHTML .= "</div></div>";

	return $sHTML;
    }
    
    public function getBalance() {
	$sHTML = $this->getHeader();
	$sHTML .= $this->getSidebar();
	
	$aOdooUser = $this->mModel->getOdooUser();
	$aProfile = $this->mController->getProfile();

	$sHTML .= "<div class='contentField'><div class='content'>";
	
	/*NAVIGATION*/
	$sHTML .= "<ul class='nav nav-tabs' role='tablist'>"
		."<li class='active'><a href='#info' data-toggle='tab'>Personal information</a></li>"
	    ."<li ><a href='#credit' data-toggle='tab'>Credit overview</a></li>"
		."<li ><a href='#bookings' data-toggle='tab'>Bookings</a></li>"
	    ."<li ><a href='#membership' data-toggle='tab'>Membership overview</a></li></ul>";
	$sHTML .= "<div class='tab-content'>";
	/*Personal info*/
	$sHTML .= "<div id='info' role=tabpanel class='memberships tab-pane active'><h2>Personal information</h2>";
	$sHTML .= "<table class='table'><tr><td>Name</td><td>" . $aOdooUser['name'] . "</td></tr>";
	$sHTML .= "<tr><td>Telephone numbers</td><td>" . $aOdooUser['phone'] . "</td></tr>";
	$sHTML .= "<tr><td>Address</td><td>" . $aOdooUser['address'] . "</td></tr>";
	$sHTML .= "<tr><td>Email</td><td>" . $aOdooUser['email'] . "</td></tr>";
	$sHTML .= "</table>";
	$sHTML .= "</div>";
	/*MEMBERSHIP*/
	$sHTML .= "<div id='membership' role=tabpanel class='memberships tab-pane '><h2>Memberships</h2>";
	$sHTML .= "<p id='membership-membership-status'>Current membership status:&nbsp<b>"
		     .ucFirst( $aOdooUser['status'] ) . "</b></p>";
	$sHTML .= "<table id='membership-table' class='table'><thead>"
		. "<tr><th>Name</th><th>Date of purchase</th><th>Price</th><th>Start date</th><th>End date</th></tr></thead><tbody>";
	foreach( $aProfile['m_lines'] as $vValue ) {
	    $aFormattedLine = $this->mController->formatMembershipLine( $vValue );
	    $sHTML .= "<tr class='" . $aFormattedLine['classes'] . "'>";
	    foreach( $aFormattedLine['lines'] as $sField ) {
		$sHTML .= "<td>" . $sField . "</td>";
	    }
	    $sHTML .= "</tr>";
	}
	$sHTML .= "</tbody></table></div>";
	
	/*CREDIT*/
	$sHTML .= "<div id='credit' role=tabpanel class='tab-pane'><h2>Credit history</h2>";
	$sHTML .= "<p id='credit-credit-status'>Credit status:&nbsp<b>"
		     . $this->mModel->toCurrency( $aOdooUser['credit'] ) . "</b></p>";
	$sHTML .= "<div id='credit-buy-credit'>BUY CREDIT over PayPal</div>";
	$sHTML .= "<table id='credit-table' class='table table-inverse info-table'><thead>"
		. "<tr><th>Date</th><th>Description</th><th>Amount</th><th>Payment method</th>"
		. "</tr></thead><tbody>";
	foreach( $aProfile['c_lines'] as $vValue ) {
	    $aFormattedLine = $this->mController->formatCreditLine( $vValue );

	    $sHTML .= "<tr class='" . $aFormattedLine['classes'] . "'>";
	    foreach( $aFormattedLine['lines'] as $sKey => $sField ) {
		$sHTML .= "<td>" . $sField . "</td>";
	    }
	    $sHTML .= "</tr>";
	}
	$sHTML .= "</tbody></table></div>";
	/*END CREDIT*/	
	/*Bookings*/
	$sHTML .= "<div id='bookings' role=tabpanel class='tab-pane'><h2>Bookings</h2>";

	$sHTML .= "<table id='bookings-table' class='table table-inverse info-table'><thead>"
		. "<tr><th>Date</th><th>From</th><th>To</th><th>Court</th>"
		. "</tr></thead><tbody>";
	foreach( $aProfile['booking_lines'] as $vValue ) {
	    $aFormattedLine = $this->mController->formatBookingLine( $vValue );

	    $sHTML .= "<tr class='" . $aFormattedLine['classes'] . "'>";
	    foreach( $aFormattedLine['lines'] as $sKey => $sField ) {
		$sHTML .= "<td>" . $sField . "</td>";
	    }
	    $sHTML .= "</tr>";
	}
	$sHTML .= "</tbody></table></div>";
	$sHTML .= "</div></div>";

	return $sHTML;
    }
    
    public function getBooking( $aParams ) {
	$sHTML = $this->getHeader();
	$sHTML .= $this->getSidebar();

	$sHTML .= "<div class='contentField'><div class='content'>";
	$sHTML .= "<p class='bookingTitle'>Booking</p>";
	if( empty( $aParams ) ) {
	    $aDisabledDates = $this->mController->getDisabledDates();
	    if( !$aDisabledDates ) {
		$this->showError();
	    }
	    $sHTML .= $this->getDateForm( $aDisabledDates );
	} else if( isset( $aParams['date'] ) && !isset( $aParams['resource'] ) ) {
	    $sHTML .= $this->getResourceForm( $aParams['date'] );
	} else if( isset( $aParams['date'] ) && isset( $aParams['resource'] )
		&& !isset( $aParams['from'] ) && !isset( $aParams['to'] )) {
	    $sHTML .= $this->getFinalForm( $aParams );
	} else if( isset( $aParams['date'] ) && isset( $aParams['resource'] ) 
		&& isset( $aParams['from'] ) && isset( $aParams['to'] ) ) {
	    $vBookingDetails = $this->mController->makeBooking( $aParams);

	    if( !$vBookingDetails || array_key_exists('error', $vBookingDetails) ) {
		return "<div id='bookingSubmitError'></div>";
	    }
	    $this->mModel->setBookingDetails( $vBookingDetails );
	    return "<div id='bookingSubmitSuccess'></div>";
	} else if( isset( $aParams['error'] ) ) {
	    $sHTML .= "<p class='booking-error'>Booking could not be made!</p>";
	} else if( isset( $aParams['success'] ) ) {
	    $aBookingDetails = $this->mModel->getBookingDetails();
	    $sHTML .= "<p class='booking-success-title'>Booking successful!</p>";
	    $sHTML .= "<p class='booking-success-info'>Date: " . $aBookingDetails['date'] . "</p>";
	    $sHTML .= "<p class='booking-success-info'>Time: " . $aBookingDetails['from'] 
		    . " - " . $aBookingDetails['to'] . "</p>";
	    $sHTML .= "<p class='booking-success-info'>Court: " . $aBookingDetails['resource'] . "</p>";
	    
	}
	
	$sHTML .= "</div></div>";
	
	$sHTML .= $this->getFooter();
	return $sHTML;
    }
    
    protected function getDateForm( $aDisabled ) {
	$sHTML = "<p class='booking-command'>Select the date:<p>";
	$vDisabled = json_encode($aDisabled['disabled']);
	$sHTML .= "<div id='booking-date' data-date-start-date='" . $aDisabled['start_date'] . "'"
		. "data-date-end-date='" . $aDisabled['end_date'] . "' "
		. " data-date-dates-disabled=" . $vDisabled . "></div>";

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
	$sHTML .= "<div id='reservedPrice'></div>";
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
		    . "data-available='" . $aHour['available'] . "' data-price='" . $aHour['price'] . "'>"
		    . "<p>" . $aHour['start'] . " - " . $aHour['end'] . "</p>";
	    if( $aHour['available'] == 1 ) {
		$sHTML .= "<span class='price'>" . $aHour['price_message'] . "</span>";
	    } else {
		$sHTML .= "<span>" . $aHour['reason'] . "</span>";
	    }
	    $sHTML .= "<div class='clear'></div></li>";
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
    
    public static function getErrorPayment( $sMessage = '') {
	$sHTML = "<div class='error-message'>There has been an error!</br>"
		. "PayPal transaction succeeded but saving that information to your acccount failed!</br>"
		. "Please save transaction details below and contact site administrator</br></br>";
	if( $sMessage ){
	    $sHTML .= $sMessage;
	}
	$sHTML .= "</div>";
	return $sHTML;
    }
    
    public static function getSuccessPayment() {
	$sHTML = "<div class='success-message'>Transaction successful!</div>";
	return $sHTML;
    }
    
}
