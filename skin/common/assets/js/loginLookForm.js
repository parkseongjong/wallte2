$(window).on('load', function(){
    if(window.walletGlobalObj.authInfoType == 'realNameAuth'){
        init_orderid();
    }
    else if(window.walletGlobalObj.authInfoType == 'phoneCode'){
        //phone로 code를 받아야 하는 경우
        targetEl = $("input[name='target']");
        targetEl.intlTelInput({
            initialCountry: "kr",
            preferredCountries : ['cn','jp','us','kr'],
            geoIpLookup: function(callback) {
                $.get('https://ipinfo.io/json?token=6ad007f53defcc', function() {}, "jsonp").always(function(resp) { // 6ad007f53defcc
                    var countryCode = (resp && resp.country) ? resp.country : "";
                    callback(countryCode);
                });
            },
            utilsScript: "flag/build/js/utils.js" // just for formatting/placeholders etc
        });


    }

    //상황별 최종 확인 버튼 눌렀을 때 분기.
    $(document).on("click", ".inactive-account-home-button", function (){
        if(window.walletGlobalObj.authInfoType == 'realNameAuth') {
            auth_type_check();
            $('form[name="form_auth"]').submit();
        }
        else{
            $('#foreignerCodeUploadHtml').modal('show');
        }
    });

    let uploadEl = $('#foreignerCodeUpload');

    //상활별 인증코드 생성
    $(document).on("click", "#foreignerCodeUpload .generateCode", async function (){
        let uploadFormData = new FormData(uploadEl[0]);
        uploadFormData.append('type','generateCode');
        uploadFormData.append('localeCode','0000');

        if(window.walletGlobalObj.authInfoType == 'emailCode'){
            uploadFormData.append('authInfoType','emailCode');
            if(!emailValidCheck(uploadFormData.get('target'))){
                bsCommonAlert(walletGlobalObj.langData.emailCollectionJsString01);
                return false;
            }
        }
        else if(window.walletGlobalObj.authInfoType == 'phoneCode'){
            uploadFormData.append('authInfoType','phoneCode');

            //해외 유효성 체크... 필요
            if(!targetEl.intlTelInput("isValidNumber")){
                bsCommonAlert(walletGlobalObj.langData.phoneValidFail);
                return false;
            }

            let countryData = targetEl.intlTelInput("getSelectedCountryData");
            if(!intValidCheck(countryData.dialCode)){
                bsCommonAlert(walletGlobalObj.langData.countryValidFail);
                return false;
            }

            uploadFormData.append('localeCode',countryData.dialCode);
        }

        uploadFormData.append('verifyCode','0000');

        let data = await jsonTypeFormDataBuild(uploadFormData);
        $.ajax({
            cache : false,
            url : "https://cybertronchain.com/apis/wallet/sleepUser.php",
            headers : {
                Authorization: 'walletKey BE14273125KL'
            },
            type : 'POST',
            processData: true,
            contentType: 'application/json; charset=UTF-8',
            dataType : 'json',
            data : data,
            success : function(data, textStatus) {
                if(data.code == "00") {
                    bsCommonAlert(walletGlobalObj.langData.emailCollectionJsString02,'success');
                    uploadEl.find('input[name="verifyCode"]').prop('disabled',false);
                }
                else{
                    bsCommonAlert(data.data.msg);
                    console.log('정상 응답 아님');
                }
            },
            error : function(xhr, status) {
                console.log('연결 실패')
            }
        });
    });

    $(document).on("submit", "#foreignerCodeUpload", async function (){
        event.preventDefault();
        let thisEl = $(this);
        btnDisabledStatus(thisEl.find('button[type="submit"]'),true);
        let uploadFormData = new FormData($(this)[0]);
        uploadFormData.append('type','upload');
        uploadFormData.append('authInfoType','0000');
        uploadFormData.append('localeCode','0000');
        if(!intValidCheck(uploadFormData.get('verifyCode'))){
            bsCommonAlert(walletGlobalObj.langData.emailCollectionJsString03);
            btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
            return false;
        }
        let data = await jsonTypeFormDataBuild(uploadFormData);
        $.ajax({
            cache : false,
            url : "https://cybertronchain.com/apis/wallet/sleepUser.php",
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
                    bsCommonAlert(walletGlobalObj.langData.emailCollectionJsSuccess01,'success');
                    $('foreignerCodeUploadHtml').modal('hide');
                    let finish = setTimeout(function() {
                        window.location.replace('https://cybertronchain.com/wallet2/ajaxComplete.php?type=sleepRestoreFormComplete');
                    },3000);
                }
                else{
                    bsCommonAlert(data.data.msg);
                    btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
                    console.log(walletGlobalObj.langData.commonJsStringDange01);
                }
            },
            error : function(xhr, status) {
                console.log(walletGlobalObj.langData.commonJsStringDange02);
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

        auth_form.action = WALLET_URL+"/control.php/auth/kcp/phone/request"; // 인증창 호출 및 결과값 리턴 페이지 주소

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
