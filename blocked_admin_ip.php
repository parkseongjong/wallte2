<?php
// Page in use
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
require_once './config/config.php';
require_once WALLET_PATH.'/config/config_admin.php';
if (!defined('WALLET_ADMIN')) exit;

//config_admin은 admin 접근 제어를 위한 공통 파일 입니다... 해당 부분에서 composer autoload 선언 됨.
use wallet\common\Filter as walletFilter;
use wallet\common\Util as walletUtil;

$filter = walletFilter::getInstance();
$util = walletUtil::getInstance();

$db = getDbInstance();


//시간이 없으니 이 부분은 레거시 그대로 사용.
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    //$data_to_store = filter_input_array(INPUT_POST);
    //2021-08-05 XSS Filter by.ojt
    $targetPostData = array(
        'ip' => 'string',
        'uuid' => 'string',
        'description' => 'string'
    );

    $filterData = $filter->postDataFilter($_POST,$targetPostData);
    unset($targetPostData);

    $ipInfo = $db->where('ip',$filterData['ip'])
        ->getOne('blocked_admin_ips');
    if($ipInfo){
        $_SESSION['failure'] = "이미 존재하는 IP 입니다.";
        header('location: blocked_admin_ip.php');
        exit();
    }
    $last_id = $db->insert('blocked_admin_ips', ['datetime'=>$util->getDateSql(),'ip'=>trim($filterData['ip']),'uuid'=>$filterData['uuid'],'description'=>$filterData['description']]);
    $walletLogger->info('관리자 모드 > 관리자 페이지 접근 제어 > IP 추가 / 고유 ID :'.$last_id.' / IP :'.trim($filterData['ip']),['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'A']);
    header('location: blocked_admin_ip.php');
}
else{
    $walletLogger->info('관리자 모드 > 관리자 페이지 접근 제어 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}


//2021-08-05 XSS Filter by.ojt
$targetPostData = array(
    'page' => 'string',
    'filter_limit' => 'string'
);

$filterData = $filter->postDataFilter($_GET,$targetPostData);

//기존 변수를 그대로 써야해서.... 가변 변수로 선언..
foreach ($targetPostData as $key => $value){
    if($key == 'filter_limit'){
        if(key_exists($key,$filterData)){
            $pagelimit = $filterData[$key];
        }
        else{
            $pagelimit = false;
        }

    }
    else{
        if(key_exists($key,$filterData)){
            $$key = $filterData[$key];
        }
        else{
            $$key = false ;
        }
    }
}
unset($targetPostData);
/*
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');
*/
if(empty($pagelimit)) {
    $pagelimit = 20;
}
if (empty($page)) {
    $page = 1;
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("blocked_admin_ips", $page, ['id', 'ip','uuid','status','description','datetime']);
$total_pages = $db->totalPages;

include_once 'includes/header.php';
include_once WALLET_PATH.'/includes/adminAssets.php';
?>
    <!-- 가능하면 추후 adminAssets 로 공통 css 옮기기 by.ojt-->
    <link rel="stylesheet" href="css/admin.css"/>
    <link rel="stylesheet" href="css/lists.css" />

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-6">
                <h1 class="page-header">관리자 모드 접근 제어</h1>
            </div>
            <div class="col-lg-6" style="">
                ...
            </div>
        </div>
        <div class="row">
            <div class="alert alert-info">
                접근 제어에 추가 된 IP 만 관리자 모드에 접근 가능 합니다.
                접근 IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>
            </div>
        </div>
        <div class="row">
            <div class="well text-center filter-form">
                <form class="form form-inline" action="#" method="post">
                    <label for="input_search" >허용 IP 추가</label>
                    <input type="text" placeholder="IP" class="form-control" name="ip" />
                    <input type="text" placeholder="UUID" class="form-control" name="uuid" />
                    <input type="text" placeholder="요약" class="form-control" name="description" />

                    <input type="submit" value="등록" class="btn btn-primary">
                </form>
            </div>
        </div>
        <?php
            //레거시 사용 case 파악해서... 조정..
            include('./includes/flash_messages.php');

            if ($_SESSION['user_id'] != '93' && $_SESSION['user_id'] != '5137' && $_SESSION['user_id'] != '11863') {
                $_SESSION['failure'] = !empty($langArr['you_don_have_access']) ? $langArr['you_don_have_access'] : "You don't have access.";
                exit();
            }


        ?>
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered admin_table_new">
                <thead>
                <tr>
                    <th class="header">#</th>
                    <th>IP</th>
                    <th>UUID</th>
                    <th>생성일</th>
                    <th>상태</th>
                    <th>요약</th>
                    <th>..</th>
                </tr>
                </thead>
                <tbody>

                <?php
                    $totalCtcAmt = 0;
                    $i=1;
                ?>
               <?php foreach ($resultData as $row) : ?>
                    <tr data-id="<?php echo $row['id'] ?>">
                        <td class="align_center"><?php echo $i; ?></td>
                        <td class="align_center">
                            <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="blockedAdminIp">
                                <?php echo $walletMasking->reset()->pushIpMask()->getMasked(htmlspecialchars($row['ip'])); ?>
                            </span>
                        </td>
                        <td class="align_center">
                            <?php echo htmlspecialchars($row['uuid']); ?>
                        </td>
                        <td class="align_center">
                            <?php echo htmlspecialchars($row['datetime']) ?>
                        </td>
                        <td class="align_center">
                            <?php if($row['status'] == 1): ?>
                                <span class="badge badge-success blockedAdminIpStatusModify" data-type="statusModify">사용</span>
                            <?php else: ?>
                                <span class="badge badge-warning blockedAdminIpStatusModify" data-type="statusModify">사용 안함</span>
                            <?php endif; ?>
                        </td>
                        <td class="align_center">
                            <?php echo $row['description']; ?>
                        </td>
                        <td class="align_center">
                            <a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['id'] ?>" style="margin-right: 8px;">
                                <span class="glyphicon glyphicon-trash"></span>
                            </a>
                        </td>
                    </tr>

                    <!-- 개선 필요.... -->
                    <!-- Delete Confirmation Modal-->
                    <div class="modal fade" id="confirm-delete-<?php echo $row['id'] ?>" role="dialog">
                        <div class="modal-dialog">
                            <form action="delete_blocked_admin_ip.php" method="POST">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Confirm</h4>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="del_id" id = "del_id" value="<?php echo $row['id'] ?>">
                                        <p>Are you sure you want to delete this user?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-default pull-left">Yes</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                    <?php  $i++; endforeach; ?>
                </tbody>
            </table>
        </div>


        <!--Pagination links-->
        <div class="text-center">
            <?php
                //여기도 추후 pagenation 따로 뺄 수 있게..... 처리
                if (!empty($filterData)) {
                    //we must unset $_GET[page] if built by http_build_query function
                    unset($filterData['page']);
                    $http_query = "?" . http_build_query($filterData);
                } else {
                    $http_query = "?";
                }
                if ($total_pages > 1) {
                    echo '<ul class="pagination text-center">';
                    for ($i = 1; $i <= $total_pages; $i++) {
                        ($page == $i) ? $li_class = ' class="active"' : $li_class = "";
                        echo '<li' . $li_class . '><a href="' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';
                    }
                    echo '</ul></div>';
                }
            ?>
        </div>
    </div>

<?php include_once 'includes/footer.php'; ?>