
// admin_sms_form.php

function adm_updateChar(fornName, output) {
	var length_limit = 80;
	
	var len = adm_calculate_msglen(fornName.value);
	output.innerText = len;

	if (len > length_limit) {
		fornName.value = fornName.value.replace(/\r\n$/, "");
		fornName.value = adm_assert_msglen(fornName.value, length_limit, output);
	}

}
function adm_calculate_msglen(message) {
   var nbytes = 0;
   for (i=0; i<message.length; i++) {
       var ch = message.charAt(i);
       if (escape(ch).length > 4) {
           nbytes += 2;
       } else if (ch == '\n') {
           if (message.charAt(i-1) != '\r') {
               nbytes += 1;
           }
       } else if (ch == '<' || ch == '>') {
           nbytes += 4;
       } else {
           nbytes += 1;
       }
   }
   return nbytes;
}

// 메세지 길이 초과시 자르기
function adm_assert_msglen(message, maximum, output) {
	var inc = 0;
	var nbytes = 0;
	var msg = "";
	var msglen = message.length;
 
	for (i=0; i<msglen; i++) {
		var ch = message.charAt(i);
		if (escape(ch).length > 4) {
			inc = 2;
		} else if (ch == '\n') {
			if (message.charAt(i-1) != '\r') {
				inc = 1;
			}
		} else if (ch == '<' || ch == '>') {
			inc = 4;
		} else {
			inc = 1;
		}
		if ((nbytes + inc) > maximum) {
			break;
		}
		nbytes += inc;
		msg += ch;
	}
	output.innerText = nbytes;
	return msg;
}

// push
function admin_push(onesignal_val) {
	document.getElementById('onesignal_value').value = onesignal_val;
}