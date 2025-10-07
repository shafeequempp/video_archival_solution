(function ($) {
  'use strict';
  $(function () {





        if ($( ".navbar" ).hasClass( "fixed-top" )) {
          document.querySelector('.page-body-wrapper').classList.remove('pt-0');
          document.querySelector('.navbar').classList.remove('proBanner-padding-top');
        }
        else {
          document.querySelector('.page-body-wrapper').classList.add('pt-0');
          document.querySelector('.navbar').classList.add('proBanner-padding-top');
          
        }

  });
})(jQuery);
