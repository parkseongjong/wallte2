<?php echo ($info['htmlHeader']); ?>
    <link rel="stylesheet" href="<?php echo $info['asstsUrl'].$this->asset('/css/common.css'); ?>">
    <link rel="stylesheet" href="<?php echo $info['asstsUrl'].$this->asset('/css/sleepRestoreForm.css'); ?>">
    <link rel="stylesheet" href="<?php echo (WALLET_URL.'/flag/build/css/intlTelInput.css'); ?>">

    <script src="<?php echo $info['asstsUrl'].$this->asset('/js/common.js'); ?>"></script>
    <script src="<?php echo $info['asstsUrl'].$this->asset('/js/sleepRestoreForm.js'); ?>"></script>
    <script src="<?php echo (WALLET_URL.'/flag/build/js/utils.js'); ?>"></script>
    <script src="<?php echo (WALLET_URL.'/flag/build/js/intlTelInput.js'); ?>"></script>
    <script>
        //global javascript variable.
        $(function($) {
            window.walletGlobalObj.authInfoType = '<?php echo $info['authInfo']['type']?>'
        });
    </script>

    <div class="inactive-account-container">
        <div class="inactive-account-wrapper">
            <div class="inactive-account-title"><?php echo $info['lang']['sleepString01']; ?></div>
            <div class="inactive-account-subtitle">
                <span><?php echo $info['lang']['sleepString02']; ?></span>
                <span><?php echo $info['lang']['sleepString03']; ?></span>
            </div>
            <div class="inactive-account-info-title"><?php echo $info['lang']['sleepString04']; ?></div>
            <div class="inactive-account-info-subtitle">
                <span><?php echo $info['lang']['sleepString05']; ?></span>
                <span><?php echo $info['lang']['sleepString06']; ?></span>
            </div>
            <div class="inactive-account-button-area">
                <button class="inactive-account-release-button">
                    <?php echo $info['lang']['sleepString07']; ?>
                </button>
                <button class="inactive-account-cancel-button">
                    <a href="<?php echo WALLET_URL;?>"><?php echo $info['lang']['commonString02']; ?></a>
                </button>
            </div>
        </div>
    </div>

<?php if($info['authInfo']['type'] == 'realNameAuth'):?>
    <iframe id="kcp_cert" name="kcp_cert" width="100%" height="700" frameborder="0" scrolling="yes" style="display: none;"></iframe>

    <form method="post" name="form_auth">
        <input type="hidden" name="ordr_idxx" id="auth_ordr_idxx" class="frminput" value="" readonly="readonly" maxlength="40"/>
        <input type="hidden" name="req_tx" value="cert" /><!-- 요청종류 -->
        <input type="hidden" name="cert_method" value="01" /><!-- 요청구분 -->
        <input type="hidden" name="web_siteid"   value="<?php echo $info['kcp']['siteid'] ?>" /><!-- 웹사이트아이디 : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
        <!-- <input type="hidden" name="fix_commid" value="KTF"/>--><!-- 노출 통신사 default 처리시 아래의 주석을 해제하고 사용하십시요 - SKT : SKT , KT : KTF , LGU+ : LGT-->
        <input type="hidden" name="site_cd" value="<?php echo $info['kcp']['sitecd'] ?>" /><!-- 사이트코드 : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
        <input type="hidden" name="Ret_URL" value="<?php echo $info['kcp']['returl'] ?>" /><!-- Ret_URL : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
        <input type="hidden" name="cert_otp_use" value="Y" /><!-- cert_otp_use 필수 ( 메뉴얼 참고) - Y : 실명 확인 + OTP 점유 확인 , N : 실명 확인 only -->
        <input type="hidden" name="cert_enc_use" value="Y" /><!-- cert_enc_use 필수 (고정값 : 메뉴얼 참고) -->
        <input type="hidden" name="cert_enc_use_ext" value="Y" />      <!-- 리턴 암호화 고도화 -->
        <input type="hidden" name="res_cd" value="" />
        <input type="hidden" name="res_msg" value="" />
        <input type="hidden" name="veri_up_hash" value="" /><!-- up_hash 검증 을 위한 필드 -->
        <input type="hidden" name="cert_able_yn" value="Y" /><!-- 본인확인 input 비활성화 -->
        <input type="hidden" name="web_siteid_hashYN" value="Y" /><!-- web_siteid 을 위한 필드 -->
        <input type="hidden" name="param_opt_1"  value="sleepUser" /> <!-- 가맹점 사용 필드 (인증완료시 리턴)-->
        <input type="hidden" name="param_opt_2"  value="" />
        <input type="hidden" name="param_opt_3"  value="" />
    </form>
<?php else: ?>
    <div id="foreignerCodeUploadHtml" class="modal fade in" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <form name="foreignerCodeUpload" id="foreignerCodeUpload" action="#" method="post" autocomplete="off">
                        <div class="form-group">
                            <input type="text" name="target" class="form-control" title="<?php echo $info['lang']['emailCollectionStringDanger03']; ?>" required="required" placeholder="">
                        </div>
                        <div class="form-group">
                            <input type="text" autocomplete="false" name="verifyCode" class="form-control mb-5" title="<?php echo $info['lang']['emailCollectionStringDanger03']; ?>" pattern="\d*" required="required" placeholder="" disabled>
                        </div>
                        <div class="form-group">
                            <span class="btn btn-block btn-info generateCode"><?php echo $info['lang']['commonString06']; ?></span>
                            <button type="submit" class="btn btn-block btn-primary"><?php echo $info['lang']['commonString07']; ?></button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $info['lang']['commonString02']; ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php echo ($info['htmlFooter']); ?>