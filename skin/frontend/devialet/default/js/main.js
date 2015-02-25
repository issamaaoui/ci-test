var $j = jQuery.noConflict();
(function($) {
    $(document).ready(function(){
        $('#select-language').selectmenu();

        $('nav.main-menu a.hamburger-menu-trigger').click(function(){
            $(this).parents('nav.main-menu').toggleClass('active');
            $(this).parents('body').toggleClass('active-menu');
            $(this).parents('nav.main-menu').find('.menu-container').slideToggle(200);
        });

        $('nav.navbar button.menu-close-trigger').click(function(){
            $(this).parents('nav.navbar').removeClass('active');
        });

        $('.trigger-customer-menu').click(function(){
            $('.menu-customer-wrapper').toggleClass('active');
            $('.menu-customer-wrapper #customer_menu-main').slideToggle(200);
        });

        $('.bg-menu-active').click(function(){
            $(this).parents('nav.main-menu').toggleClass('active');
            $(this).parents('nav.main-menu').find('.menu-container').slideToggle(200);
        });

        $('.command .article-dropdown').click(function(){
            var command_body = $(this).parents('.command').find('.body-command');
            command_body.slideToggle();
            command_body.parents('.command').toggleClass('active');
        });

        $('.field-wrapper input').focus(function(){
            $(this).parents('.field-wrapper').addClass('active');
        });
        $('.field-wrapper input').focusout(function(){
            $(this).parents('.field-wrapper').removeClass('active');
        });

        $('.tab-appli li').click(function(){
            $('.tab-appli li').removeClass('active');
            $(this).addClass('active');
            $('.content-wrapper .panel-appli').removeClass('active');
            $(".content-wrapper .panel-appli[data-panel-id='" + $(".tab-appli li").index($(this)) + "']").addClass('active');
        });

        $(document).click(function(e){
            if($(e.target).parents('ul.menu-customer').length < 1 && !$(e.target).hasClass('ul.menu-customer')){
                $('.menu-customer-wrapper').removeClass('active');
                $('.menu-customer-wrapper #customer_menu-main').slideUp(200);
            }

            if($(e.target).parents('.menu-footer').length < 1 && !$(e.target).hasClass('menu-footer')
                && $(e.target).parents('.menu-devialet-wrapper').length < 1 && !$(e.target).hasClass('menu-devialet-wrapper')
                && $(e.target).parents('.menu-phantom-wrapper').length < 1 && !$(e.target).hasClass('menu-phantom-wrapper')
                && $(e.target).parents('.menu-expert-wrapper').length < 1 && !$(e.target).hasClass('menu-expert-wrapper')
                && $(e.target).parents('.logo-wrapper').length < 1 && !$(e.target).hasClass('logo-wrapper')
                && $(e.target).parents('.button-wrapper').length < 1 && !$(e.target).hasClass('button-wrapper')){

                $('nav.main-menu').removeClass('active');
                $('body').removeClass('active-menu');
                $('nav.main-menu').find('.menu-container').slideUp(200);

            }
        });

    });
})(jQuery);