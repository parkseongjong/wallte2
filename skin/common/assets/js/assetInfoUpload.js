document.addEventListener("DOMContentLoaded", function () {
    console.log("loaded");
    const uploadInput = document.querySelector("#upload-input");

    uploadInput.addEventListener("change", function () {
        console.log("uploadInput change", this.files);
        const uploadLabelInput = document.querySelector(
            ".upload-label-input"
        );

        const uploadFile = this.files[0];
        if (!uploadFile) {
            uploadLabelInput.innerText = "파일을 업로드 해주세요";
            uploadLabelInput.style.color = null;
            return;
        }

        const filename = uploadFile.name;

        uploadLabelInput.innerText = filename;
        uploadLabelInput.style.color = "#000";
    });
});
$(window).on('load', function(){
    $(document).on("submit", "#assetInfoUpload", async function () {
        event.preventDefault();
        let thisEl = $(this);
        btnDisabledStatus(thisEl.find('button[type="submit"]'),true);

        let uploadFormData = new FormData($(this)[0]);
        let fileElFile = $('#upload-input')[0].files[0];
        if(!fileElFile){
            bsCommonAlert('파일을 첨부해 주세요.');
            btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
            return false;
        }

        $.ajax({
            cache: false,
            url : "https://cybertronchain.com/apis/wallet/withdrawal.php?type=upload",
            headers : {
                Authorization: 'walletKey BE14273125KL'
            },
            type: 'POST',
            processData: false,
            //contentType : 'multipart/form-data; charset=UTF-8',
            contentType: false,
            dataType: 'json',
            data: uploadFormData,
            success : function(data, textStatus) {
                console.log(data);
                if(data.code == "00") {
                    bsCommonAlert(walletGlobalObj.langData.emailCollectionJsSuccess01,'success');
                    //btnDisabledStatus(thisEl.find('button[type="submit"]'),false);
                    location.replace(WALLET_URL);
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

function onClickCompleteDeleteAccount() {
    console.log("onClickCompleteDeleteAccount");
    const uploadFile = document.querySelector("#upload-input").files[0];
    console.log("file? ", uploadFile);
    if (!uploadFile) {
        console.log("file not found");
        return;
    }
}