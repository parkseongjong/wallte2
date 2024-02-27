$(window).on('load', function(){
    $(document).on("submit", "#passwordCheckForm", async function (){
        event.preventDefault();
        let thisEl = $(this);
        btnDisabledStatus(thisEl.find('button[type="submit"]'),true);

        let uploadFormData = new FormData($(this)[0]);
        if(!$('#agree').is(':checked')){
            bsCommonAlert(walletGlobalObj.langData.commonApiStringDanger05);
            btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
            return false;
        }
        if(!uploadFormData.get('password')){
            bsCommonAlert(walletGlobalObj.langData.passwordChangeStringDanger01);
            btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
            return false;
        }
        let data = await jsonTypeFormDataBuild(uploadFormData);
        $.ajax({
            cache : false,
            //url : "https://cybertronchain.com/apis/wallet/.php",
            url : "https://cybertronchain.com/apis/wallet/withdrawal.php?type=passwordCheck",
            headers : {
                Authorization: 'walletKey BE14273125KL'
            },
            type : 'POST',
            processData: true,
            contentType: 'application/json; charset=UTF-8',
            dataType : 'json',
            data : data,
            success : function(data, textStatus) {
                console.log(data);
                if(data.code == "00") {
                    //자산이 있는지 없는지에 따라 분기로 페이지 이동..
                    if(data.otherCode == 10){
                        let finish = setTimeout(function() {
                            location.replace(WALLET_URL+'/control.php/withdrawal/assetinfo')
                        },3000);
                    }
                    else if(data.otherCode == 20){
                        location.replace(WALLET_URL+'/control.php/withdrawal/complete');
                    }
                    //bsCommonAlert('ok!!','success');
                    btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
                }
                else{
                    bsCommonAlert(data.data.msg);
                    btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
                    console.log('fail');
                }
            },
            error : function(xhr, status) {
                console.log('error');
                btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
            }
        });
    });

});

function auth_type_check() {

    var auth_form = document.form_auth;

    if (auth_form.ordr_idxx.value == '') {
        return false;
    }
    else {

        if( navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1 || navigator.userAgent.indexOf("android-web-view") > - 1 || navigator.userAgent.indexOf("ios-web-view") > - 1 ) {
            auth_form.target = "kcp_cert";
            $('.inactive-account-container').hide();
            document.getElementById( "kcp_cert").style.display = "";
        }
        else {
            var return_gubun;
            var width  = 410;
            var height = 500;

            var leftpos = screen.width  / 2 - ( width  / 2 );
            var toppos  = screen.height / 2 - ( height / 2 );

            var winopts  = "width=" + width   + ", height=" + height + ", toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
            var position = ",left=" + leftpos + ", top="    + toppos;
            var AUTH_POP = window.open('','auth_popup', winopts + position);

            auth_form.target = "auth_popup";
        }

        auth_form.action = "./auth.pro.req_sleepUser.php"; // 인증창 호출 및 결과값 리턴 페이지 주소

        return true;
    }
}

// 본인인증 : 요청번호 생성 예제 ( up_hash 생성시 필요 )
function init_orderid()
{
    var today = new Date();
    var year  = today.getFullYear();
    var month = today.getMonth()+ 1;
    var date  = today.getDate();
    var time  = today.getTime();

    if (parseInt(month) < 10)
    {
        month = "0" + month;
    }

    var vOrderID = year + "" + month + "" + date + "" + time;
    document.form_auth.ordr_idxx.value = vOrderID;
}
