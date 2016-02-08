jQuery.fn.followTo = function (pos,top1,top2,type1, type2) {
    var $this = this,
        $window = $(window);

    $window.scroll(function (e) {
        if ($window.scrollTop() > pos) {
            $this.css({
                position: type1,
                top: top1
            });
            $('#checkin2').width($(window).width() - 180);
        } else {
            $this.css({
                position: type2,
                top: top2
            });
            $('#checkin2').width('100%');
        }
    });
	
   
  
};

function carouselSize() { $('#newcarousel').height($(window).height() * 0.73);	}


function moodVideo() { 

    if($(window).width() > $(window).height())
        $('#moodVideo').height($(window).height() * 0.5);  
    else
        $('#moodVideo').height($(window).height() * 0.35);  
    
    $('#moodVideo iframe').fadeOut(0);
    $('#moodVideo').slideUp(0);
    $('.vtext2').fadeOut(0);
    $('.vtext1').fadeIn(0);
    $('.vicon').addClass('fa-youtube-play').removeClass('fa-close');
}

function handleMoodVideo() {

    var vsrc = $('#moodVideo iframe').attr('src');
    $('#moodVideo iframe').attr('src','');
    $('#moodVideo iframe').attr('src',vsrc);

    if($('#moodVideo').css('display') == 'none') {
        $('.vicon').removeClass('fa-youtube-play').addClass('fa-close');
        $('#moodVideo').slideDown(250);
        $('#moodVideo iframe').delay(250).fadeIn();
        $('.vtext1').fadeOut(0);
        $('.vtext2').fadeIn(0);
        
    } else {
        
        $('.vicon').addClass('fa-youtube-play').removeClass('fa-close');
        $('#moodVideo iframe').fadeOut();
        $('#moodVideo').delay(250).slideUp(250);
        $('.vtext2').fadeOut(0);
        $('.vtext1').fadeIn(0);
    }
    
    
}

function CheckinPosition() {
    
    $('#checkin').followTo($('#newcarousel').height()+40,160,$('#newcarousel').height()+170,'fixed','absolute');
    $('#checkin2').followTo(600,0,0,'fixed','relative');
    
    $('#booking-summary').followTo($('.page-title').height()+15,15,$('.page-title').height()+15,'fixed','absolute');
}

function RecheckAvailability(target) {
    location.href = target+'?from='+$('.cifrom').val()+'&to='+$('.cito').val()+'&location='+$('.cilocation').val();
}


$(window).scroll(function() { CheckinPosition(); });

$(document).ready(function() {
    
    $('.roomCard').each(function() { 
        
       if($(this).find('div.specOff').length != 0) {
            $(this).css('marginBottom','60px');
       }
    });
    
    
    $('.dateChangerFader, .bkSummarySwitch').click(function() { 
        $('.dateChangerFader, .bkSummary, .bkSummarySwitch, .cdFields').slideToggle(); 
    });

    
    $('.noselect').on('selectstart dragstart', function(evt){ evt.preventDefault(); return false; });
    
    carouselSize();
    moodVideo();
    
    $('.map').height($(window).height() * 0.35);
    
    if(typeof Tipped !== 'undefined') Tipped.create('.roomDetails img');
     
    
    CheckinPosition();
    $(window).trigger('resize');
    $(window).trigger('scroll');
    
    
    $('.cifrom').on('change', function() { $('.cifrom').val($(this).val()); });
    $('.cito').on('change', function() { $('.cito').val($(this).val()); });
    $('.fluid-wrapper').on('click',function() { $('#checkin3').slideUp(); });
    
    $('.roomButton').on('click', function() {
      
       /* hide */ 
       if($(this).children('.roomDetClose').css('display') == 'inline') {
           
           $(this).children('.roomDetClose').fadeOut(0);
           $(this).children('.roomDetOpen').fadeIn(0);
           /* close details */
           $(this).parent().parent().children('.roomDetails').slideUp();
        
       /* show */ 
       } else {
           $(this).children('.roomDetClose').fadeIn(0);
           $(this).children('.roomDetOpen').fadeOut(0);
           
           /* show details */
           $(this).parent().parent().children('.roomDetails').slideDown();
       }
        
    });
    
    $('.roomDets2').click(function() {
        
        $(this).parent().parent().children('.roomDetails').slideToggle();
        
    })
    
    $('.roomOccupancyText').click(function() {
        
        /* hide */ 
        if($(this).parent().children('.roomCalendar').css('display') == 'inline-block') {
         
            $(this).parent().children('.roomCalendar').slideUp(200).delay(200).empty();
            $(this).removeClass('roomOccAct');
           
        /* show */
        } else {

            $(this).addClass('roomOccAct');
            avRoomItems = Math.floor(($('.roomCard').width() - 100) / 80 );
            roomTypeId = $(this).parent().attr('data-room-type-id');
            $(this).parent().children('.roomCalendar').slideDown(200).load("roomCalendar.php?items="+avRoomItems+"&room_type_id="+roomTypeId);

        }
        
    });
    
    
     
      
    $('.open-gallery').on('click', function(e) {
     
        $('#gallery .gallery-title').html($(this).attr('data-gallery-title'));
        $('#gallery iframe').height($('#gallery').height() - 80);
        $('#gallery iframe').attr('src',$(this).attr('data-gallery-url'));
        $('#gallery').fadeIn();
       
    });
    
    $('.offer-box').on('click', function(e) {
        
        $('.offer-box-desc').width($('#special-offers').width() - 55); 
        $('.offer-box-desc').not($(this).children('.offer-box-desc')).slideUp();
        $('.offer-box-triangle').not($(this).find('.offer-box-triangle')).removeClass('offer-box-active-triangle');
        $('.offer-box').not($(this)).removeClass('offer-box-active');
        $(this).find('.offer-box-triangle').toggleClass('offer-box-active-triangle');
        $(this).toggleClass('offer-box-active');
        $(this).children('.offer-box-desc').slideToggle();
        
        
        
    });
    
    
   $(document).mouseup(function (e) {
    
        var container = $('#gallery');
        
        
        if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) {
             container.hide();
        }
        
       
       if(!e.target.className.match(/offer/g)) $('.offer-box-desc').slideUp();
        
    });
      
    
});
	
$(window).resize(function() { 
    
    carouselSize(); 
    //moodVideo();
    if($(window).width() > 1023) $('#checkin3').slideUp(0);
   
    if($(window).width() > 716){
        $('.offer-box').width(($('#special-offers').width() - 35) / 7);
        
    } else {
        
        $('.offer-box').width($('#special-offers').width() * 0.33 - 10);
        
    } 
    
    $('.offer-box-desc').width($('#special-offers').width() - 55);

   $('#checkin').css('top',$('#newcarousel').height() + 170);
   $(window).trigger('scroll');
   
   $('.roomCalendar').empty().slideUp();
   
});


$(window).scroll(function() {
    
    $('.offer-box-desc').slideUp();
    
})

