<?php
// Test Page : profile.php used
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';


//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
   <!-- MetisMenu CSS -->

<link  rel="stylesheet" href="css/member.css"/>
<div id="page-wrapper">
	<div id="profile" class="member_common">

		<?php include('./includes/flash_messages.php') ?>
		(z_app_test.php) Test Page<br />


		<a href="intent://scheme=barrybarries;package=com.cybertronchain.barrybarries;end;">intent://scheme=barrybarries;package=com.cybertronchain.barrybarries;end;</a>
		<br /><br />
		<a href="intent://#Intent;scheme=barrybarries;package=com.cybertronchain.barrybarries;end;">intent://#Intent;scheme=barrybarries;package=com.cybertronchain.barrybarries;end;</a>
		<br /><br />
		<a href="intent://com.cybertronchain.barrybarries">intent://com.cybertronchain.barrybarries</a>
		<br /><br />
		<a href="barrybarries://com.cybertronchain.barrybarries">barrybarries://com.cybertronchain.barrybarries</a>
		<br /><br />
		<a href="market://details?id=com.cybertronchain.barrybarries">market://details?id=com.cybertronchain.barrybarries</a>
		<br /><br />
		<a href="intent://com.cybertronchain.barrybarries#Intent;scheme=barrybarries;package=com.cybertronchain.barrybarries;end;">intent://com.cybertronchain.barrybarries#Intent;scheme=barrybarries;package=com.cybertronchain.barrybarries;end;</a>
		<br /><br />
		<a href="javascript:;" onclick="app_link_move('barrybarries')">Test<a>
		<br /><br />
		<a href="javascript:;" onclick="app_link_move('coinibt');">coinibt://com.cybertronchain.coinibt</a>
		<br /><br />

		<a href="javascript:;" onclick="goBarry_test();">Barry Test</a>
		<br /><br />

	</div>
</div>


<script>
    function goBarry_test() {
		var android_url = 'barrybarries://com.cybertronchain.barrybarries';
        $.ajax({
            url : 'go.barry.php',
            type : 'POST',
            data : {},
            dataType : 'json',
            success : function(resp){
                if (resp.msg) {
					//location.href = android_url;
                    document.location.href = android_url + "?ckey=" + resp.msg;
                } else {
                    //document.location.href = "https://barrybarries.kr";
					location.href = android_url;
                }
            },
            error : function(resp){
                //document.location.href = "https://barrybarries.kr";
				location.href = android_url;
            }
        });
    }
</script>
<?php include_once 'includes/footer.php'; ?>

