$(function($) {
    $(document).on("click", ".maskingArea", function (){
        let thisEl = $(this);
        let userDataType = false;
        if(thisEl.data('userDataType') === undefined){
            userDataType = 'N';
        }
        else if(thisEl.data('userDataType') == 'S'){
            userDataType = 'S';
        }

        if(thisEl.hasClass('on')){
            return false;
        }
        let type = thisEl.data('type');

        //앨리먼트에서 kind build
        $.ajax({
            cache : false,
            url : "https://cybertronchain.com/apis/walletAdmin/unMask.php",
            headers : {
                Authorization: 'walletKey ABS521!^6ec44(*'
            },
            type : 'POST',
            processData: true,
            contentType: 'application/json; charset=UTF-8',
            dataType : 'json',
            data : JSON.stringify({'id':thisEl.data('id'), 'kind':type, 'userDataType':userDataType}),
            success : function(data, textStatus) {
                //console.log(data);
                if(data.code == "00") {
                    thisEl.html(data.data.unMaskData);
                    thisEl.addClass('on');
                    //console.log('정상 응답')
                }
                else{
                    //console.log('정상 응답 아님');
                }
            },
            error : function(xhr, status) {
                console.log('연결 실패')
            }
        });
    });

    $(document).on("click", ".blockedAdminIpStatusModify", function (){
        let thisEl = $(this);
        let id = thisEl.closest('tr').data('id');
        let type = thisEl.data('type');
        //앨리먼트에서 kind build
        $.ajax({
            cache : false,
            url : "https://cybertronchain.com/apis/walletAdmin/blockedAdminIp.php",
            headers : {
                Authorization: 'walletKey ABS521!^6ec44(*'
            },
            type : 'POST',
            processData: true,
            contentType: 'application/json; charset=UTF-8',
            dataType : 'json',
            data : JSON.stringify({'id':id, 'kind':type}),
            success : function(data, textStatus) {
                // console.log(data);
                if(data.code == "00") {
                    console.log('정상 응답');
                    if(data.data.status == 0){
                        thisEl.html('사용 안함');
                    }
                    else{
                        thisEl.html('사용');
                    }
                    bsCommonAlert('상태가 변경 되었습니다.','success');
                }
                else{
                    //console.log('정상 응답 아님');
                    bsCommonAlert('정상 응답이 아닙니다.','warning');
                }
            },
            error : function(xhr, status) {
                console.log('연결 실패');
            }
        });
    });
});
/*


    함수 목록


 */
