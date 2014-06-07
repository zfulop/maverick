function numOfGuestsChanged(type, roomId, numOfGuests) {
	toggleOtherDiv(type, roomId, numOfGuests);
	updatePreviewBooking();
}

function updatePreviewBooking() {
	new Ajax.Updater('preview_sum', 'preview_booking_sum.php', {
		parameters: $('availability_form').serialize(true),
		method:'get',
		onComplete: function() {
			if(document.getElementById('has_booking').value == '1') {
				document.getElementById('book_now_btn_div').style.display='block';
				document.getElementById('next_week_link').style.display='none';
				document.getElementById('prev_week_link').style.display='none';
			} else {
				document.getElementById('book_now_btn_div').style.display='none';
				document.getElementById('next_week_link').style.display='block';
				document.getElementById('prev_week_link').style.display='block';
			}
		}
	});
	return false;
}

function toggleOtherDiv(type, roomId, numOfGuests) {
	var otherDivId = "sel_num_guest_room_";
	var otherSelectId = "room_";
	if(type == "PRIVATE") {
		otherDivId += "DORM";
		otherSelectId += "DORM";
	} else {
		otherDivId += "PRIVATE";
		otherSelectId += "PRIVATE";
	}
	otherDivId += roomId;
	otherSelectId += roomId;
	if(document.getElementById(otherDivId)) {
		if(numOfGuests == 0) {
			document.getElementById(otherDivId).style.display = "block";
		} else {
			document.getElementById(otherDivId).style.display = "none";
			if(document.getElementById(otherSelectId)) {
				document.getElementById(otherSelectId).selectedIndex = 0;
			}
		}
	}

}

function CalcFixX() {
	return f_scrollLeft() + 100;
}

function CalcFixY() {
	return f_scrollTop() + 100;
}

function f_clientWidth() {
	return f_filterResults (
		window.innerWidth ? window.innerWidth : 0,
		document.documentElement ? document.documentElement.clientWidth : 0,
		document.body ? document.body.clientWidth : 0
	);
}
function f_clientHeight() {
	return f_filterResults (
		window.innerHeight ? window.innerHeight : 0,
		document.documentElement ? document.documentElement.clientHeight : 0,
		document.body ? document.body.clientHeight : 0
	);
}
function f_scrollLeft() {
	return f_filterResults (
		window.pageXOffset ? window.pageXOffset : 0,
		document.documentElement ? document.documentElement.scrollLeft : 0,
		document.body ? document.body.scrollLeft : 0
	);
}
function f_scrollTop() {
	return f_filterResults (
		window.pageYOffset ? window.pageYOffset : 0,
		document.documentElement ? document.documentElement.scrollTop : 0,
		document.body ? document.body.scrollTop : 0
	);
}
function f_filterResults(n_win, n_docel, n_body) {
	var n_result = n_win ? n_win : 0;
	if (n_docel && (!n_result || (n_result > n_docel)))
		n_result = n_docel;
	return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

