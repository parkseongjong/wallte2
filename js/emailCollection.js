$(window).on('load', function(){
    if(emailHtml){
        $('#emailHtml').modal('show');
    }
    //js 쪽도 전역 경로 전역변수가 필요함... 추후 고민
    //ajax 도 전역화 필요함.
    let emailHtmlUploadEl = $('#emailHtmlUpload');
    $(document).on("click", "#emailHtmlUpload .generateCode", async function (){
        let uploadFormData = new FormData(emailHtmlUploadEl[0]);
        uploadFormData.append('type','generateCode');
        uploadFormData.append('verifyCode','0000');
        if(!emailValidCheck(uploadFormData.get('email'))){
            bsCommonAlert(walletGlobalObj.langData.emailCollectionJsString01);
            return false;
        }
        let data = await jsonTypeFormDataBuild(uploadFormData);
        $.ajax({
            cache : false,
            url : "https://cybertronchain.com/apis/wallet/emailCollection.php",
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
                    emailHtmlUploadEl.find('input[name="verifyCode"]').prop('disabled',false);
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

    $(document).on("submit", "#emailHtmlUpload", async function (){
        event.preventDefault();
        let thisEl = $(this);
        btnDisabledStatus(thisEl.find('button[type="submit"]'),true);
        let uploadFormData = new FormData($(this)[0]);
        uploadFormData.append('type','upload');
        if(!intValidCheck(uploadFormData.get('verifyCode'))){
            bsCommonAlert(walletGlobalObj.langData.emailCollectionJsString03);
            btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
            return false;
        }
        let data = await jsonTypeFormDataBuild(uploadFormData);
        $.ajax({
            cache : false,
            url : "https://cybertronchain.com/apis/wallet/emailCollection.php",
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
                    bsCommonAlert(walletGlobalObj.langData.emailCollectionJsString04,'success');
                    //엘리먼트는 bs 버전이 낮아서 파괴 하지 않으나, 어차피 다른 페이지 가면 다시 노출 됨
                    $('#emailHtml').modal('hide');
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
/*


    함수 목록


 */
