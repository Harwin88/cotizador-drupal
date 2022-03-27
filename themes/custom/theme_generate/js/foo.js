(function ($) {
  'use strict';

$(document).ready(function() {

  $('.modal-open-btn').click(function(){
    $("#myModal").css("display", "block");
});

$('.close').click(function(){
  $("#myModal").css("display", "none");
});


    $(".fadeout").delay(2000).fadeOut(1000);

    $("#ocultar").click(function(event){
      event.preventDefault();
      $('#box1').fadeOut(2000);
      });
      $("#mostrar").click(function(event){
      event.preventDefault();
      $("#box1").slideDown(3000);
      });

});
})(jQuery);