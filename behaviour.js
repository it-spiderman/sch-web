(function () {
    $('#booking-date').datepicker({
	format: "yyyy-mm-dd",
	todayHighlight: true
    }).on( 'changeDate', function( e ) {
	var date = e.format('yyyy-mm-dd');
	var loc = window.location.pathname + window.location.search;
	window.location.replace(loc + "&date=" + date);
    });
    var t_start = undefined;
    var t_end = undefined;
    var t_price = 0;
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
	$.each(newHours, function (index, value) {
	    var period = $(value);

	    var start = period[0].attributes['data-start'].value;
	    var end = period[0].attributes['data-end'].value;
	    var price = period[0].attributes['data-price'].value;
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
	    message = "Booking period: " + hourize(t_start) + " - " + hourize(t_end);
	    $('.reservedHours').html(message);
	} else {
	    $('.reservedHours').html('Please select the time');
	}

	if (!fillInBlanks()) {
	    $('.hourNew').removeClass('hourNew');

	    $('#errorBooking').html("Selected period is not available").show();
	    $('#errorBooking').delay(3000).fadeOut(500);
	    $('.reservedHours').html('Please select the time');
	    t_start = t_end = undefined;
	    t_price = 0;
	    $('#reservedPrice').html('');
	    submitDisabled = true;
	    $('#submitBooking').addClass('submitDisabled');
	}

	if (t_price > 0) {
	    $('#reservedPrice').html('Total price: ' + monetize(t_price));
	} else {
	    $('#reservedPrice').html('');
	}

	if (t_start && t_end) {
	    submitDisabled = false;
	    $('#submitBooking').removeClass('submitDisabled');
	}
    }

    function fillInBlanks() {
	var res = true;
	$.each($(".hour"), function (index, value) {
	    var el = $(value);
	    /*if( el.hasClass('hourBooked') || el.hasClass('hourClosed') )
	     return;*/

	    var start = el[0].attributes['data-start'].value;
	    var end = el[0].attributes['data-end'].value;
	    var avb = el[0].attributes['data-available'].value;
	    var price = el[0].attributes['data-price'].value;

	    start = parseFloat(start);
	    end = parseFloat(end);
	    price = parseFloat(price);
	    if ((t_start && t_end) && (start > t_start) && (end < t_end)) {
		if (avb === '0') {
		    res = false;
		    return res;
		}
		t_price += price;
		el.addClass('hourNew');
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
    $('#submitBooking').addClass('submitDisabled');
    $('#submitBooking').click(function () {
	if (submitDisabled) {
	    return;
	}
	if (!t_start || !t_end) {
	    $('#errorBooking').html("You must select the time!").show();
	    $('#errorBooking').delay(3000).fadeOut(500);
	    return;
	}
	var path = window.location.pathname;
	var search = window.location.search;
	var time = "&from=" + t_start + "&to=" + t_end;
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

