var app = angular.module('LOTGD', []);

var startX = 0,
    maxDistance = 100,
    touch = 'ontouchend' in document,
    startEvent = 'touchstart',
    moveEvent = 'touchmove',
    endEvent = 'touchend',
    modalOpened,
    datacacheSelector = $('#DB_USEDATACACHE');

$(document).ready(function() {
    if (window.matchMedia("(max-width: 700px)").matches != false) {
        tutorial();
    }
    if ($('a[name=mailLink] i').hasClass('glow')) {
        newMail();
    }
    if (localStorage['hideMain'] == 1) {
        $('main').hide();
        $('nav').show();
        $('.rightPanel').show();
    }
    if (localStorage['hideTranslate'] == 1) {
        $('.thot, .t').css('display', 'none');
    }
    else {
        $('.thot, .t').css('display', 'inline-block');
        $('.navigation .thot, .navigation .t').css('display', 'block');
    }
    if (datacacheSelector.value == 1) {
        $('#DB_DATACACHE').css('display', 'inline');
    }
    $('select[name="template"] > option').remove();
    $('select[name="template"]')
        .append(
            '<option value="ResponsiveBlue.htm" data-css="blue.css">Blue</option>'
        )
        .append(
            '<option value="Responsive.htm" data-css="black.css">Black</option>'
        );
    if (!window.frameElement) {
        $('.close-modal, .popout-modal').hide();
    }
});

$(document).on(startEvent, function(e) {
    startX = e.originalEvent.touches ? e.originalEvent.touches[0].pageX : e.pageX;
})
.on(endEvent, function(e) {
    startX = 0;
})
.on(moveEvent, function(e) {
    var currentX = e.originalEvent.touches ? e.originalEvent.touches[0].pageX : e.pageX,
        currentDistance = (startX === 0) ? 0 : Math.abs(currentX - startX);
    if (currentDistance > maxDistance && currentX > startX){
        $('main').hide();
        $('nav').show();
        $('.rightPanel').show();
        startX = 0;
        currentDistance = 0;
        localStorage.setItem('hideMain', 1);
        tutorial();
    }
    else if (currentDistance > maxDistance && currentX < startX) {
        $('main').show();
        $('nav').hide();
        $('.rightPanel').hide();
        startX = 0;
        currentDistance = 0;
        localStorage.setItem('hideMain', 0);
        tutorial();
    }
})
.on('keyup', function(event){
    var isHidden = localStorage['hideTranslate'];
    if (isHidden != 1 && event.keyCode == 27) {
        localStorage.setItem('hideTranslate', 1);
        $('.thot, .t').css('display', 'none');
    }
    if (isHidden == 1 && event.keyCode == 27) {
        localStorage.setItem('hideTranslate', 0);
        $('.thot, .t').css('display', 'inline-block');
        $('.navigation .thot, .navigation .t').css('display', 'block');
    }
});

$('#DB_USEDATACACHE').on('change', function() {
    var style = $(this).value() == 1 ? 'inline' : 'none';
    $('#DB_DATACACHE').css('display', style);
});

$('.alerts').on('click tap', function(){
    $('.alerts').animate({
        height: '0em'
    }, 250).html('');
});

$('.contact a').on('click', function (e) {
    modalOpened = this;
    $(this).addClass('glow');
    loadModal($(this).attr('href'));
    clearMail();
    e.preventDefault();
    return false;
});

$('.contact a, a[target^="_"').each(function() {
    $(this).removeAttr('onclick');
});

$('.modal').on('load', function() {
    if ($(this).attr('src')) {
        $(this).slideDown();
    }
    $(modalOpened).removeClass('glow');
});

$('#close-modal').on('click', function (e) {
    $('.modal', window.parent.document.body).slideUp(function () {
        $(this).removeAttr('src').hide();
    });
});

$('#popout-modal').on('click tap', function (e) {
    window.open(window.frameElement.src);
    $('.modal', window.parent.document.body).slideUp(function () {
        $(this).removeAttr('src').hide();
    });
});

$('select[name="template"]').on('change click tap', function() {
    var cssSelected = $(this).find(':selected').data('css');
    $('#responsiveCSS').attr('href', 'templates/' + cssSelected);
});

$('a[target^="_"]').on('click tap', function (e) {
    if ($(this).attr('href').startsWith('http')) {
        return true;
    }
    loadModal($(this).attr('href'));
    e.preventDefault();
    return false;
});

function newMail()
{
    $('a[name=mailLink] i').addClass('glow');
    $('.alerts').html('You have a new mail!').animate({
        height: '1.25em'
    }, 250);
}

function clearMail()
{
    $('a[name=mailLink] i').removeClass('glow');
    $('.alerts').animate({
        height: '0em'
    }, 250).html('');
}

function tutorial() {
    var stage = localStorage.getItem('tutorialStage');
    if (stage > 2) {
        return;
    }
    switch (stage) {
        default:
            $('.tutorial').show(250);
            $('.tutorial-stage-1').show();
            localStorage.setItem('tutorialStage', '1');
            stage = 1;
            break;
        case "1":
            $('.tutorial-stage-1').hide();
            $('.tutorial-stage-2').show(250);
            localStorage.setItem('tutorialStage', '2');
            stage = 2;
            break;
        case "2":
            $('.tutorial').hide();
            localStorage.setItem('tutorialStage', '3');
            break;
    }
}

function loadModal(link) {
    $('.modal').slideUp().attr('src', link);
}