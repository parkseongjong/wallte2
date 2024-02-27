$(function($) {
    window.walletGlobalObj = {};
    let walletLangType = (walletLang == 'ko')?'ko':'en';
    languageInfoApi(walletLangType)
        .then(function(result) {
            window.walletGlobalObj.langData = result;
        })
        .catch(function(result) {
            //reject는 따로 하지 않음.
        });
});
/*


    함수 목록


 */
/*


    공통 함수


 */
function getMobileOperatingSystem() {
    var userAgent = navigator.userAgent || navigator.vendor || window.opera;

    // Windows Phone must come first because its UA also contains "Android"
    if (/windows phone/i.test(userAgent)) {
        return "Windows Phone";
    }

    if (/android/i.test(userAgent)) {
        return "Android";
    }

    // iOS detection from: http://stackoverflow.com/a/9039885/177710
    if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
        return "iOS";
    }

    return "unknown";
}
function chatChannel() {
    event.preventDefault();
    let httpSchema = 'https://';
    let inappSchema = 'kakaolink://';
    let url = "pf.kakao.com/_axkxaxis/chat";
    //2021.10.15 일부 Android 11 버전 비즈니스 URL 접근 시 카카오톡 채팅창으로 바로 연결 되지 않는 현상으로 SDK 교체
    if(getMobileOperatingSystem() == 'iOS'){
        let anchor = document.createElement('a');
        anchor.setAttribute('href', httpSchema+url);
        anchor.setAttribute('target', '_black');

        let dispatch = document.createEvent('HTMLEvents')
        dispatch.initEvent('click', true, true);

        anchor.dispatchEvent(dispatch);
    }
    else if(getMobileOperatingSystem() == 'Android'){
        location.href = inappSchema+url;
    }
    else{
        location.href = httpSchema+url;
    }

}
function languageInfoApi(langType){
    return new Promise(function(resolve, reject) {
        $.ajax({
            cache : false,
            url : WALLET_URL+"/lang/"+langType+"/index.json",
            type : 'GET',
            processData: true,
            contentType: 'application/json; charset=UTF-8',
            dataType : 'json',
            data : null,
            success : function(data, textStatus) {
                resolve(data);
            },
            error : function(xhr, status) {
                bsCommonAlert('서버와 연결에 실패 하였습니다.', 'danger');
            }
        });
    });
}
function bsCommonAlert(msg = '경고!', type = 'warning' ){

    /*
        success
        danger
        warning
        info
    */
    var html = '';
    html += '<div id="bsCommonAlert" class="alert alert-'+type+' alert-dismissible show bsCommonAlert" role="alert">';
    html += '   <strong>'+msg+'</strong>';
    html += '   <button type="button" class="close" data-dismiss="alert" aria-label="Close">';
    html += '       <span aria-hidden="true">&times;</span>';
    html += '   </button>';
    html += '</div>';
    if(!$('#bsCommonAlert').length){
        $('body').append(html);
    }
}

/*


    target : 엘리먼트, text : 노출 text, type : true일때는 success / false warning


 */
function bsInnerTextDraw(target = false, text = false, type = false){
    if(target == false || text == false){
        return false;
    }
    target.removeClass('hidden');
    let encodeText = btoa(unescape(encodeURIComponent(text)));
    if(type == false){
        if(target.find('p').data('type') == 'success'){
            target.find('[data-type=success]').remove();
        }
        target.removeClass('alert-success');
        target.addClass('alert-warning');

        if(target.find('p[data-text]')){
            let check = false;
            target.find('p[data-text]').each(async function (index,item){
                if($(item).data('text') == encodeText){
                    check = true;
                    return false;
                }
            });
            if(check == false){
                target.append('<p data-type="warning" data-text="'+encodeText+'">'+text+'</p>');
            }
        }
    }
    else{
        target.addClass('alert-success');
        target.removeClass('alert-warning');
        target.html('<p data-type="success" data-text="'+encodeText+'">'+text+'</p>');
    }

}
function formDataToJson($data){
    let unindexedarray = $data.serializeArray();
    let indexedArray = {};

    $.map(unindexedarray, function(n, i){
        indexedArray[n['name']] = n['value'];
    });

    return indexedArray;
}

function btnDisabledStatus(target = false,type = false){
    if(!target) {
        return false;
    }
    else{
        if(type){
            target.attr('disabled',true);
        }
        else{
            target.attr('disabled',false);
        }
    }
}

// not callback foreach
async function asyncForEach(array, callback) {
    for (let index = 0; index < array.length; index++) {
        const result = await callback(array[index], index, array);
    }
}

/*
    targetObj = [
            {'target':'opt_id[]','postName':'optId'},
            {'target':'opt_stock_qty[]','postName':'optStockQty'},
            {'target':'opt_use[]','postName':'optUse'},
            {'target':'opt_price[]','postName':'optPrice'},
    ];
    원하는 post key 값으로 build 할 때,
 */
async function formDataBuild(formData,targetObj){
    await asyncForEach(targetObj, async function(item, index) {
        formData.delete(item.target);
        await asyncForEach($('input[name="' + item.target + '"]'), function (item2, index2) {
            formData.append(item.postName, $(item2).val());
        });
    });
}

// json 타입으로 배열 form build
async function jsonTypeFormDataBuild(formData){
    let tempObj = {};
    let pattern = new RegExp("[\[\]]$");
    for await(peer of formData.entries()){
        if(pattern.test(peer[0])){
            if(Array.isArray(tempObj[peer[0]])){
                tempObj[peer[0]].push(
                    peer[1]
                );
            }
            else{
                tempObj[peer[0]] = new Array();
                tempObj[peer[0]].push(
                    peer[1]
                );
            }

        }
        else{
            tempObj[peer[0]] = peer[1];
        }

    }
    return JSON.stringify(tempObj);
}

//formDataBuild,jsonTypeFormDataBuild 동시에 수행하는 function
async function jsonTypeFormDataAndFormDataBuild(formData,targetObj){
    await asyncForEach(targetObj, async function(item, index) {
        formData.delete(item.target);
        console.log(item.postName);
        await asyncForEach($('[name="' + item.target + '"]'), function (item2, index2) {
            formData.append(item.postName, $(item2).val());
        });
    });

    let tempObj = {};

    for await(peer of formData.entries()){
        let comparison = await targetObj.find(function(element, index, array){
            return (element.postName == peer[0])?true:false;
        });
        if(comparison){
            if(Array.isArray(tempObj[peer[0]])){
                tempObj[peer[0]].push(
                    peer[1]
                );
            }
            else{
                tempObj[peer[0]] = new Array();
                tempObj[peer[0]].push(
                    peer[1]
                );
            }
        }
        else{
            tempObj[peer[0]] = peer[1];
        }
    }
    return JSON.stringify(tempObj);
}

function setCookie(name, value, expirehours, domain)
{
    var today = new Date();
    today.setTime(today.getTime() + (60*60*1000*expirehours));
    document.cookie = name + "=" + escape( value ) + "; path=/; expires=" + today.toGMTString() + ";";
    if (domain) {
        document.cookie += "domain=" + domain + ";";
    }
}

function getCookie(name)
{
    var find_sw = false;
    var start, end;
    var i = 0;

    for (i=0; i<= document.cookie.length; i++)
    {
        start = i;
        end = start + name.length;

        if(document.cookie.substring(start, end) == name)
        {
            find_sw = true
            break
        }
    }

    if (find_sw == true)
    {
        start = end + 1;
        end = document.cookie.indexOf(";", start);

        if(end < start)
            end = document.cookie.length;

        return unescape(document.cookie.substring(start, end));
    }
    return "";
}

function deleteCookie(name)
{
    var today = new Date();

    today.setTime(today.getTime() - 1);
    var value = getCookie(name);
    if(value != "")
        document.cookie = name + "=" + value + "; path=/; expires=" + today.toGMTString();
}


/*


    유효성 체크


 */
// 일치하면 true, 아니면 false
function emailValidCheck(email){
    let regex = /([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/g;
    if(regex.test(email)){
        return true;
    }
    else{
        return false;
    }
}

// 일치하면 true, 아니면 false
function passwordValidCheck(pw){
    let regex = /((?=.*[a-z])(?=.*[0-9])(?=.*[$@$!%*#?&])(?=.*[^a-zA-Z0-9])(?!.*(admin|root)).{8,})/m;
    if(regex.test(pw)){
        return true;
    }
    else{
        return false;
    }
}

// 일치하면 true, 아니면 false
function intValidCheck(int){
    let regex = /^[0-9]/g;
    if(regex.test(int)){
        return true;
    }
    else{
        return false;
    }
}
