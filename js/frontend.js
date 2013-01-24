;(function (jQuery, window, undefined){
  'use strict';

  jQuery.fn.foundationAccordion = function (options) {

    // DRY up the logic used to determine if the event logic should execute.
    var hasHover = function(accordion) {
      return accordion.hasClass('hover') && !Modernizr.touch
    };

    jQuery(document).on('mouseenter', '.routes-and-schedules-accordion-li', function () {
        var p = jQuery(this).parent();

        if (hasHover(p)) {
          var flyout = jQuery(this).children('.routes-and-schedules-accordion-content').first();

          jQuery('.routes-and-schedules-accordion-content', p).not(flyout).hide().parent('li').removeClass('routes-and-schedules-accordion-li-active');
          flyout.show(0, function () {
            flyout.parent('li').addClass('routes-and-schedules-accordion-li-active');
          });
        }
      }
    );

    jQuery(document).on('click.fndtn', '.routes-and-schedules-accordion-title', function () {
        var li = jQuery(this).closest('li'),
            p = li.parent();

        if(!hasHover(p)) {
          var flyout = li.children('.routes-and-schedules-accordion-content').first();

          if (li.hasClass('routes-and-schedules-accordion-li-active')) {
            p.find('li').removeClass('routes-and-schedules-accordion-li-active').end().find('.routes-and-schedules-accordion-content').hide();
          } else {
            jQuery('.routes-and-schedules-accordion-content', p).not(flyout).hide().parent('li').removeClass('routes-and-schedules-accordion-li-active');
            flyout.show(0, function () {
              flyout.parent('li').addClass('routes-and-schedules-accordion-li-active');
            });
          }
        }
      }
     );

  };

})( jQuery, this );

jQuery(document).foundationAccordion();