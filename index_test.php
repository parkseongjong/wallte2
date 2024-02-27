<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

use wallet\common\Log as walletLog;
use wallet\common\Info as walletInfo;
use wallet\common\InfoWeb3 as walletInfoWeb3;

require __DIR__ .'/vendor/autoload.php';

if(empty( $_SESSION['user_id'] )) {
    return;
    exit;
}
//2021-08-11 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('index 조회',['target_id'=>0,'action'=>'S']);

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$walletAddress = $row[0]['wallet_address'];
$checkApproved = $row[0]['usdt_approved'];
$email = $row[0]['email'];
$username = $row[0]['name'];
$registerWith = $row[0]['register_with'];
$showHeader = ($registerWith=="email") ? $email : $row[0]['phone'];

if ( !empty($row[0]['id_auth']) && $row[0]['id_auth'] == 'Y' && !empty($row[0]['auth_name']) ) { // (2020-05-25, YMJ)
    $username = $row[0]['auth_name'];
}

$user_name = '';
$user_name = get_user_real_name($row[0]['auth_name'], $row[0]['name'], $row[0]['lname']);

$etokenBalance = [];
foreach($n_decimal_point_array2 as $k1=>$v1) {
    $etokenBalance[$k1] = !empty($row[0]['etoken_'.$k1]) ? $row[0]['etoken_'.$k1] : 0;
}




//Get DB instance. function is defined in config.php
$db = getDbInstance();

//Get Dashboard information
$numCustomers = $db->getValue ("customers", "count(*)");

// Get Balance
$getbalances = array();
if ( !empty($walletAddress) ) {
    $wi_wallet_infos = new walletInfo();
    $web3Instance = new walletInfoWeb3();
    $getbalances = $wi_wallet_infos->wi_get_balance('', 'all', $walletAddress, $contractAddressArr);
    $bnbBalances = $web3Instance->wi_get_bsc_balance('bnb',$walletAddress,$contractAddressArr);
    
    $getbalances['ctc7'] = $web3Instance->wi_get_bsc_balance('ctc7',$walletAddress,$contractAddressArr);
    $getbalances['ctctm'] = $web3Instance->wi_get_bsc_balance('ctctm',$walletAddress,$contractAddressArr);
    //if ( !empty($getbalances['eth']) ) {
    //	$getbalances['eth'] = ($getbalances['eth']>0.0045 && $checkApproved=='N' && $row[0]['transfer_approved'] == 'C') ? $getbalances['eth']-0.0045 :$getbalances['eth'];
    //}
    if ( !empty($getbalances) ) {
        $_SESSION['eth_balance']	=	$getbalances['eth'];
        $_SESSION['Token_balance']	=	$getbalances['ctc'];
    }
}

//Get DB instance. function is defined in config.php
$db = getDbInstance();
$db->where("user_id", $_SESSION['user_id']);
$pointSum = $db->getValue("store_transactions", "sum(points)");

include_once('includes/header.php');

if ( !empty($row[0]['profile_img']) && is_file($n_profile_uploaddir . $row[0]['profile_img']) ) {
    $profile_img_path = $n_profile_uploaddir . $row[0]['profile_img'];
    $profile_circle2_class = 'profile_circle2_back';
    $img_size = GetImageSize($profile_img_path); // 0: 가로, 1 : 세로
    if ( $img_size[0] >= $img_size[1]) {
        $profile_img_class = 'profile_img_w';
    } else {
        $profile_img_class = 'profile_img';
    }
} else {
    $profile_img_path = 'images/icons/person.png';
    $profile_img_class = 'profile_img_none';
    $profile_circle2_class = 'profile_circle2_back_none';
}

$coupon_count = 0;
/*
$db = getDbInstance();
$db->where("user_id", $_SESSION['user_id']);
$db->where('coupon_kind', 'fee');
$coupon_count = $db->getValue("coupon_result", "count(*)");
*/

?>
<?php if($_SESSION['user_id'] == '11863'|| $_SESSION['user_id'] == '5137' || $_SESSION['user_id'] == '12122'): //webview TEST ?>
    <a href="https://cybertronchain.com/wallet2/ojtojtojt.php">tttt</a>
    <a href="http://110.10.189.191/wallet2/login.php">test server GO</a><br>
    <a href="https://cybertronchain.com/wallet2/control.php/notice/v1">testtesttest!</a><br>
    <a href="https://dev.barrybarries.kr/">DEV 서버BARRY!</a><br>
<?php endif; ?>
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<style>
    .change_address_btn {
        background-color: #ffea61;
        border: 1px solid #ffea61;
        font-size: 1.47rem;
        color: #000000;
        font-weight: 500;
        width: 100%;
        padding: 19px 0;
        margin: 15px auto 30px;
    }
</style>

<div id="popup_box" style="position:fixed; left:10%; top:20%; width: 80%; z-index:110000; background:#fff; border-radius:5px; display: none; " onclick="pop_close();">
    <div style="line-height: 22px; padding: 20px">
        <div style="margin-bottom:12px; text-align: center; font-weight:bold">공지사항</div>
        ERC20 mage update 인하여 <br />
        내일 모래(21일) 오전까지 간혈적 점검하오니<br />
        유념해주시길 바랍니다. <br />
        <br />
        CTC Wallet을 이용해주셔서 감사합니다.<br />
        <br />
        (클릭시 창이 닫힙니다.)
    </div>
</div>

<script>

    function pop_close(){
        $("#popup_box").css('display','none');
    }
    function refresh_move() {
        $("#index .index_token_block .refresh a img").addClass('refresh_rotate');
        location.href="index.php";
    }
    function goPlayStore() {
        window.location.href = "market://details?id=com.cybertronchain.wallet2";
    }

    $(function () {
        var tab3_text = "<?php echo !empty($langArr['charge_menu_tab']) ? $langArr['charge_menu_tab'] : 'Charging'; ?>";
        var mySwiperText = ['E-PAY', 'COIN', tab3_text];
        var mySwiper = new Swiper('.swiper-container', {
            // Optional parameters
            direction: 'horizontal',
            loop: false,
            spaceBetween: 30,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                renderBullet: function (index, className) {
                    // return '<span class="' + className + ' swiper-title-' + (index + 1) + '">' + mySwiperText[index] + '</span>';
                    //if (mySwiperText[index] === "Charging") {
                    if ( index == 2 ) {
                        // return `<span class="${className} swiper-title-${index + 1}"><img src="./charging.png" alt="charging" class="charging-img">${mySwiperText[index]}</span>`
                        return `
					<span class="${className} swiper-title-${index + 1}">
						<div class="flex-box">
							<img src="images/icons/charging.png" alt="charging" class="charging-img"><span>${mySwiperText[index]}</span>
						</div>
					</span>`
                    }
                    return `
					<span class="${className} swiper-title-${index + 1}">
						<div class="flex-box">
							<span>${mySwiperText[index]}</span>
						</div>
					</span>`
                }
            },
            paginationUpdate(swiper, el) {
                console.log('paginationUpdate');
            }
        });

        // control swiper-tab style
        function setTabShadow() {
            const activeTab = $('.swiper-pagination-bullet-active')[0];
            const prevTab = activeTab.previousElementSibling;
            const nextTab = activeTab.nextElementSibling;
            const tabs = [prevTab, nextTab];

            // 각 탭들에 줬던 draw-shadow 제거
            for (const tabItem of $('.swiper-pagination-bullet')) {
                tabItem.classList.remove('draw-shadow-left');
                tabItem.classList.remove('draw-shadow-right');
            }

            // active 양 옆 tab에 shadow 관련 css class 추가
            if (prevTab) {
                prevTab.classList.add('draw-shadow-right');
            }
            if (nextTab) {
                nextTab.classList.add('draw-shadow-left');
            }
        }
        setTabShadow();
        mySwiper.on('paginationUpdate', function (swiper, paginationEl) {
            setTabShadow();
        });
    });


    <?php
    // $_SESSION['user_id']=='5137' : 유차장
    ?>
    <?php if ($_SESSION['user_id']=='6135') { ?>
    $(document).ready(function(){
        /*
                if (navigator.userAgent.indexOf("android-web-view3") < 0){
                    $('<div id="_hidden_frame" style="position:fixed; left:0; top:0; width:100%; height:100%; background:#0c0c00; z-index:100000; opacity:0.4"></div>').appendTo('body');
                    $('#popup_box').appendTo('body');
                    $('#popup_box').show();
                }
        */
    });
    <?php } ?>
</script>


<link  rel="stylesheet" href="css/main.css?ver=2.1.5"/>
<div id="page-wrapper">
    <div id="index">
        <script>
            function view_message () {
                let msg = "<?php echo !empty($langArr['ready_message']) ? $langArr['ready_message'] : "준비중 입니다."; ?>";
                bsCommonAlert(msg);
            }
        </script>
        <div class="row" style="overflow: hidden;">
            <?php include('./includes/flash_messages.php') ?>

            <div class="infos">
                <div class="infos1">
                    <div class="f2_left">
                        <div class="circle1"><div class="circle2 <?php echo $profile_circle2_class; ?>"><img src="<?php echo $profile_img_path; ?>" alt="profile" class="<?php echo $profile_img_class; ?>" /></div></div>
                    </div>
                    <div class="f2_right">
                        <div class="f2_1">
                            <p class="user_ename"><?php echo $user_name; ?></p>
                        </div>
                        <div class="f2_1">
                            <div class="f2_right_name"><p class="user_eid"><?php echo $showHeader; ?></p></div>
                            <div class="profile">
                                <a href="profile.php" title="My Info">
                                    <span><?php echo !empty($langArr['index_profile_link']) ? $langArr['index_profile_link'] : 'My Info'; ?></span>
                                    <img src="images/icons/blue_right_btn.png" alt="My Info" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slider main container -->
            <div class="swiper-container">
                <div class="swiper-pagination"></div>
                <!-- Additional required wrapper -->
                <div class="swiper-wrapper">
                    <!-- Slides -->
                    <div class="swiper-slide">

                        <ul class="index_token_block">
                            <li class="refresh">
                                <a href="javascript:;" title="refresh" onclick="refresh_move();">
                                    <img src="images/icons/refresh.png" alt="refresh" />
                                    <?php echo !empty($langArr['refresh']) ? $langArr['refresh'] : 'Refresh'; ?>
                                </a>
                            </li>
                            <?php


                            
                            foreach ($n_full_name_array2 as $key=>$val) {
                            //if ( $key == 'eusdt' || $key == 'eeth') {
                            //if ( isset($_SESSION['admin_type']) && $_SESSION['admin_type'] == 'admin' ) {
                            ?><!--
									<li class="token_block">
										<a href="etoken.php?token=<?php echo $key; ?>" title="<?php echo strtoupper($key); ?>">
											<div class="img2"><div><img src="images/logo2/<?php echo $key; ?>.png" alt="<?php echo $key; ?>" /></div></div>
											<span class="text"><?php echo $n_full_name_array2[$key]; ?> (관리자만보임)</span>
											<span class="amount"><span class="amount_t1"><?php echo new_number_format($etokenBalance[$key], $n_decimal_point_array2[$key]); ?></span><span class="amount_t2"> <?php echo $n_epay_name_array[$key]; ?></span></span>
										</a>
									</li>-->
                            <?php
                            //}
                            //} else {
                            ?>
                            <li class="token_block">
                                <a href="etoken.php?token=<?php echo $key; ?>" title="<?php echo strtoupper($key); ?>">
                                    <div class="img2"><div><img src="images/logo2/<?php echo $key; ?>.png" alt="<?php echo $key; ?>" /></div></div>
                                    <span class="text"><?php echo $n_full_name_array2[$key]; ?></span>
                                    <span class="amount"><span class="amount_t1"><?php echo new_number_format($etokenBalance[$key], $n_decimal_point_array2[$key]); ?></span><span class="amount_t2"> <?php echo $n_epay_name_array[$key]; ?></span></span>
                                </a>
                            </li>
                            <?php
                            //}
                            } // foreach
                            ?>
                            <li class="token_block">
                                <!--                                <a href="..." title="coin bank">-->
                                <a href="javascript:;" onclick="view_message();">
                                    <div class="img2"><div><img src="images/logo2/ebnb.png" alt="ebnb" /></div></div>
                                    <span class="text">E-Binance Coin</span>
                                    <span class="amount"></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <!--                                <a href="..." title="coin bank">-->
                                <a href="javascript:;" onclick="view_message();">
                                    <div class="img2"><div><img src="images/logo2/elavie.png" alt="elavie" /></div></div>
                                    <span class="text">E-LaVie Coin</span>
                                    <span class="amount"></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="store_transactions_user.php" title="stores">
                                    <div class="img2"><div><img src="images/logo2/beepoints.png" alt="bee points" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['bee_points']) ? $langArr['bee_points'] : "Bee Points"; ?></span>
                                    <span class="amount"><span class="amount_t2">₩ </span><span class="amount_t1"><?php echo new_number_format($pointSum,2); ?></span></span>
                                </a>
                            </li>
                            <?php
                            //if ( !empty($_SESSION['admin_type']) && $_SESSION['admin_type'] == 'admin' ) {
                            ?><!--<li class="token_block">
									<a href="coupon_list.php" title="coupon">
										<div class="img3"><div><img src="images/logo2/coupon1.png" alt="coin ban" /></div></div>
										<span class="text"><?php echo !empty($langArr['coupon_fee_title']) ? $langArr['coupon_fee_title'] : "Purchase fee coupon"; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo $coupon_count; ?></span></span>
									</a>
								</li>-->
                            <?php //} ?>
                            <li class="token_block">
                                <a href="coin_bank.php" title="coin bank">
                                    <div class="img2"><div><img src="images/logo2/coin_bank.png" alt="coin ban" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['transfer']) ? $langArr['transfer'] : "Transfer"; ?></span>
                                    <span class="amount"></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="swiper-slide" id="swiper-slide2">

                        <ul class="index_token_block">
                            <li class="refresh">
                                <a href="javascript:;" title="refresh" onclick="refresh_move();">
                                    <img src="images/icons/refresh_white.png" alt="refresh" />
                                    <?php echo !empty($langArr['refresh']) ? $langArr['refresh'] : 'Refresh'; ?>
                                </a>
                            </li>
                            <?php
                            foreach ($n_full_name_array as $key=>$val) {
                                if ( !empty($getbalances[$key]) ) {
                                    $balances = $getbalances[$key];
                                } else {
                                    $balances = 0;
                                }
                                ?>
                                <li class="token_block">
                                    <a href="token.php?token=<?php echo $key; ?>" title="<?php echo strtoupper($key); ?>">
                                        <div class="img2"><div><img src="images/logo2/<?php echo $key; ?>.png" alt="<?php echo $key; ?>" /></div></div>
                                        <span class="text"><?php echo $n_full_name_array[$key]; ?></span>
                                        <span class="amount"><span class="amount_t1"><?php echo new_number_format($balances, $n_decimal_point_array[$key]); ?></span><span class="amount_t2"> <?php echo strtoupper($key); ?></span></span>
                                    </a>
                                </li>
                                <?php
                            } // foreach
                            ?>
                            <li class="token_block">
                                <a href="javascript:;" onclick="view_message();">
                                    <div class="img2"><div><img src="images/logo2/bnb.png" alt="ebnb" /></div></div>
                                    <span class="text">Binance Coin</span>
                                    <span class="amount"><span class="amount_t1"><?php echo new_number_format($bnbBalances, 8); ?></span><span class="amount_t2"> BNB</span></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="javascript:;" onclick="view_message();">
                                    <div class="img2"><div><img src="images/logo2/lavie.png" alt="lavie" /></div></div>
                                    <span class="text">LaVie Coin</span>
                                    <span class="amount"></span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="swiper-slide" id="swiper-slide3">

                        <ul class="index_token_block">
                            <li class="refresh">
                                <a href="javascript:;" title="refresh" onclick="refresh_move();">
                                    <img src="images/icons/refresh_white.png" alt="refresh" />
                                    <?php echo !empty($langArr['refresh']) ? $langArr['refresh'] : 'Refresh'; ?>
                                </a>
                            </li>
                            <!-- 점검 항목들 START -->
                            <li class="token_block">
                                <a href="exchange_ectc.php" title="ctc">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/ectc.png" alt="ctc" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['charging_ectc']) ? $langArr['charging_ectc'] : "Charging E-CTC"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">CTC</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-CTC</span></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="exchange_etoken.php?token=tp3" title="tp3">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/etp3.png" alt="tp3" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['charging_etp3']) ? $langArr['charging_etp3'] : "Charging E-TP3"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">TP3</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-TP3</span></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="exchange_etoken.php?token=mc" title="mc">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/emc.png" alt="mc" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['charging_emc']) ? $langArr['charging_emc'] : "Charging E-MC"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">MC</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-MC</span></span>
                                </a>
                            </li>
                            <!-- 점검 항목들 END-->
                            <li class="token_block">
                                <a href="exchange_etoken.php?token=usdt" title="usdt">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/eusdt.png" alt="usdt" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['charging_eusdt']) ? $langArr['charging_eusdt'] : "Charging E-USDT"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">USDT</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-USDT</span></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="exchange_etoken_eth.php" title="eth">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/eeth.png" alt="eth" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['charging_eeth']) ? $langArr['charging_eeth'] : "Charging E-ETH"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">ETH</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-ETH</span></span>
                                </a>
                            </li>

                            <li class="token_block">
                                <a href="exchange_etoken_re.php?token=ectc" title="ctc">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/ctc.png" alt="ctc" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">E-CTC</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">CTC</span></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="exchange_etoken_re.php?token=etp3" title="tp3">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/tp3.png" alt="tp3" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['buy_tp3']) ? $langArr['buy_tp3'] : "Charging TP3"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">E-TP3</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">TP3</span></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="exchange_etoken_re.php?token=emc" title="mc">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/mc.png" alt="mc" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['buy_mc']) ? $langArr['buy_mc'] : "Charging MC"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">E-MC</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">MC</span></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="exchange_etoken_re.php?token=eusdt" title="usdt">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/usdt.png" alt="usdt" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['buy_usdt']) ? $langArr['buy_usdt'] : "Charging USDT"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">E-USDT</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">USDT</span></span>
                                </a>
                            </li>
                            <li class="token_block">
                                <a href="exchange_etoken_re.php?token=eeth" title="eth">
                                    <!--                                <a href="javascript:;" onclick="view_message();">-->
                                    <div class="img2"><div><img src="images/logo2/eth.png" alt="eth" /></div></div>
                                    <span class="text"><?php echo !empty($langArr['buy_eth']) ? $langArr['buy_eth'] : "Charging ETH"; ?></span>
                                    <span class="amount"><span class="m_exchange_t1">E-ETH</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">ETH</span></span>
                                </a>
                            </li>
                            <!--<li class="token_block">
								<a href="exchange.php" title="ctc">
									<div class="img2"><div><img src="images/logo2/ctc.png" alt="ctc" /></div></div>
									<span class="text"><?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?></span>
									<span class="amount"><span class="m_exchange_t1">ETH</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">CTC</span></span>
								</a>
							</li>
							<li class="token_block">
								<a href="exchange_tp3.php" title="tp3">
									<div class="img2"><div><img src="images/logo2/tp3.png" alt="tp3" /></div></div>
									<span class="text"><?php echo !empty($langArr['buy_tp3']) ? $langArr['buy_tp3'] : "Charging TP3"; ?></span>
									<span class="amount"><span class="m_exchange_t1">ETH</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">TP3</span></span>
								</a>
							</li>-->



                            <!--
							<li class="token_block">
								<a href="exchange_ekrw_epay.php?token=ectc" title="ctc">
									<div class="img2"><div><img src="images/logo2/ctc.png" alt="ctc" /></div></div>
									<span class="text"><?php echo !empty($langArr['charging_ectc']) ? $langArr['charging_ectc'] : "Charging E-CTC"; ?></span>
									<span class="amount"><span class="m_exchange_t1">E-KRW</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-CTC</span></span>
								</a>
							</li>
							<li class="token_block">
								<a href="exchange_ekrw_epay.php?token=etp3" title="tp3">
									<div class="img2"><div><img src="images/logo2/tp3.png" alt="tp3" /></div></div>
									<span class="text"><?php echo !empty($langArr['charging_etp3']) ? $langArr['charging_etp3'] : "Charging E-TP3"; ?></span>
									<span class="amount"><span class="m_exchange_t1">E-KRW</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-TP3</span></span>
								</a>
							</li>
							<li class="token_block">
								<a href="exchange_ekrw_epay.php?token=emc" title="mc">
									<div class="img2"><div><img src="images/logo2/mc.png" alt="mc" /></div></div>
									<span class="text"><?php echo !empty($langArr['charging_emc']) ? $langArr['charging_emc'] : "Charging E-MC"; ?></span>
									<span class="amount"><span class="m_exchange_t1">E-KRW</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-MC</span></span>
								</a>
							</li>
							<li class="token_block">
								<a href="exchange_ekrw_epay.php?token=eeth" title="eth">
									<div class="img2"><div><img src="images/logo2/eth.png" alt="eth" /></div></div>
									<span class="text"><?php echo !empty($langArr['charging_eeth']) ? $langArr['charging_eeth'] : "Charging E-ETH"; ?></span>
									<span class="amount"><span class="m_exchange_t1">E-KRW</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-ETH</span></span>
								</a>
							</li>
							<li class="token_block">
								<a href="exchange_ekrw_epay.php?token=eusdt" title="usdt">
									<div class="img2"><div><img src="images/logo2/usdt.png" alt="usdt" /></div></div>
									<span class="text"><?php echo !empty($langArr['charging_eusdt']) ? $langArr['charging_eusdt'] : "Charging E-USDT"; ?></span>
									<span class="amount"><span class="m_exchange_t1">E-KRW</span><span class="m_exchange_img"><img src="images/icons/main_exchange_arrow.png" alt="exchange" /></span><span class="m_exchange_t2">E-USDT</span></span>
								</a>
							</li>
							-->



                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


<!-- /#page-wrapper -->

<?php
$n_scroll_menu = 'a';
include_once('includes/footer.php');
?>