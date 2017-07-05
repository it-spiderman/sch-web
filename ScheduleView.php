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
        $sLoginText = '<p>Accedi</p>';
        if( $this->mController->isLoginFail() ) {
            $sLoginText = "<p class='loginFail'>Si è verificato un problema!</p>";
        }

        return '<div class="container">
            <div class="login">' . $sLoginText .
            '<form action="' . $this->mModel->getBaseUrl() . '" method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="mail">Email o nome utente</label>
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
        $sHTML .= "</p></li><li><form action='" . $this->mModel->getBaseUrl() . "' method='POST'>";
        $sHTML .= '<input type="hidden" name="action" value="logout">';
        $sHTML .= '<input type="submit" class="btn" value="Esci"></form></li></ul>';
        $sHTML .= '<div class="clear"></div></div></div>';
        return $sHTML;
    }

    private function getSidebar( $sPageClass = '' ) {
	$aUser = $this->mModel->getOdooUser();
	$sHTML = "<div class='sidebar'><div class='content'>";
	$sHTML .= "<ul class='main-menu'>";
	$sHTML .= "<li id='menu-balance'><p>Panoramica</p></li>";
	if( in_array( $aUser['status'], array( 'paid', 'free' ) ) ) {
	    $sHTML .= "<li id='menu-booking'><p>Effettua una prenotazione</p></li>";
	}

	$sHTML .= "</ul>";

	$sHTML .= "<ul class='status-menu'>";
	$sHTML .= "<li>Stato dell’utente:&nbsp<b>" . $this->translateState( $aUser['status'] ) . "</b></li>";
	if( $aUser['status'] == 'paid' ) {
	    $sHTML .= "<li>Valido sino:&nbsp" . $aUser['end'] . "</li>";
	}
	if( $aUser['credit'] != 0 ) {
	    $sHTML .= "<li id='credit-status'>Credito:&nbsp<b>"
		     . $this->mModel->toCurrency( $aUser['credit'] ) . "</b></li>";
	}

	$sHTML .= "</div></div>";
	return $sHTML;
    }

    public function translateState( $state ) {
      if( $state == 'paid' ) {
        return "Pagato";
      }
      if( $state == 'free' ) {
        return "Gratis";
      }
      if( $state == 'none' ) {
        return "Non socio";
      }
    }

    public function buyCredit() {
	$sHTML = "<script src='https://www.paypalobjects.com/api/checkout.js'></script>";
	$sHTML .= $this->getHeader();
	$sHTML .= $this->getSidebar();

	$aOdooUser = $this->mModel->getOdooUser();

	$sHTML .= "<div class='contentField'><div class='content'>";
	$sHTML .= "<h2>Acquista credito con PayPal</h2>";
	$sHTML .= "<div id='paymentContainer'>";
	$sHTML .= "<p class='credit-info'>Stato del Credito: " . $this->mModel->toCurrency($aOdooUser['credit']) . "</p>";
	$sHTML .= "<p class='label'>1. Inserisci l’importo desiderato:</p></br>";
	$sHTML .= "<input type='number' min='0' step='0.01' id='paypal-credit-amount'>";
	$sHTML .= "<p class='label paypal-button-label'>2. Clicca sul pulsante per concludere la transazione:</p></br>";
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
		."<li class='active'><a href='#info' data-toggle='tab'>Informazioni personali</a></li>"
	    ."<li ><a href='#credit' data-toggle='tab'>Stato del Credito</a></li>"
		."<li ><a href='#bookings' data-toggle='tab'>Prenotazioni</a></li>"
	    ."<li ><a href='#membership' data-toggle='tab'>Panoramica</a></li></ul>";
	$sHTML .= "<div class='tab-content'>";
	/*Personal info*/
	$sHTML .= "<div id='info' role=tabpanel class='memberships tab-pane active'><h2>Informazioni personali</h2>";
	$sHTML .= "<table class='table'><tr><td>Nome</td><td>" . $aOdooUser['name'] . "</td></tr>";
	$sHTML .= "<tr><td>Numero di telefono</td><td>" . $aOdooUser['phone'] . "</td></tr>";
	$sHTML .= "<tr><td>Indirizzo</td><td>" . $aOdooUser['address'] . "</td></tr>";
	$sHTML .= "<tr><td>Email</td><td>" . $aOdooUser['email'] . "</td></tr>";
	$sHTML .= "</table>";
	$sHTML .= "</div>";
	/*MEMBERSHIP*/
	$sHTML .= "<div id='membership' role=tabpanel class='memberships tab-pane '><h2>Pacchetti</h2>";
	$sHTML .= "<p id='membership-membership-status'>Stato dell’utente:&nbsp<b>"
		     .ucFirst( $aOdooUser['status'] ) . "</b></p>";
	$sHTML .= "<table id='membership-table' class='table'><thead>"
		. "<tr><th>Nome</th><th>Data di acquisto</th><th>Prezzo</th><th>Inizio – Il pacchetto è valido da</th><th>Fine – Il pacchetto è valido sino a</th><th></th></tr></thead><tbody>";
	$iIdx = 0;
	foreach( $aProfile['m_lines'] as $vValue ) {
	    $iIdx++;
	    $aFormattedLine = $this->mController->formatMembershipLine( $vValue );
	    $sHTML .= "<tr class='" . $aFormattedLine['classes'] . "' data-includes='" . $aFormattedLine['includes'] . "'>";
	    foreach( $aFormattedLine['lines'] as $sField ) {
		$sHTML .= "<td>" . $sField . "</td>";
	    }
	    if( !empty( $aFormattedLine['includes'] ) ) {
		$sHTML .= "<td><button type='button' class='btn btn-info btn-lg' data-toggle='modal' data-target='#includes" . $iIdx . "'>Cosa è incluso?</button></td>";
		$sHTML .= "<div id='includes" . $iIdx . "' class='modal fade' role='dialog'>" .
			"<div class='modal-dialog'>" .
			    "<div class='modal-content'>" .
			"<div class='modal-header'>" .
			"<button type='button' class='close' data-dismiss='modal'>&times;</button>" .
			"<h4 class='modal-title'>Cosa è incluso nel pacchetto?</h4>" .
			"</div>" .
			"<div class='modal-body'>";
		$sHTML .= "<ul>";
		foreach( explode( "|", $aFormattedLine['includes'] ) as $sInclude ) {
		    $sHTML .= "<li>". $sInclude . "</li>";
		}
		$sHTML .= "</ul>";
		$sHTML .= "</div></div></div></div>";
	    } else {
		$sHTML .= "<td><button type='button' disabled class='btn btn-info btn-lg' >Cosa è incluso?</button></td>";
	    }
	    $sHTML .= "</tr>";
	}
	$sHTML .= "</tbody></table></div>";

	/*CREDIT*/
	$sHTML .= "<div id='credit' role=tabpanel class='tab-pane'><h2>Credito</h2>";
	$sHTML .= "<p id='credit-credit-status'>Stato del Credito:&nbsp<b>"
		     . $this->mModel->toCurrency( $aOdooUser['credit'] ) . "</b></p>";
	$sHTML .= "<div id='credit-buy-credit'>Acquista credito con PayPal</div>";
	$sHTML .= "<table id='credit-table' class='table table-inverse info-table'><thead>"
		. "<tr><th>Data</th><th>Descrizione</th><th>Importo</th><th>Metodo di Pagamento</th>"
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
	$sHTML .= "<div id='bookings' role=tabpanel class='tab-pane'><h2>Prenotazioni</h2>";

	$sHTML .= "<table id='bookings-table' class='table table-inverse info-table'><thead>"
		. "<tr><th>Data</th><th>Da ore</th><th>A ore</th><th>Struttura</th>"
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
	$sHTML .= "<p class='bookingTitle'>Prenotazioni</p>";
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
	    $sHTML .= "<p class='booking-error'>La prenotazione non può essere completata!</p>";
	} else if( isset( $aParams['success'] ) ) {
	    $aBookingDetails = $this->mModel->getBookingDetails();
	    $sHTML .= "<p class='booking-success-title'>La prenotazione è stata completata con successo!</p>";
	    if(!array_key_exists('long', $aBookingDetails)) {
		$sHTML .= "<p class='booking-success-info'>Data: " . $aBookingDetails['date'] . "</p>";
		$sHTML .= "<p class='booking-success-info'>Ora: " . $aBookingDetails['from']
		    . " - " . $aBookingDetails['to'] . "</p>";
		$sHTML .= "<p class='booking-success-info'>Struttura: " . $aBookingDetails['resource'] . "</p>";
	    }

	    $sHTML .= "<p class='booking-success-info'>Appunto: " . $aBookingDetails['note'] . "</p>";

	}

	$sHTML .= "</div></div>";

	#$sHTML .= $this->getFooter();
	return $sHTML;
    }

    protected function getDateForm( $aDisabled ) {
	$sHTML = "<p class='booking-command'>Seleziona una data:<p>";
	$vDisabled = json_encode( $aDisabled['disabled'] );
	$sHTML .= "<div id='booking-date' data-date-start-date='" . $aDisabled['start_date'] . "'"
		. "data-date-end-date='" . $aDisabled['end_date'] . "' "
		. " data-date-dates-disabled=" . $vDisabled . "></div>";

	return $sHTML;
    }

    protected function getResourceForm( $sDate ) {
	$sHTML = "<p class='booking-info'>Date:&nbsp" . $sDate . "</p>";
	$sHTML .= "<p class='booking-command'>Seleziona una struttura:<p>";
	$aCourts = $this->mController->getResourcesForDate( $sDate );
	if( empty( $aCourts ) ) {
	    $sHTML .= "<p id='no-courts'>Nessuna struttura disponibile<p>";
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
	$sHTML .= "<p class='booking-info'>Data:&nbsp" . $aParams['date'] . "</p>";
	$sHTML .= "<p class='booking-info'>Struttura:&nbsp" . $this->mController->getName( $aParams['resource'] ) . "</p>";
	$sHTML .= "<div class='reservedHours'>Seleziona l’orario</div>";
	$sHTML .= "<div id='reservedPrice'></div>";
	$sHTML .= "<div id='errorBooking'></div>";
	$sHTML .= "<div id='submitBooking'>Prenota!</div>";
	$sHTML .= "<div id='submitBookingLong'></div>";
	$sHTML .= "</div><div class='booking-right'>";
	$aHours = $this->mController->getWorkhours( $aParams );
	if( empty( $aHours ) ) {
	    $sHTML .= "<p class='booking-info'>In questa data non è disponibile la prenotazione</p></div><div class='clear'></div>";

	    return $sHTML;
	}

	$sHTML .= "<ul class='day-schedule'>";
	foreach( $aHours as $aHour ) {
	    $sHTML .= "<li class='hour hour" . ucfirst( $aHour['reason'] ) . "' "
		    . "data-start='" . $aHour['start_f'] . "' data-end='" . $aHour['end_f'] . "' "
		    . "data-available='" . $aHour['available'] . "' data-price='" . $aHour['price'] . "' "
		    . "data-lprice='" . $aHour['lprice'] . "' data-lprice-message='" . $aHour['lprice_message'] . "'>"
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

    /*protected function getFooter() {
	return '';
	return "<div class='clear'></div><div class='footer'></div>";
    }*/

    public function error() {
	return "<p class='error'>C’è stato un errore, riprova più tardi. Grazie.</p>";
    }

    public static function getErrorPayment( $sMessage = '') {
	$sHTML = "<div class='error-message'>C’è stato un errore!</br>"
		. "La transazione PayPal è andata a buon fine ma non è riuscito il salvataggio sul tuo conto.</br>"
		. "Per favore prendi nota della transazione e contatta l’Amminsitrazione.</br></br>";
	if( $sMessage ){
	    $sHTML .= $sMessage;
	}
	$sHTML .= "</div>";
	return $sHTML;
    }

    public static function getSuccessPayment() {
	$sHTML = "<div class='success-message'>Transazione conclusa con successo!</div>";
	return $sHTML;
    }

}
