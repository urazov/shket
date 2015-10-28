var CurrentColumn = -1;

$(window).load(function(){
   /* LOADER */
    $('.loader').animate({opacity : 0}, 600, function(){
        $('.loader').css('display', 'none');
    });
});

$(document).ready(function(){

    $('.btn_login').click(function(e){
        e.preventDefault();
        $(this).text('Выполняется вход, подождите...');
        var form = $(this).parents('form:first');
        $(form).submit();
    });

    /*кнопка печать*/
    $('.printStyle').click(function(e){
        e.preventDefault();
        var elementId = $(this).attr('print_table_id');
        var prtContent = document.getElementById(elementId);
        var WinPrint = window.open('','','left=50,top=50,width=800,height=640,toolbar=0,scrollbars=1,status=0');
        WinPrint.document.write('<div id=\"print\" class=\"contentpane\">');
        WinPrint.document.write(prtContent.innerHTML);
        WinPrint.document.write('</div>');
        WinPrint.document.close();
        WinPrint.focus();
        WinPrint.print();
        WinPrint.close();
    });

    /* TABLES */
    if ($('table.reestr').length > 0)
    {
        $('table.reestr tbody tr td a').click(function(e){
            e.preventDefault();

            $('#modalEditSprData').modal('show');
        });
        $('#btnAddSpr').click(function(e){
            e.preventDefault();

            $('#modalEditSprData').modal('show');
        });
    }

    

    if ($('table.turnik2').length > 0)
    {
        $('table.turnik2 tbody tr td a').click(function(e){
            e.preventDefault();

            $('#modalTurnik1').modal('show');
        });
    }

    if ($('table.turnik3').length > 0)
    {
        $('table.turnik3 tbody tr td a').click(function(e){
            e.preventDefault();

            $('#modalTurnik1').modal('show');
        });
    }

    if ($('table.dop').length > 0)
    {
        $('table.dop tbody tr td a').click(function(e){
            e.preventDefault();

            $('#modalDop').modal('show');
        });
    }

    if ($('.table-markered').length > 0)
    {
        $('.table-markered tr td.markered').click(function(e){
            e.preventDefault();

            var HTML = $(this).html();
            if (HTML == '')
                $(this).html('<div class="marker-small"></div>');
            else
                $(this).html('');
            return -1;
        });
    }

    /* TABS */
    if ($('.cabinet-tabs').length > 0)
    {
        var FormatBreadCrumbs = function(){
            var FirstItem = $('.cabinet-tabs ul.level-one > li.active > a');
            var SecondItem = $('.cabinet-tabs ul.level-two .level-two-item.active')

            var Text = '';
            Text += FirstItem.html();
            Text += ' / ' + SecondItem.html();

            $('.cabinet-breadcrumbs').html(Text);

        };
        var CalcFullWidth = function(){
            ReducedWidth = 1075;
            FullWidth = 0;
            CurrentPos = 0;

            FullWidth = $('.cabinet-tabs ul.level-two li.active').children().length * 361;
            /// $('.cabinet-tabs ul.level-two li.active div').each(function(){
            ///     FullWidth += $(this).width();
            /// });

            return -1;
        };
        var FormatTabsCounters = function(){

            var TabsCounter = $('.cabinet-tabs ul.level-two li.active').children().length;

            var LeftCounter = 0;
            var RightCounter = 0;

            if (TabsCounter >= 3)
            {
                LeftCounter = Math.floor(CurrentPos / 360);
                RightCounter = TabsCounter - 3 - LeftCounter;
                if (RightCounter < 0)
                {
                    RightCounter = 0;
                    LeftCounter = TabsCounter - 3;
                }
                if (LeftCounter < 0)
                {
                    LeftCounter = 0;
                    RightCounter = TabsCounter - 3;
                }
            }

            var Left = LeftCounter + '/' + TabsCounter;
            var Right = RightCounter + '/' + TabsCounter;

            $('.scroll-counter.scroll-counter-left').html(Left);
            $('.scroll-counter.scroll-counter-right').html(Right);
        };

        $('.cabinet-tabs ul.level-one > li > a').click(function(e){
            e.preventDefault();

            var BlockID = $(this).data('block');           

            $('.cabinet-tabs ul.level-one > li').removeClass('active');
            $(this).parent().addClass('active');

            /* SHOW MENU */
            $('.cabinet-tabs ul.level-two li').removeClass('active');
            var Menu = $('.cabinet-tabs ul.level-two li[data-block="' + BlockID + '"]');
            Menu.addClass('active');

            $('.cabinet-tabs ul.level-two li.active').animate({ scrollLeft: 0 }, 10);

            $('.cabinet-tabs ul.level-two .level-two-item').removeClass('active');
            var PanelID = Menu.children().eq(0).data('panel');
            Menu.children().eq(0).addClass('active');
            $('.tab-pane').removeClass('active');
            $('#' + PanelID).addClass('active');

            FormatBreadCrumbs();
            CalcFullWidth();
            FormatTabsCounters();

            return -1;
        });
        $('.cabinet-tabs ul.level-two .level-two-item').click(function(e) {
            e.preventDefault();

            var BlockID = $(this).parent().data('block');
            var PanelID = $(this).data('panel');

            $('.cabinet-tabs ul.level-two .level-two-item').removeClass('active');
            $(this).addClass('active');

            $('.tab-pane').removeClass('active');
            $('#' + PanelID).addClass('active');

            FormatBreadCrumbs();

            return -1;
        });

        var ReducedWidth = 1075;
        var FullWidth = 0;
        var CurrentPos = 0;
        var Step = 1085;
        var LeftTimeOutID = 0;
        var RightTimeOutID = 0;

        var ScrollLeft = function(){
            $('.cabinet-tabs ul.level-two li.active').animate({ scrollLeft: CurrentPos - Step }, 200);
            CurrentPos -= Step;
            if (CurrentPos < 0)
                CurrentPos = 0;

            FormatTabsCounters();
            return -1;
        };
        var ScrollRight = function(){
            $('.cabinet-tabs ul.level-two li.active').animate({ scrollLeft: CurrentPos + Step }, 200);
            CurrentPos += Step;
            if (CurrentPos >= FullWidth)
                CurrentPos = FullWidth - Step;

            FormatTabsCounters();
            return -1;
        };
        $('.scroll-element.scroll-left').click(ScrollLeft);
        $('.scroll-element.scroll-right').click(ScrollRight);

        FormatBreadCrumbs();
        CalcFullWidth();
        FormatTabsCounters();
    }

    /* NAV TABS */
     if ($('.horizontal-scrollable-tabs').length > 0)
    {
        $('.horizontal-scrollable-tabs').horizontalTabs();
    } 
    /*if ($('.nav-tabs').length > 0)
    {
        $('.nav-tabs').tabdrop({text: '<i class="fa fa-bars"></i>'});
        $('.dropdown-submenu .dropdown-menu li a').click(function(e){
            e.preventDefault();

            $(this).parent().parent().parent().parent().children().removeClass('active');
            $('.dropdown-submenu .dropdown-menu li').removeClass('active');
        });
        $('.dropdown-menu li a').click(function(e){
            e.preventDefault();

            if ($(this).parent().hasClass('dropdown-submenu'))
                return;


            if (!$(this).parent().parent().hasClass('dropdown-menu'))
                return;

            $(this).parent().parent().children().removeClass('active');
            $('.dropdown-submenu .dropdown-menu li').removeClass('active');
        });

        $('.dropdown-submenu .dropdown-menu').each(function(){
            var Menu = $(this);
        });
    }*/


    /* MODULES */
    $('.module-item .content-hover').click(function(e){
        e.preventDefault();

        var Content = $(this).find('.module-desc').html();
        var Name = $(this).data('name');
        $('#modalModule .modal-body').html(Content);
        $('#modalModule .modal-title').html(Name);

        $('#modalModule').modal('show');
    });

    /* DATE PICKER */
    if ($('.datepicker').size() > 0){
        $('.datepicker').datetimepicker({format: 'DD-MM-YYYY', language: 'ru', pickTime: false});
    }

    /* LINKS */
    $('.navigate').click(function(e){
        e.preventDefault();

        var Link = $(this).data('link');
        window.location.replace(Link);
    });

    $('.nav.navbar-nav').onePageNav({
        currentClass: 'active',
        scrollSpeed: 750,
        scrollThreshold: 0.3,
        easing: 'swing',
        scrollOffset: 100
    });

    /* SLIDER AUTOSIZE */
    var LoginHeight = $('#login').height() + 100;
    var ScreenHeight = $(window).height();
    var ScreenWidth = $(window).width();
    var H = ScreenHeight - 100 - LoginHeight;

    if (ScreenHeight > 700)
        $('#slider').css({height : H});

    /* MAIN SLIDER */
    if ($('#slider').length > 0)
    {
        $('#slider .slider-container').bjqs({
            'height' : H,
            'width' : 1130,
            'responsive' : true,
            'nexttext': '<i class="icon-chevron-right"></i>',
            'prevtext': '<i class="icon-chevron-left"></i>',
            'responsive' : true
        });

        /// CALCULATE SLIDE SIZES
        $('.bjqs-slide img').each(function(){
            var Image = $(this);
            var VisibleHeight = H;

            var ImageWidth = getOriginalWidthOfImg(this);
            var ImageHeight = getOriginalHeightOfImg(this);
            var Coef = ImageWidth / ImageHeight;

            ImageHeight = VisibleHeight;
            ImageWidth = ImageHeight * Coef;
            Image.css({width : ImageWidth, height : ImageHeight});

        });


    }

    /* LOGIN */
    $('#btnLogin').click(function(e){
        e.preventDefault();
        var Button = $('#btnLoginForm');
        Button.html('Войти');

        var Errors = $('#loginErrors');
        Errors.html('');

        $('#modalLogin').modal({show : true});
    });

    /* TARIIFS */
    $('.table-tariffs-right > tbody > tr > td').hover(function(){
        
        var Column = $(this).parent().children().index($(this));
        var Name = $('.table-tariffs-right > thead > tr > th span').eq(Column).html();
        var Price = $('.table-tariffs-right > tbody > tr').eq(0).children().eq(Column).html();
        var Desc = $('.table-tariffs-right > tbody > tr').eq($('.table-tariffs-right > tbody > tr').length - 1).children().eq(Column).html();
        $('.table-tariffs-right > tbody > tr > td .marker').removeClass('active');
        var ColumnObject = $('table tr td:nth-child(' + (Column + 1) + ')');
        ColumnObject.each(function(){
           if ($(this).find('.marker').length > 0)
               $(this).find('.marker').addClass('active');
        });

        if (Column == CurrentColumn)
            return -2;

        CurrentColumn = Column;
        var HTML = '';
        HTML = '<h4>Тариф "' + Name + '" за ' + Price + ' р. в месяц</h4>';
        HTML += Desc;

        $('#tariffsInfo').html(HTML);

    }, function(){});

    /* MODALS */
    function centerModals(){
        $('.modal').each(function(i){
            var $clone = $(this).clone().css('display', 'block').appendTo('body');
            var top = Math.round(($clone.height() - $clone.find('.modal-content').height()) / 2);
            top = top > 0 ? top : 0;
            $clone.remove();
            $(this).find('.modal-content').css("margin-top", top);
        });
    }
    $('.modal').on('show.bs.modal', centerModals);
    $(window).on('resize', centerModals);
});

$(window).load(function(){

    /* INITIAL SCROLLING */
    var ObjectID = window.location.hash.substring(1);
    if (ObjectID != '')
        ScrollTo(ObjectID, -100);

});
$(window).scroll(function () {
});

function getOriginalWidthOfImg(img_element) {
    var t = new Image();
    t.src = (img_element.getAttribute ? img_element.getAttribute("src") : false) || img_element.src;
    return t.width;
}
function getOriginalHeightOfImg(img_element) {
    var t = new Image();
    t.src = (img_element.getAttribute ? img_element.getAttribute("src") : false) || img_element.src;
    return t.height;
}
function ScrollTo(ObjectID, Offset) {
    $.scrollTo( '#' + ObjectID , 650, { easing: 'swing' , offset: Offset , 'axis': 'y' } );
    return -1;
}
function ChangeAddress(Address) {
    window.history.pushState("New Address", "", Address);
    return -1;
}

/* IE10 FIX*/
if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    var msViewportStyle = document.createElement('style')
    msViewportStyle.appendChild(
        document.createTextNode(
            '@-ms-viewport{width:auto!important}'
        )
    )
    document.querySelector('head').appendChild(msViewportStyle)
}

/* SMOOTH SCROLL  */
var scrollAnimationTime = 1200,
    scrollAnimation = 'easeInOutExpo';

$('a.scrollto').bind('click.smoothscroll', function (event) {
    event.preventDefault();
    var target = this.hash;
    $('html, body').stop().animate({
        'scrollTop': $(target).offset().top
    }, scrollAnimationTime, scrollAnimation, function () {
        window.location.hash = target;
    });
});
    
/*Отображать выпадающий список школ на страницу с выводом меню*/
/*$('#tab_menu').click(function(){
        alert("dfsfds");
        console.log("sdsfsfds");
        var params = {
            school_id : 1,
            date_from : 1,
            date_to : 1
        };
        $.ajax({
            url: '../js_action/getAllSchool.php',
            type: 'POST',
            data: params,
            success: function(data){
                $('#result_all_school_pitanie').empty().append(data);
            }
        });
    });*/