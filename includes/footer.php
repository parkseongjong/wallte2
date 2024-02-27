<?php
$bottom_fixed_menu_c = 'none';
//if ( isset($n_scroll_menu) && $n_scroll_menu == 'a') {
//	$bottom_fixed_menu_c = '';
//}
if (isset($_SESSION['user_logged_in']) && !empty($_SESSION['user_id'])) {
	$bottom_fixed_menu_c = '';
}

if (stristr($_SERVER['SCRIPT_FILENAME'], 'set_transferpw_') == TRUE){ 
	$bottom_fixed_menu_c = 'none';
}

$home_btn_class = 'off';
$coupon_btn_class = 'off';
if (stristr($_SERVER['REQUEST_URI'], 'index.php') == TRUE){ 
	$home_btn_class = 'on';
	$coupon_btn_class = 'off';
} else if (stristr($_SERVER['REQUEST_URI'], 'coupon_list.php') == TRUE || stristr($_SERVER['REQUEST_URI'], 'coupon_buy.php') == TRUE || stristr($_SERVER['REQUEST_URI'], 'coupon.pro.php') == TRUE){ 
	$home_btn_class = 'off';
	$coupon_btn_class = 'on';
}
?>
<div id="bottom_fixed_menu" class="<?php echo $bottom_fixed_menu_c; ?>">
	<ul>
		<li>
			<a href="<?php echo WALLET_URL ?>/index.php" title="home" class="<?php echo $home_btn_class; ?>">
				<img src="<?php echo WALLET_URL ?>/images/icons/bmenu_home_<?php echo $home_btn_class; ?>.png" alt="home" />
				<p><?php echo !empty($langArr['send_sms_message6']) ? $langArr['send_sms_message6'] : 'Home'; ?></p>
			</a>
		</li>
		<li>
			<a href="<?php echo WALLET_URL ?>/coupon_list.php" title="coupon" class="<?php echo $coupon_btn_class; ?>">
				<img src="<?php echo WALLET_URL ?>/images/icons/bmenu_coupon_<?php echo $coupon_btn_class; ?>.png" alt="home" />
				<p><?php echo !empty($langArr['coupon_fee_title2']) ? $langArr['coupon_fee_title2'] : 'Coupon'; ?></p>
			</a>
		</li>
<!--		<li>-->
<!--			<a href="javascript:;" title="exchange" onclick="goExchange()" class="off">-->
<!--				<img src="--><?php //echo WALLET_URL ?><!--/images/icons/bmenu_exchange.png" alt="exchange" />-->
<!--				<p>--><?php //echo !empty($langArr['bottom_fixed_menu_bit_btn']) ? $langArr['bottom_fixed_menu_bit_btn'] : 'Exchange'; ?><!--</p>-->
<!--			</a>-->
<!--		</li>-->
        <li>
            <a href="javascript:;" title="barrybarries" onclick="goBarry()" class="off">
                <img src="<?php echo WALLET_URL ?>/images/icons/bmenu_market.png" alt="market" />
                <p><?php echo !empty($langArr['bottom_fixed_menu_barry_btn']) ? $langArr['bottom_fixed_menu_barry_btn'] : 'Market'; ?></p>
            </a>
        </li>

	</ul>
</div>

<?php if ( empty($menu_show) ) { // header.php reference

// n_scroll_menu : a(barry, top) / t (top)
if (isset($_SESSION['user_logged_in']) && isset($n_scroll_menu) && !empty($n_scroll_menu)) { ?>
<div class="scroll_m">
	<!--
	<?php if ($n_scroll_menu == 'a') { ?>
		<a href="javascript:;" title="barrybarries" class="barrybarries" onclick="goBarry()"><img src="images/icons/smenu_barry.png" alt="barrybarries" /></a>
	<?php } ?>-->
	<p class="scroll_top_btn"><img src="<?php echo WALLET_URL ?>/images/icons/smenu_top.png" alt="top" /></p>
</div>
<?php } ?>

<?php include_once '/var/www/html/wallet2/barcode_common.php'; ?>

</div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    

    <!-- Bootstrap Core JavaScript -->
    <script src="<?php echo WALLET_URL ?>/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="<?php echo WALLET_URL ?>/js/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="<?php echo WALLET_URL ?>/js/sb-admin-2.js"></script>
    <script src="<?php echo WALLET_URL ?>/js/jquery.validate.min.js"></script>


<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-127069169-1"></script>

<script>


$(function(){
	bmenu_height1();
});
$(window).on('resize',function(){
	bmenu_height1();
});
function bmenu_height1(){
	if ( $("#bottom_fixed_menu").attr('class') != 'none' ) {
		var bmenu_height = $("#bottom_fixed_menu").outerHeight();
		if ( !$(".bottom_fixed_menu_blank").length ) {
			$("#page-wrapper").append('<div class="bottom_fixed_menu_blank" style="height:'+bmenu_height+'px"></div>');
		}
	}
}




  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-127069169-1');
  
    
  // for language
  function changeLanguage(getThis){
	var getThisVal = $(getThis).val();
	
	  	$.ajax({
			url : 'changelang.php',
			type : 'POST',
			data : {lang:getThisVal},
			dataType : 'json',
			success : function(resp){
				window.location.reload();
			},
			error : function(resp){
				window.location.reload();
			}
		}) 
  }
	function goExchange() {
		//pop_message("<?php echo !empty($langArr['ready_message']) ? $langArr['ready_message'] : 'Coming soon'; ?>");
		app_link_move('coinibt');
	}
    function goBarry() {
        $.ajax({
            url : 'go.barry.php',
            type : 'POST',
            data : {},
            dataType : 'json',
            success : function(resp){
                if (resp.msg) {
                    //console.log("성공 => "+resp.msg);
                    //console.log(resp.firstVisitAgree);
                    //alert("테스트중입니다");
                    document.location.href = "https://barrybarries.kr/?ckey=" + resp.msg +"&firstVisitAgree="+resp.firstVisitAgree;
                    //document.location.href = "https://barrybarries.kr/bbs/wallet_login.php?wallet_id=01066253606";
                } else {
                    //console.log("실패 => "+resp);
                    //return;
                    /*alert("로그인정보전달 실패");
                    return;*/
                    document.location.href = "https://barrybarries.kr";
                }
            },
            error : function(resp){
                document.location.href = "https://barrybarries.kr";
            }
        });
    }

$(function(){
	$(".scroll_top_btn").on('click', function(){
		window.scrollTo({
			top: 0,
			left: 0,
			behavior: 'smooth'
		});
	});
});

  // for language

  
function pop_message(message) {
	$("#pop_message .sub").html(message);
	$("#pop_message").removeClass('none');
	setTimeout(function(){$("#pop_message").addClass('none'); $("#pop_message .sub").html(''); }, 3000);
}


function app_link_move (app_name) {
	var android_url = '';
	var ios_id = '';
	var scheme = '';
	var web_url = '';

	if ( app_name == 'coinibt' ) {
		android_url = 'coinibt://com.cybertronchain.coinibt';
		ios_id = '';
		scheme = '';
		web_url = 'https://www.coinibt.io/';
	} else if ( app_name == 'barrybarries' ) {
		android_url = 'barrybarries://com.cybertronchain.barrybarries';
		ios_id = 'id1537941110';
		scheme = 'barrybarries';
		web_url = 'https://barrybarries.kr/';
	}

	var device = '';
	if (navigator.userAgent.indexOf("android-web-view") > - 1){
		device = 'android';
	} else if (navigator.userAgent.indexOf("ios-web-view") > - 1){
		device = 'ios';
	} else {
		device = 'web';
	}

	if ( device == 'android' ) {
		location.href = android_url;
	} else if ( device == 'ios' ) {
		if ( ios_id == '' ) {
			pop_message("<?php echo !empty($langArr['ready_message']) ? $langArr['ready_message'] : 'Coming soon'; ?>");
		} else {
			var visiteTm = ( new Date() ).getTime();
			setTimeout( function () {
				if ( ( new Date() ).getTime() - visiteTm < 3000 ) { // �۽���� �̵�
					location.href = "https://itunes.apple.com/app/" + ios_id;
				}
			} ,2500 );
			setTimeout( function () { // �۽���
				location.href = scheme + "://"; // scheme
			} ,0 );
		}
	} else if ( device == 'web' ) {
		location.href = web_url;
	}
}

</script>

<style>

#bottom_fixed_menu {
	padding: 11.3px 0;
	background-color: #FFFFFF;
	border-top: 1px solid #cfcfcf;
	width: 100%;
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	z-index: 99998;
}
#bottom_fixed_menu ul {
	list-style-type: none;
	padding: 0;
	margin: 0;
}
#bottom_fixed_menu ul li {
	float: left;
	/*width: 25%;*/
	width: 33.333%;
	text-align: center;
	overflow: hidden;
}
#bottom_fixed_menu ul li a {
	width: 100%;
	height: 100%;
	display: inline-block;
}
#bottom_fixed_menu ul:after {
	content:'';
	display: block;
	clear: both;
}
#bottom_fixed_menu ul li a p {
	font-weight: 500;
	font-size: 0.805rem;
	margin: 5px 0 0;
}
#bottom_fixed_menu ul li a.on p {
	color: #000000;
}
#bottom_fixed_menu ul li a.off p {
	color: #959595;
}
/*
#bottom_fixed_menu ul li:first-child a p {
	color: #000000;
}*/
#bottom_fixed_menu ul li a img {
	height: 22px;
	width: auto;
}



.scroll_top_btn:hover {
	text-decoration: none;
	color: #a8a8a8;
}
.scroll_top_btn {
	width: 45.8px;
	height: 45.8px;
	display: block;
	margin-top: 10.3px;
	cursor: pointer;
}
.scroll_top_btn img {
	width: 100%;
	height: auto;
}
/*
.barrybarries {
	width: 45.8px;
	height: 45.8px;
	display: block;
}
.barrybarries img {
	width: 100%;
	height: auto;
}*/
.scroll_m {
	z-index: 999;
	position: fixed;
	right: 10px;
	bottom: 110px;
}

@media only screen and (max-width: 767px) {
}
@media (min-width: 768px) {
}


/* pop message */
#pop_message {
	position: fixed;
	left: 0;
	width: 100%;
	text-align: center;
	z-index: 99999;
}
#pop_message .sub {
	display: inline-block;
	padding: 11px 40px;
	word-break: break-all;
	color: #FFFFFF;
	background-color: #000000;
	-ms-opacity: 0.6;
	-webkit-opacity: 0.6;
	-o-opacity: 0.6;
	-moz-opacity: 0.6;
	opacity: 0.6;
	z-index: 99999;
}
@media only screen and (max-width: 767px) {
	#pop_message {
		bottom: 100px;
	}
	#pop_message .sub {
		font-size: 1rem;
	}
}
@media (min-width: 768px) {
	#pop_message {
		top: 150px;
	}
	#pop_message .sub {
		font-size: 1.12rem;
	}
}
</style>
<?php
// #pop_message : The part that sprays a message on the screen instead of an alert
?>
<div id="pop_message" class="none"><div class="sub"></div></div>

<?php } // $menu_show ?>
</body>
</html>