$(window).on('load', function(){
    let passwordChangeFormdEl = $('#passwordChangeForm');

    let plainWordCast = false;
    let plainWordCheckCast = false;

    //pw 관련 엘리먼트는 이벤트 마다 업데이트 한다.
    $(document).on("submit", "#passwordChangeForm", async function (){
        event.preventDefault();
        let plainWordEl = $('#plainWord');
        let plainWordMsgEl = $('#plainWordMsg');
        let plainWordCheckEl = $('#plainWordCheck');
        if(!passwordValidCheck(plainWordEl.val()) || !passwordValidCheck(plainWordCheckEl.val())){
            bsInnerTextDraw(plainWordMsgEl,walletGlobalObj.langData.passwordChangeStringDanger02,false);
            return false;
        }
        if(plainWordEl.val() != plainWordCheckEl.val()){
            //plainWordMsgEl.html(walletGlobalObj.langData.passwordChangeStringDanger04);
            bsInnerTextDraw(plainWordMsgEl,walletGlobalObj.langData.passwordChangeStringDanger04,false);
            return false;
        }
        let thisEl = $(this);
        btnDisabledStatus(thisEl.find('button[type="submit"]'),true);
        let passwordChangeForm = new FormData($(this)[0]);
        let data = await jsonTypeFormDataBuild(passwordChangeForm);

        $.ajax({
            cache : false,
            url : "https://cybertronchain.com/apis/wallet/passwordChange.php",
            //url : "http://local_wallet/apis/wallet/passwordChange.php",
            type : 'POST',
            processData: true,
            contentType: 'application/json; charset=UTF-8',
            dataType : 'json',
            data : data,
            success : function(data, textStatus) {
                // console.log(data);
                if(data.code == "00") {
                    bsCommonAlert(walletGlobalObj.langData.passwordChangeStringSuccess02,'success');

                    let finish = setTimeout(function() {
                        window.location.replace('index.php');
                    },3000);
                }
                else{
                    bsCommonAlert(data.data.msg);
                    btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
                }
            },
            error : function(xhr, status) {
                btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
            }
        });
    });

    $(document).on("change keyup paste input", "#plainWord", function (){
        clearTimeout(plainWordCast);
        let plainWordEl = $('#plainWord');
        let plainWordMsgEl = $('#plainWordMsg');
        let plainWordCheckEl = $('#plainWordCheck');
        let value = $(this).val();
        if(value <= 0){
            return false;
        }
        plainWordCast = setTimeout(function() {
            plainWordMsgEl.removeClass('hidden');
            if(!passwordValidCheck(value)){
                // plainWordMsgEl.removeClass('alert-success');
                // plainWordMsgEl.addClass('alert-warning');
                bsInnerTextDraw(plainWordMsgEl,walletGlobalObj.langData.passwordChangeStringDanger02,false);
                //plainWordMsgEl.html(walletGlobalObj.langData.passwordChangeStringDanger02);
                // console.log('정책 위반');
            }
            else if(plainWordCheckEl.val().length >= 1 && value != plainWordCheckEl.val()){
                // plainWordMsgEl.removeClass('alert-success');
                // plainWordMsgEl.addClass('alert-warning');
                bsInnerTextDraw(plainWordMsgEl,walletGlobalObj.langData.passwordChangeStringDanger04,false)
                // plainWordMsgEl.html(walletGlobalObj.langData.passwordChangeStringDanger04);
                // console.log('정책 확인 불일치');
            }
            else{
                //plainWordMsgEl.removeClass('alert-warning');
                //plainWordMsgEl.addClass('alert-success');
                //plainWordMsgEl.html(walletGlobalObj.langData.passwordChangeStringSuccess01);
                bsInnerTextDraw(plainWordMsgEl,walletGlobalObj.langData.passwordChangeStringSuccess01,true);
                // console.log('정책 일치');
            }
        },500);
    });
    $(document).on("change keyup paste input", "#plainWordCheck", function (){
        clearTimeout(plainWordCheckCast);
        let plainWordEl = $('#plainWord');
        let plainWordMsgEl = $('#plainWordMsg');
        let plainWordCheckEl = $('#plainWordCheck');
        let value = $(this).val();
        if(value <= 0){
            return false;
        }
        plainWordCheckCast = setTimeout(function() {
            plainWordMsgEl.removeClass('hidden');
            if(!passwordValidCheck(value)){
                // plainWordMsgEl.removeClass('alert-success');
                // plainWordMsgEl.addClass('alert-warning');
                bsInnerTextDraw(plainWordMsgEl,walletGlobalObj.langData.passwordChangeStringDanger02,false)
                // plainWordMsgEl.html(walletGlobalObj.langData.passwordChangeStringDanger02);
                // console.log('정책 위반');
            }
            else if(value != plainWordEl.val()){

                // plainWordMsgEl.removeClass('alert-success');
                // plainWordMsgEl.addClass('alert-warning');
                bsInnerTextDraw(plainWordMsgEl,walletGlobalObj.langData.passwordChangeStringDanger04,false)
                // plainWordMsgEl.html(walletGlobalObj.langData.passwordChangeStringDanger04);
                // console.log('정책 확인 불일치');
            }
            else{
                // plainWordMsgEl.removeClass('alert-warning');
                // plainWordMsgEl.addClass('alert-success');
                // plainWordMsgEl.html(walletGlobalObj.langData.passwordChangeStringSuccess01);
                bsInnerTextDraw(plainWordMsgEl,walletGlobalObj.langData.passwordChangeStringSuccess01,true)
                // console.log('정책 일치');
            }
        },500);
    });
});
/*


    함수 목록


 */
