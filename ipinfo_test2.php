<?php
$kind = isset($_REQUEST['kind']) ? $_REQUEST['kind'] : '';
if ( $kind =='ip1' ) {
	$country = new_kisa_ip_chk1();
	echo json_encode(array('country' => $country));
}

function new_kisa_ip_chk1(){

	$ip = '';
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		//ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		//ip pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	
	$key = "2020032517154809084222";
	$url ="http://whois.kisa.or.kr/openapi/ipascc.jsp?query=".$ip."&key=".$key."&answer=json";
	$ch = curl_init();

	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_NOSIGNAL, 1);
	//curl_setopt($ch,CURLOPT_POST, 1); //Method를 POST. 없으면 GET
	$data = curl_exec($ch);
	$curl_errno = curl_errno($ch);
	$curl_error = curl_error($ch);
	curl_close($ch);
	$decodeJsonData = json_decode($data, true);
	return $decodeJsonData['whois']['countryCode']; // KR, DE, LU, ...
}
?>

<script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
	var url = 'ipinfo_test2.php?kind=ip1';
	$.get(url, function(data) {
		var countryCode = JSON.parse(data).country;
		alert(countryCode);
	});

});
</script>

