<?php
// Page in use
if (!defined('WALLET_USER')) exit;
    //등록된 사용자 서브 메뉴 공통 파일.

    //2021.10.27 By OJT
    //ㅇ 관리자모드 > 등록된 사용자 : ctc not approved users, 주소변경 신청목록, 수수료 변환신청 결제리스트 등 사용하지 않는 메뉴는 비노출 처리 필요
    $adminUserMenuArray = array(
        ['fileName'=>'admin_users','langKey'=>'registered_users'],
        ['fileName'=>'admin_users_sleep','langKey'=>'user_list_sleep'],
        ['fileName'=>'admin_adminlist','langKey'=>'admin_list'],
        ['fileName'=>'admin_stores','langKey'=>'store_list'],
        ['fileName'=>'admin_user_exchange','langKey'=>'coinIBT_name'],
        //['fileName'=>'admin_ctc_not_approved','langKey'=>'ctc_not_approved_users'],
        //['fileName'=>'admin_fee_list','langKey'=>'change_fee_admin_tab_name'],
        //['fileName'=>'admin_change_address_users','langKey'=>'change_address_text1'],
        ['fileName'=>'admin_users_fee_type1','langKey'=>'admin_users_fee_type1_title'],
        //['fileName'=>'admin_users_fee_payment','langKey'=>'coupon_payment_admin_list'],
        ['fileName'=>'admin_users_epay','langKey'=>'admin_user_epay_list']
    );
?>
<ul class="nav nav-tabs">
    <?php foreach($adminUserMenuArray as $key => $value): ?>
        <?php if(basename($_SERVER['SCRIPT_NAME']) == $value['fileName'].'.php'): ?>
            <li class="active">
        <?php else: ?>
            <li>
        <?php endif; ?>
                <a href="<?php echo WALLET_URL ?>/<?php echo $value['fileName']?>.php">
                    <?php echo !empty($langArr[$value['langKey']]) ? $langArr[$value['langKey']] : "언어 파일 설정 필요"; ?>
                </a>
            </li>
    <?php endforeach; ?>
</ul>
<?php
    unset($adminUserMenuArray);
?>