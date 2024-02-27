document.addEventListener("DOMContentLoaded", function () {
    let loadEl = $('#loading-o');
    if(loadEl.length > 0){
        loadEl.removeClass('none');
    }
})
// $(function() {
// });
$(window).on('load', function(){
    let loadEl = $('#loading-o');
    if(loadEl.length > 0){
        loadEl.addClass('none');
    }
});