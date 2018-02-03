/*List of changes coming to the commentary:
-New form (send with send, or tap the 'add' button.
-Preview field will apply even on saved messages.
-No requirement on commentary length (novels allowed)
-typing /ooc will switch to OOC, typing /rp will switch to the RP.
*/
var chatLoop,
    chatSection = localStorage['chatSection'],
    myComment = [],
    commentaryMessages = [],
    lastCommentID = 0,
    chatWhosTyping = '<br /><br />',
    isEditing = false,
    chatLastKeypress = 0,
    oocOffset = 0, 
    rpOffset = 0;

/* API Module */
function apiLink(module, action)
{
    return 'runmodule.php?module=api&mod=' + module + '&act=' + action;
}

/* Districts Module */
function districtNavigate(district)
{
    $.post(apiLink('villageDistricts', 'getDescription'),
        {thisDistrict: district},
        function(data, status) {
            if (status == 'error') {
                connectionError();
            }
            setChatSection(district);
            getAllChatData(true)
            $('[name=district').html(data['description']);
        }
    );
}

/* jQuery Commentary Module */
$(document).ready(function() {
    getAllChatData();
    startChat();
    $('#charsleftinsertcommentary, #previewtextinsertcommentary').remove();
    $('#inputinsertcommentary').val(localStorage['commentaryForm']);
    $('input[name=section]').
        val(chatSection).
        after(
            "<div id='charsleftinsertcommentary' hidden></div>" +
            "<div id='previewtextinsertcommentary'></div>"
        );
    if (localStorage['commentaryForm'].length > 0
        && typeof previewtextinsertcommentary != 'undefined') {
        previewtextinsertcommentary(localStorage['commentaryForm'], 1000);
    }
});

$('#inputinsertcommentary').keypress(function(e) {
    //chatLastKeypress = unixTimestamp
    //Compare keypresses, post to typing area if less than three seconds.
    if (e.keyCode == 13) {
        e.preventDefault();
        if ($(this).val().trim().length > 0) {
            postChatMessage($(this).val(), chatSection);
        }
        return false;
    }
});

$('#inputinsertcommentary').keyup(function(e) {
    localStorage.setItem('commentaryForm', $(this).val());
    commentaryLength = $(this).val().length;
    if (($(this).val() == '/edit' || (e.keyCode == 38 && commentaryLength < 1)) && !isEditing) {
        isEditingMessage($(this), e);
        $('input[name=section]').before()
    }
    if (commentaryLength < 1 && e.keyCode != 38) {
        notEditingMessage();
        return;
    }
    $('#previewtextinsertcommentary').show();
    if (typeof colorFilter == 'function') {
        colorFilter();
    }
});

$('#submitChat').on('click tap', function(e) {
    form = $('#inputinsertcommentary');
    console.log(form.val());
    e.preventDefault();
    if (form.val().length > 0) {
        postChatMessage(form.val(), chatSection);
    }
    return false;
});

$('#message-subject').on('click tap', editTitle);

$('#message-reply-form')
.on('mouseover focus', function() {
    $('#message-reply').css('background-color', '#111');
})
.on('mouseout blur', function() {
    if (document.activeElement.name != 'reply') {
        $('#message-reply').css('background-color', '#222');
    }
});

$('a[href="runmodule.php?module=faq"]').on('click tap', function(e) {
    $('.modal').slideUp().attr('src', $(this).attr('href'));
    e.preventDefault;
    return false;
});

$('.modal').slideUp().attr('src', $(this).attr('href'));

$('tr[name="mail-message-users"]').on('click tap', function() {
    $('.message-to').html($(this).data('name') + '</span>');
    $('#new-message').css('display', 'block');
    $('#to').val($(this).data('acctid'));
});

$('tr[name="messages"]').on('click tap', function() {
    id = $(this).data('originator');
    window.location = 'runmodule.php?module=mail&op=view&id=' + id + '#last';
});

$('.contact a').on('click tap', function() {
    $(this).children().removeClass('glow');
    console.log('HELLO!');
    console.log($(this).children());
});

function sanitizeMessage(message)
{
    return message.replace(/(\<.*?\>)/ig, '').replace(/\`\`/ig, '` ');
}

function getAllChatData(refresh)
{
    $.get(
        apiLink('jQueryCommentary', 'getAllChatData'),
        function(data, status) {
            myComment['comment'] = data['myComment']['comment'];
            myComment['commentid'] = data['myComment']['commentid'];
            myComment['section'] = data['myComment']['section'];
            chatSection = data['chatSection'];
            if (refresh != false) {
                getChatMessages();
            }
        }
    );
}

function freezeChat() {
    clearInterval(chatLoop);
    chatLoop = undefined;
}

function startChat() {
    if (typeof chatLoop == 'number' || typeof chatLoop == 'object') {
        console.log('Chat loop is already running, not starting chat twice.');
        return false;
    }
    chatLoop = setInterval(
        function () {
            getChatMessages();
        },
        3000
    );
}

function getChatSection()
{
    return chatSection.trim();
}

function setChatSection(newSection)
{
    $.post(
        apiLink('jQueryCommentary', 'setChatSection'),
        {section: newSection},
        function (data, status) {
            if (status == 'error') {
                connectionError();
                return;
            }
            chatSection = data['section'].trim();
            localStorage.setItem('chatSection', chatSection);
            $('input[name=section]').val(chatSection);
        }
    );
}

function getLastMessage()
{
    $.get(
        apiLink('jQueryCommentary', 'getLastMessage'),
        function(data) {
            myComment = data;
            $('#inputinsertcommentary').prop('disabled', false).focus();
            $('#disabled').remove();
        }
    );
    return myComment;
}

function isEditingMessage(form, e)
{
    localStorage.setItem('commentaryForm', '');
    isEditing = true;
    $(form).val(myComment['comment']);
    $('input[name=section]').
        val(myComment['section']).
        before(
            "<div id='editing'>\
            To cancel edit mode, clear the commentary field.\
            </div>"
        );
}

function notEditingMessage()
{
    isEditing = false;
    setChatSection(chatSection);
    clearCommentaryForm();
}

function editMessageOf(id)
{
    $.post(
        apiLink('jQueryCommentary', 'editMessage'),
        {commentid: id},
        function (data) {
            if (data.length < 1) {
                return false;
            }
            isEditing = true;
            myComment = data;
            $('#inputinsertcommentary').val(data['comment']).focus();
            $('#editing').remove();
            $('input[name=section]').
                val(data['section']).
                before(
                    "<div id='editing'>\
                    To cancel edit mode, clear the commentary field.\
                    </div>"
                );
        }
    );
}

function postChatMessage(message, channel)
{
    if (isEditing) {
        var editedID = myComment['commentid'];
    }
    $.post(apiLink('jQueryCommentary', 'postMessage'),
        {comment: message, section: channel, edited: editedID},
        function(data, status) {
            console.log(data);
            if (status == 'error') {
                connectionError();
                return;
            }
            if (typeof chatLoop == 'undefined') {
                startChat();
            }
            getAllChatData();
            //getChatMessages(offsets['rp'], offsets['ooc']);
            /*
            -Clear the 'Who's typing' section of our name. notMessaging();
            */
            localStorage.setItem('commentaryForm', '');
            notEditingMessage();
        }
    );
}

function removeChatMessage(id)
{
    $.post(
        apiLink('jQueryCommentary', 'removeMessage'),
        {commentid: id},
        function(data) {
            console.log(data);
            if (data.length < 1) {
                return false;
            }
            getChatMessages();
        }
    );
}

function getChatMessages()
{
    var messages = [];
    $.post(
        apiLink('jQueryCommentary', 'getChatMessages'),
        {page: rpOffset, ooc: oocOffset, ret: location.pathname + location.search},
        function(data, status) {
            if (status == 'error') {
                commentaryMessages = [];
                connectionError();
            }
            var messages = [], newLast;
            commentaryMessages = data;
            if (data.length < 2) {
                $('#jQueryChatForm').remove();
                $('#jQuery-chat').html(data[0]['formattedComment']);
                return false;
            }
            newLast = $(commentaryMessages).last()[0]['id'];
            if (localStorage['lastCommentID'] < newLast) {
                localStorage.setItem('lastCommentID', newLast);
            }
            $.each(commentaryMessages, function(index, data) {
                messages.push(data['formattedComment']);
            });
            $('#jQuery-chat').html(messages.join(''));
            if (rpOffset > 0) {
                $('#nextrp').val('Next >');
            }
            if (oocOffset > 0) {
                $('#nextooc').val('Next >');
            }
            delete messages;
        }
    );
}

function clearCommentaryForm()
{
    $('#inputinsertcommentary').val('');
    $('#charsleftinsertcommentary, #previewtextinsertcommentary').hide();
    $('input[name=section]').val(chatSection);
    $('#editing').remove();
}

function connectionError()
{
    alert('Could not connect to the API server.');
}

function incrementOOC()
{
    oocOffset++;
    getChatMessages()
    ;
}

function decrementOOC()
{
    if (oocOffset < 1) {
        return false;
    }
    oocOffset--;
    getChatMessages();
}

function incrementRP()
{
    rpOffset++;
    getChatMessages();
}

function decrementRP()
{
    if (rpOffset < 1) {
        return false;
    }
    rpOffset--;
    getChatMessages();
}

function editTitle() {
    var form = document.getElementById('message-subject-form'),
        subject = document.getElementById('message-subject');
    $('#message-subject-form').css('display', 'block');
    $('#message-subject').css('display', 'none');
}
