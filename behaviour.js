(function () {

    /*Datepicker init*/
    $('#booking-date').datepicker({
	format: "yyyy-mm-dd",
	todayHighlight: true
    }).on( 'changeDate', function( e ) {
	var date = e.format('yyyy-mm-dd');
	var loc = window.location.pathname + window.location.search;
	window.location.replace(loc + "&date=" + date);
    });

    /*Data tables init */
    $('#credit-table').DataTable();
    $('#membership-table').DataTable();
    $('#bookings-table').DataTable();

    /*Buy credit init*/
    $( '#credit-buy-credit' ).click(function() {
	window.location.replace(window.location.pathname + "?title=buy_credit" );
    });

    /*PAYPAL*/
    var amount = 0;
    $('#paypal-credit-amount').change(function(e) {
	var amount = e.currentTarget.value;
	if(isNaN( amount ) ) {

	    return;
	} else {
	    amount = parseFloat(amount);
	    $('.paypal-button-label').show();
	    paypal.Button.render({

		env: 'sandbox', // sandbox | production

		// PayPal Client IDs - replace with your own
		// Create a PayPal app: https://developer.paypal.com/developer/applications/create
		client: {
		    sandbox:    'AVLQNMMwfD0mK5EVCLPWPkIRg0hD_vHoMTCEca6w8K9WupjnTZkCtMbL3xF10v23xYE8mgd4novecN_K',
		    production: '<insert production client id>'
		},

		// Show the buyer a 'Pay Now' button in the checkout flow
		commit: true,

		// payment() is called when the button is clicked
		payment: function(data, actions) {

		    // Make a call to the REST api to create the payment
		    return actions.payment.create({
			transactions: [
			    {
				amount: { total: amount, currency: 'EUR' }
			    }
			]
		    });
		},

		// onAuthorize() is called when the buyer approves the payment
		onAuthorize: function(data, actions) {

		    // Make a call to the REST api to execute the payment
		    return actions.payment.execute().then(function(e) {
			$('#paymentContainer').hide();
			$('#payment-details').html("<table class='table'><tr><td>Transaction id</td><td>" + e.id  + "</td></tr>"
				+ "<tr><td>Date</td><td>" + e.create_time + "</td></tr>"
				+ "<tr><td>Amount</td><td>" + e.transactions[0].amount.total
				+ e.transactions[0].amount.currency + "</td></tr></table>");
			$.post( "paypal.php", {'action': 'paypal', 'paypal':e})
				.done( function( ret ) {
				    console.log(ret);
				$('#payment-result').html(ret);

			});
		    });
		}

	    }, '#paypal-button-container');
	}
    });



    /*Global vars init */
    var t_start = undefined;
    var t_end = undefined;
    var t_price = 0;
    var t_lprice = 0;



    $(".hour").click(function (caller) {
	var target = $(caller.currentTarget);
	if (target.hasClass('hourBooked') || target.hasClass('hourClosed'))
	    return;

	if (target.hasClass('hourNew')) {
	    target.removeClass('hourNew');
	    recalculate();
	    return;
	}

	target.addClass('hourNew');
	recalculate();

    });

    function recalculate() {
	t_start = t_end = undefined;
	var newHours = $('.hourNew');
	t_price = 0;
	t_lprice = 0;
	var t_lmessage = '';
	$.each(newHours, function (index, value) {
	    var period = $(value);

	    var start = period[0].attributes['data-start'].value;
	    var end = period[0].attributes['data-end'].value;
	    var price = period[0].attributes['data-price'].value;

	    if( period[0].attributes['data-lprice'] ) {
		var lprice = period[0].attributes['data-lprice'].value;
		var lprice_message = period[0].attributes['data-lprice-message'].value;
		t_lprice += parseFloat( lprice );
		t_lmessage = lprice_message;
	    }
	    start = parseFloat(start);
	    end = parseFloat(end);
	    price = parseFloat(price);
	    if (!t_start || t_start > start) {
		t_start = start;
	    }
	    if (!t_end || t_end < end) {
		t_end = end;
	    }

	    t_price += price;
	});

	if (t_start && t_end) {
	    message = "Periodo di prenotazione: " + hourize(t_start) + " - " + hourize(t_end);
	    $('.reservedHours').html(message);
	} else {
	    $('.reservedHours').html('Seleziona l’orario');
	}

	if (!fillInBlanks()) {
	    $('.hourNew').removeClass('hourNew');

	    $('#errorBooking').html("Il periodo selezionato non è disponibile").show();
	    $('#errorBooking').delay(3000).fadeOut(500);
	    $('.reservedHours').html('Seleziona l’orario');
	    t_start = t_end = undefined;
	    t_price = 0;
	    $('#reservedPrice').html('');
	    submitDisabled = true;
	    $('#submitBooking').addClass('submitDisabled');
	    hideLPriceButton();
	}

	if (t_price > 0) {
	    $('#reservedPrice').html('Prezzo Totale: ' + monetize(t_price));
	} else {
	    $('#reservedPrice').html('');
	}

	if (t_start && t_end) {
	    submitDisabled = false;
	    $('#submitBooking').removeClass('submitDisabled');

	    if( t_lprice > 0 ) {
		showLPriceButton( t_lprice, t_lmessage );
	    }
	} else {
	    hideLPriceButton();
	}
    }

    function showLPriceButton( price, message ) {
	$( '#submitBookingLong' ).html('<span>' + message + '</span><p>' + monetize(price) + '</p>').show();
	submitLongDisabled = false;
    }

    function hideLPriceButton() {
	$( '#submitBookingLong' ).html('').hide();
	submitLongDisabled = true;
    }

    function fillInBlanks() {
	var res = true;
	$.each($(".hour"), function (index, value) {
	    var el = $(value);
	    if( el.hasClass('hourNew') )
		return;

	    var start = el[0].attributes['data-start'].value;
	    var end = el[0].attributes['data-end'].value;
	    var avb = el[0].attributes['data-available'].value;
	    var price = el[0].attributes['data-price'].value;

	    var lprice = 0;
	    if( el[0].attributes['data-lprice'] ) {
		lprice = el[0].attributes['data-lprice'].value;

	    }
	    lprice = parseFloat( lprice );

	    start = parseFloat(start);
	    end = parseFloat(end);
	    price = parseFloat(price);
	    if ((t_start && t_end) && (start > t_start) && (end < t_end)) {
		if (avb === '0') {
		    res = false;
		    t_lprice = 0;
		    return res;
		}
		t_price += price;
		el.addClass('hourNew');
		if( lprice > 0 ) {
		    t_lprice += lprice;
		}
	    }


	});
	return res;
    }

    function hourize(time) {
	var wholeHour = Math.floor(time);
	if (!wholeHour) {
	    return time;
	}
	var remainder = time - wholeHour;
	var hour = wholeHour.toString();
	if (wholeHour < 10) {
	    hour = '0' + hour;
	}
	var fMinute = remainder * 60;
	var minute = fMinute.toString();
	if (minute < 10) {
	    minute = '0' + minute;
	}

	return hour + ':' + minute;
    }

    function monetize(price) {
	price = price.toFixed(2);
	price = price.toString() + "€";
	return price;
    }

    var submitDisabled = true;
    var submitLongDisabled = true;
    $('#submitBooking').addClass('submitDisabled');
    $('#submitBooking').click(function () {
	if (submitDisabled) {
	    return;
	}
	if (!t_start || !t_end) {
	    $('#errorBooking').html("Devi selezionare l’ora!").show();
	    $('#errorBooking').delay(3000).fadeOut(500);
	    return;
	}
	var path = window.location.pathname;
	var search = window.location.search;
	var time = "&from=" + t_start + "&to=" + t_end;
	window.location.replace(path + search + time);
    });

    $('#submitBookingLong').click(function () {
	if (submitLongDisabled) {
	    return;
	}
	if (!t_start || !t_end) {
	    $('#errorBooking').html("Devi selezionare l’ora!").show();
	    $('#errorBooking').delay(3000).fadeOut(500);
	    return;
	}
	var path = window.location.pathname;
	var search = window.location.search;
	var time = "&from=" + t_start + "&to=" + t_end + "&long=1";
	window.location.replace(path + search + time);
    });

    var bookingError = $('#bookingSubmitError');
    if (bookingError.length > 0) {
	window.location.replace(window.location.pathname + "?title=booking&error=1");
    }

    var bookingSuccess = $('#bookingSubmitSuccess');
    if (bookingSuccess.length > 0) {
	window.location.replace(window.location.pathname + "?title=booking&success=1");
    }

    $('#menu-balance').click(function () {
	var loc = window.location.pathname;
	window.location.replace(loc + "?title=balance");
    });
    $('#menu-booking').click(function () {
	var loc = window.location.pathname;
	window.location.replace(loc + "?title=booking");
    });

    $('.resource-selection-item').click(function (caller) {
	resource_id = caller.currentTarget.attributes['data-resource'].value;
	var loc = window.location.pathname + window.location.search;
	window.location.replace(loc + "&resource=" + resource_id);
    });
})();
