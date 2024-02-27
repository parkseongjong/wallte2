<?php
/*
 *
 *  by. OJT 2021.05.27 사용 중인 페이지 입니다.
 *
 *
 */

if (!defined('WALLET_USER')) exit;
$db = getDbInstance();
if (isset($_SESSION['user_id'])) {
    //db에 where로 조회 할 수 있지만, 일단 회원 정보 자체를 조회 한다.
    $userInfo = $db->where("id", $_SESSION['user_id'])->where("register_with", 'phone')->getOne('admin_accounts', 'wallet_phone_email');
    if($userInfo){
        if (!$userInfo['wallet_phone_email']) {
            $userInfo = false;
        }
        else {
            //phone 계정이며 이미 email을 등록 했다면 true 리턴
            $userInfo = true;
        }
    }
    else{
        //email계정 인 경우 email 있다고 가정,
        $userInfo = true;
    }
}
else {
    //session이 없는 경우, user_id가 존재하는게 아닌 경우에도 eamil이 있다고 가정하고 true를 리턴
    $userInfo = true;
}
//종속된 include 파일이 많아서 일단.. 하드코딩으로 두고 추후 확인?
if (!$userInfo) {
    $emailHtml = '
        <div id="emailHtml" class="modal fade in" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-body">
                <form name="emailHtmlUpload" id="emailHtmlUpload" action="#" method="post" autocomplete="off">
                    <div class="alert alert-info" role="alert">
                        <p>' . $langArr['emailCollectionString01'] . '</p>
                        <p>' . $langArr['emailCollectionString02'] . '</p>
                        <p>' . $langArr['emailCollectionString03'] . '</p>
                        <p>' . $langArr['emailCollectionString04'] . '</p>
                        <p>' . $langArr['emailCollectionString05'] . '</p>
                        <p>' . $langArr['emailCollectionString06'] . '</p>
                    </div>
                    <div class="alert alert-danger" role="alert">
                        <p>' . $langArr['emailCollectionStringDanger01'] . '</p>
                        <p>' . $langArr['emailCollectionStringDanger02'] . '</p>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" title="' . $langArr['emailCollectionStringDanger03'] . '" required="required" placeholder="' . $langArr['emailCollectionString07'] . '">
                    </div>
                    <div class="form-group">
                        <input type="text" autocomplete="false" name="verifyCode" class="form-control mb-5" title="' . $langArr['emailCollectionStringDanger04'] . '" pattern="\d*" required="required" placeholder="' . $langArr['emailCollectionString08'] . '" disabled>
                    </div>
                    <div class="form-group">
                        <span class="btn btn-block btn-info generateCode">' . $langArr['emailCollectionString09'] . '</span>
                        <button type="submit" class="btn btn-block btn-primary">' . $langArr['emailCollectionString10'] . '</button>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">' . $langArr['emailCollectionString11'] . '</button>
              </div>
            </div>
          </div>
        </div>
    ';
}
else {
    $emailHtml = false;
}
unset($userInfo);
?>
