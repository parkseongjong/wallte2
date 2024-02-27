
// set_transferpw
function stf_arrayShuffle(oldArray) {
    var newArray = oldArray.slice();
    var len = newArray.length;
    var i = len;
    while (i--) {
        var p = parseInt(Math.random()*len);
        var t = newArray[i];
        newArray[i] = newArray[p];
        newArray[p] = t;
    }
    return newArray;
} //

function stf_num_re() {
	var num_arr = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
	var new_array = stf_arrayShuffle(num_arr);
	var len = new_array.length;
	for (var i = 0; i < len; i++) {
		if (i + 1 == len) {
			$(".number p").eq(len).attr({'id':'pass_number_'+new_array[i], 'data-num':new_array[i]});
			//$(".number p").eq(len).html('<span>'+new_array[i]+'</span>');
			$(".number p").eq(len).html('<span><img src="images/icons/'+new_array[i]+'.png" alt="'+new_array[i]+'" /></span>');
		} else {
			$(".number p").eq(i).attr({'id':'pass_number_'+new_array[i], 'data-num':new_array[i]});
			//$(".number p").eq(i).html('<span>'+new_array[i]+'</span>');
			$(".number p").eq(i).html('<span><img src="images/icons/'+new_array[i]+'.png" alt="'+new_array[i]+'" /></span>');
		}
	} // for
} //

function stf_num_del() {
	if ( pass_length > 0 ) {
		if (pass_length == 1) {
			pass_length = 0;
			pass = '';
			$("#pas1").val(pass);
			$("#pass_area_"+pass_length+" img").attr('src','images/icons/pass_input_n.png');
		} else {
			pass_length = pass_length - 1;
			pass = pass.substr(0, pass_length);
			$("#pas1").val(pass);
			$("#pass_area_"+pass_length+" img").attr('src','images/icons/pass_input_n.png');
		}
	}
} //