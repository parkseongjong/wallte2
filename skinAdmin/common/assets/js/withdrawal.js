document.addEventListener("DOMContentLoaded", function () {
})
$(function() {
});
$(window).on('load', function(){
    let rejectModalEl = $('#rejectModal')
    $(document).on("click", ".control", async function (){
        event.preventDefault();

        let thisEl = $(this);
        let type = thisEl.data('type');
        let targetId = thisEl.closest('tr').data('id');
        let data = false;
        if(type == 'reject'){
            rejectModalEl.modal('show');
            rejectModalEl.off("click",".modal-footer > .btn-warning");
            rejectModalEl.on("click", ".modal-footer > .btn-warning", async function (){
                if(!rejectModalEl.find('input[name="rejectMsg"]').val()){
                    bsCommonAlert('반려 사유를 입력해주세요.');
                    return false;
                }
                data = JSON.stringify({
                    'targetId':targetId,
                    'rejectMsg':rejectModalEl.find('input[name="rejectMsg"]').val()
                });
                withdrawalAjax(data,type);
            });
        }
        else if(type == 'withdrawal'){
            data = JSON.stringify({'targetId':targetId});
            withdrawalAjax(data,type);
        }
    });
});
/*

    함수 목록

 */
function withdrawalAjax(data, type){
    $.ajax({
        cache : false,
        url : "https://cybertronchain.com/apis/walletAdmin/withdrawal.php?type="+type,
        headers : {
            Authorization: 'walletKey ABS521!^6ec44(*'
        },
        type : 'POST',
        processData: true,
        contentType: 'application/json; charset=UTF-8',
        dataType : 'json',
        data :  data,
        success : function(data, textStatus) {
            console.log(data);
            if(data.code == "00") {
                bsCommonAlert('ok!!','success');
            }
            else{
                bsCommonAlert(data.data.msg);
                console.log('fail');
            }
            location.reload();
        },
        error : function(xhr, status) {
            console.log('error');
        }
    });
}