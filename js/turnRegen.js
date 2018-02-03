var turnTime = 100,
    count = $('#turnCount').html(),
    regenLoop = setInterval(animateBar, 100);
    
function animateBar() {
    turnTime = Math.round(turnTime - 1);
    if (turnTime < 0) {
        turnCompleted();
    } 
    $('#turnPct').width(turnTime + "%");
}

function turnCompleted() {
    var d = new Date();
    timestamp = d.getTime()/1000|0;
    turnTime = 100;
    increase = $('#turnAmt').attr('data-amt');
    $.ajax({
        url: 'runmodule.php?module=api&mod=turnRegen&act=updateTurns',
        type: 'POST',
        data: {unixTimestamp: timestamp},
        success: function (data){
            count = parseInt(count) + parseInt(increase);
            $('#turnCount').html(count);
        },
        error: function(){
        }
    });
}