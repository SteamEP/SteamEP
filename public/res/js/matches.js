var toggle = false;
function hideOffline() {
    if (toggle) {
        $('.status').each(function() {
            $(this).parent().show();
        });
        toggle = false;
    } else {
        $('.status').each(function() {
            if ($(this).html() == 'Offline')
                $(this).parent().hide();
        });
        toggle = true;
    }
}
function addIgnore(url, steamid) {
    $.ajax({url: url}).done(function(){
        $('#'+steamid).fadeTo(1000, 0.10);
    });
}
function addFriend(steamid) {
    window.location.href = 'steam://friends/add/' + steamid;   
}
