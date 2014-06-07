# prevent slow clicks
$ ->
  FastClick.attach document.body
  return

# zoom helper
$ ->
  $helper = $ '#zoom-helper'

  resize = ->
    zoom = Math.round (detectZoom.zoom() || detectZoom.device()) * 100
    $helper.width zoom + '%'
    return
  $(window).on 'resize', resize
  resize()

  return

# mobile header
$ ->
  $header =   $ '#header'
  className = 'opened'
  
  $('#mobile-header').find('.open-header, .close-header').on 'click', (e) ->
    e.preventDefault()
    $header.toggleClass className
    return
  
  return

# fake select
$ ->
  $('.fake-select select').each ->
    $select = $ this
    $select.siblings('.value').text $select.find('option:selected').text()
    return
  
  $('.fake-select select').on 'change', ->
    $select = $ this
    $select.siblings('.value').text $select.find('option:selected').text()
    return
  
  return

# header settings
$ ->
  $('#header select').on 'change', (event) ->
    $target = $ event.target
    if $target.attr('id') == 'language'
      window.location.href = window.location.pathname.replace $target.data('current-language'), $target.val()
    else if $target.attr('id') == 'currency'
      window.location.href = window.location.pathname + (if window.location.search.length then window.location.search + '&' else '?') + $.param currency: $target.val()
    return
  
  return

# date selector
$ ->
  if !$('#mobile-header').is ':visible'
    $inputs = $ '.field.date input[type="date"]'

    if $inputs.length
      $inputs
        .attr
          type: 'text'
        .datepicker
          buttonText:         ''
          dateFormat:         'yy-mm-dd'
          defaultDate:        0
          firstDay:           1
          minDate:            0
          showMonthAfterYear: true
          showOn:             'button'
          onClose: (date) ->
            $input = $ this
            if $input.hasClass 'from'
              $input.closest('form').find('input.to').datepicker 'option', 'minDate', date
            else if $input.hasClass 'to'
              $input.closest('form').find('input.from').datepicker 'option', 'maxDate', date
            return

  return

# carousel
$ ->
  disabledNavigation = false
  $carousel = $ '#carousel'
  $slides = $carousel.find '.slides > li'
  $tooltips = $carousel.find '.tooltips li'
  $navigation = $carousel.find '.navigation'
  timer = null
  
  $carousel.addClass 'disabled-transitions'
  $slides.eq(0).addClass 'current fast-tooltips'
  $slides.eq(0).height()
  $carousel.removeClass 'disabled-transitions'
  
  resize = ->
    zoom = detectZoom.zoom() || detectZoom.device()

    height = $(window).height()
    if $('#mobile-header').is ':visible'
      height -= $('#mobile-header').height()
    else if $carousel.hasClass 'small'
      height *= .7
    $carousel.height height * zoom
    return
  
  $(window).on 'resize', resize
  
  resize()

  $pagination = $ '<ul class="pagination"></ul>'
  for i in [0 .. ($slides.length - 1)]
    $pagination.append '<li' + (if !i then ' class="current"' else '') + '></li>'
  $carousel.append $pagination
  
  $navigation.on 'click', (event) ->
    $target = $ event.target
    
    if !disabledNavigation && $target.is '.prev, .next'
      disabledNavigation = true
      
      $currentSlide = $slides.filter('.current')
      
      if $target.is '.prev'
        $targetSlide = if $currentSlide.prev().length then $currentSlide.prev() else $slides.eq $slides.length - 1
        targetClass = 'prev'
        nextCurrentClass = 'next'
      else if $target.is '.next'
        $targetSlide = if $currentSlide.next().length then $currentSlide.next() else $slides.eq 0
        targetClass = 'next'
        nextCurrentClass = 'prev'
      
      $carousel.addClass 'disabled-transitions'
      $targetSlide.addClass targetClass
      $targetSlide.height()
      $carousel.removeClass 'disabled-transitions'
      $currentSlide.removeClass('current fast-tooltips').addClass nextCurrentClass
      $targetSlide.removeClass(targetClass).addClass 'current'

      $pagination.find('li').eq($targetSlide.prevAll().length).addClass('current').siblings().removeClass 'current'
    
    return
  
  $navigation.on 'mouseenter', ->
    window.clearTimeout timer
    return
  
  $navigation.on 'mouseleave', ->
    rotate()
    return
  
  $carousel.hammer()
    .on 'swipeleft', ->
      $navigation.find('.next').trigger 'click'
      return
    .on 'swiperight', ->
      $navigation.find('.prev').trigger 'click'
      return
  
  $slides.on 'transitionend webkitTransitionEnd', (event) ->
    $target = $ event.target
    
    if $target.is('.slides > .current')
      $target.addClass 'fast-tooltips'
    
    if $target.is('.slides > .prev, .slides > .next')
      $(this).removeClass 'prev next'
      disabledNavigation = false
    
    return
  
  rotate = ->
    timer = window.setTimeout ->
      $navigation.find('.next').trigger 'click'
      rotate()
      return
    , 8000
    return
  
  $tooltips.on 'mouseenter', ->
    window.clearTimeout timer
    
    $div = $(this).find('div')
    $div.css marginTop: -1 * Math.floor $div.height() / 2
    return
  
  $tooltips.on 'mouseleave', ->
    rotate()
    return
  
  rotate()
  
  return

# fixed sidebar elements
$ ->
  elements = [
    {
      selector:       '#checkin',
      headerSelector: '#carousel',
      headerOffset:   -60,
      footerSelector: '#footer',
      footerOffset:   30
    },
    {
      selector:       '#booking-summary',
      headerSelector: '.page-title',
      headerOffset:   30,
      footerSelector: '#footer',
      footerOffset:   30
    }
  ]
  
  elements.forEach (element) ->
    $element = $(element.selector)
    
    if $element.length && $element.css('position') == 'fixed'
      element.fixed = true
      element.height = $element.outerHeight()
      
      if element.headerSelector
        $header = $ element.headerSelector
        element.fixedTop = $header.outerHeight() + element.headerOffset
        element.scrollTop = element.fixedTop - (Math.floor($element.position().top) + parseInt($element.css('marginTop')))
      
      if element.footerSelector
        $footer = $ element.footerSelector
        element.fixedBottom = $(document).outerHeight() - $footer.outerHeight() - element.footerOffset - $element.outerHeight()
        element.scrollBottom = element.fixedBottom - (Math.floor($element.position().top) + parseInt($element.css('marginTop')))
      
      reposition = ->
        top = $element.offset().top
        
        if element.fixed
          if top < element.fixedTop
            element.fixed = false
            element.restoreMargin = $element.css 'marginTop'
            $element.css
              position:   'absolute'
              top:        element.fixedTop + 'px'
              marginTop:  0
          else if top > element.fixedBottom
            element.fixed = false
            element.restoreMargin = $element.css 'marginTop'
            $element.css
              position:   'absolute'
              top:        element.fixedBottom + 'px'
              marginTop:  0
        else
          if $(window).scrollTop() > element.scrollTop && $(window).scrollTop() < element.scrollBottom
            element.fixed = true
            $element.css
              position:   'fixed'
              top:        $element.data('top')
              marginTop:  element.restoreMargin
        
        return
      
      $(window).on 'scroll', ->
        reposition()
        return
      
      reposition()
    
    return
  
  return

# map
$ ->
  if $('#map-container').length
    $map = $ '#map-container .map'

    options =
      mapTypeControl:     false
      scrollwheel:        false
      streetViewControl:  false
      zoomControl:        false
      mapTypeId:          google.maps.MapTypeId.ROADMAP
      center:             new google.maps.LatLng 47.498072, 19.062733
      zoom:               15
      styles:             [{featureType: 'poi.business', stylers: [{visibility: 'off'}]}]
    map = new google.maps.Map $('#map-container > div').get(0), options
    
    bubble = new google.maps.InfoWindow()
    
    $.ajax
      url:      $map.data 'poi-url'
      type:     'GET',
      dataType: 'json'
      success: (pois) ->
        bounds = new google.maps.LatLngBounds()
        for poi in pois
          latlng = new google.maps.LatLng poi.lat, poi.lng
          bounds.extend latlng
          if poi.image
            icon =
              url:        poi.image
              size:       new google.maps.Size 174, 103
              scaledSize: new google.maps.Size 87, 52
              anchor:     new google.maps.Point 44, 52
            shape =
              type:   'poly'
              coord:  [0, 0, 174, 0, 174, 90, 101, 90, 88, 103, 76, 90, 0, 90]
            marker = new google.maps.Marker map: map, position: latlng, title: poi.name, icon: icon, shape: shape, anchorPoint: new google.maps.Point(0, -52)
          else
            marker = new google.maps.Marker map: map, position: latlng, title: poi.name
            google.maps.event.addListener marker, 'click', ->
              bubble.setContent this.title
              bubble.open map, this
              return
          map.fitBounds bounds
        return
      error: (request, status, error) ->
        console.log request, status, error
        return
  
  return

# rooms
$ ->
  $('.rooms').on 'click', (e) ->
    $target = $ e.target
    if $target.is('h2 a') || $target.is('.open') || $target.is('.close')
      e.preventDefault()
      $li = $target.closest('li')
      $extra = $li.find('.extra')
      
      if $target.is('h2 a')
        if !$li.hasClass 'opened'
          $li.find('.open').trigger 'click'
        else
          $li.find('.close').trigger 'click'
      else if $target.is('.open')
        $li.addClass 'opened'
        $extra
          .hide()
          .slideDown 200
      else if $target.is '.close'
        $li.removeClass 'opened'
        $extra.slideUp 200
    return
  return

# booking filter
$ ->
  $container = $ '#booking-filter'
  $container.find('.open-filter').on 'click', (e) ->
    e.preventDefault()
    $fieldset = $container.find('.filter > fieldset')
    if $container.hasClass 'opened'
      $fieldset.slideUp 200, ->
        $container.removeClass 'opened'
        return
    else
      $container.addClass 'opened'
      $fieldset
        .hide()
        .slideDown 200
    return
  
  return

# booking update summary
$ ->
  if $('form.update-summary').length || $('form.auto-update-summary').length
    updateSummary = ->
      $form = if $('form.update-summary').length then $('form.update-summary') else $('form.auto-update-summary')
    
      $.ajax
        url:      $form.data 'refresh'
        type:     $form.attr 'method'
        data:     $form.serialize()
        dataType: 'json'
        success: (response) ->
          $('#booking-summary .details li').addClass('inactive').text ->
            $(this).data 'label'
            return
          
          Object.keys(response.details).forEach (key) ->
            if response.details[key].length
              $container = $ '#booking-summary .details li.' + key
              $container.removeClass('inactive').empty()
              response.details[key].forEach (data) ->
                if data.title
                  $container.text data.title
                else
                  $p = $ '<p></p>'
                  ['name', 'description', 'price'].forEach (index) ->
                    $('<span class="' + index + '"></span>').text(data[index]).appendTo $p
                    return
                  $p.appendTo $container
                return
            return
          
          $('#booking-summary .total').text response.total
          
          return
        error: (request, status, error) ->
          console.log request, status, error
          return
      
      return

    $('form.update-summary').on 'change', updateSummary

    updateSummary()
  return

# header
$ ->
  if $('#mobile-header').is(':visible') && $(window).height() < 640
    $('#header').addClass 'small'
  return

# overlays
$ ->
  if $('.open-overlay').length
    $('body').on 'click', (e) ->
      $target = $ e.target
      if $target.is '#overlay .close'
        e.preventDefault()
        $target.closest('#overlay').removeClass 'opened'
        window.setTimeout ->
          $target.closest('#overlay').remove()
          return
        , 200
      return
    
    $('.open-overlay').on 'click', (e) ->
      e.preventDefault()
      $target = $(e.target).closest '.open-overlay'
      
      $overlay = $ '<section id="overlay"><div><h1 class="title"><a class="close ir" href="">Close</a></h1><div class="content"></div></div></section>'
      $overlay.find('.title').prepend $target.data 'overlay-title'
      
      ###
      if $target.data 'overlay-class'
        $overlay.addClass $target.data('overlay-class')
      ###

      if $target.data 'overlay-content-selector'
        $overlay.find('.content').html $($target.data('overlay-content-selector')).html()
        $overlay.appendTo 'body'
        $overlay.height()
        $overlay.addClass 'opened'
      else if $target.data 'overlay-content-url'
        $.ajax
          type: 'GET'
          url:  $target.data 'overlay-content-url'
          success: (response) ->
            $overlay.find('.content').html response
            $overlay.appendTo 'body'
            $overlay.height()
            $overlay.addClass 'opened'
            return
      else if $target.data 'overlay-gallery-url'
        $.ajax
          type: 'GET'
          url:  $target.data 'overlay-gallery-url'
          success: (response) ->
            $overlay.addClass 'gallery'
            $overlay.find('.content').html response
            $overlay.appendTo 'body'
            $overlay.height()
            $overlay.addClass 'opened'

            $content = $overlay.find '.content'

            width = $content.find('.gallery').width()
            height = $content.find('.gallery').height()

            $pagination = $ '<ul class="pagination"></ul>'

            $content.find('.gallery img').each (i, img) ->
              $img = $ img

              if !i
                $img.closest('li').addClass 'current'
                $pagination.append '<li class="current"></li>'
              else
                $pagination.append '<li></li>'

              $img.wrap '<div></div>'

              if $img.attr('width') / $img.attr('height') > width / height
                $img.attr
                  width:  width
                  height: Math.floor $img.attr('height') * width / $img.attr 'width'
              else
                $img.attr
                  width:  Math.floor $img.attr('width') * height / $img.attr 'height'
                  height: height
              return

            $content.append $pagination

            $content.append '<nav class="navigation"><span class="prev"></span><span class="next"></span></nav>'

            $content.on 'click', (event) ->
              $target = $ event.target

              $current = $overlay.find '.gallery .current'
              $images = $current.closest('ul').find('li')

              if $target.is '.prev'
                $next = if $current.prev().length then $current.prev() else $images.eq $images.length - 1
              else if $target.is '.next'
                $next = if $current.next().length then $current.next() else $images.eq 0

              $current.removeClass 'current'
              $next.addClass 'current'
              $pagination.find('li').eq($next.prevAll().length).addClass('current').siblings().removeClass 'current'
              return

            $content.hammer()
              .on 'swipeleft', ->
                $overlay.find('.navigation .next').trigger 'click'
                return
              .on 'swiperight', ->
                $overlay.find('.navigation .prev').trigger 'click'
                return
            return
      else if $target.data('overlay-type') && $target.data('overlay-type') == 'map'
        $overlay.find('.content').append $('<div class="map"></div>')
        $overlay.appendTo 'body'
        $overlay.height()
        $overlay.addClass 'opened map'

        latitude =  parseFloat $target.data 'latitude'
        longitude = parseFloat $target.data 'longitude'
        
        success = (position) ->
          start = new google.maps.LatLng position.coords.latitude, position.coords.longitude
          end = new google.maps.LatLng latitude, longitude
          
          options =
            mapTypeControl:     false
            scrollwheel:        false
            streetViewControl:  false
            zoomControl:        false
            mapTypeId:          google.maps.MapTypeId.ROADMAP
            center:             start
            zoom:               15
            styles:             [{featureType: 'poi.business', stylers: [{visibility: 'off'}]}]
          map = new google.maps.Map $overlay.find('.map').get(0), options
          
          directionsService =   new google.maps.DirectionsService()
          directionsRenderer =  new google.maps.DirectionsRenderer map: map
          
          request =
            travelMode:   google.maps.TravelMode.WALKING
            origin:       start
            destination:  end
          
          directionsService.route request, (response, status) ->
            if status == google.maps.DirectionsStatus.OK
              directionsRenderer.setDirections response
            else
              # TODO
            return
          
          return
        
        error = (message) ->
          # TODO
          return
        
        if navigator.geolocation
          navigator.geolocation.getCurrentPosition success, error
        else
          # TODO
      else
        # TODO
        $overlay.appendTo 'body'
        $overlay.height()
        $overlay.addClass 'opened'
      
      return

    $(window).on 'resize', ->
      $overlay = $ '#overlay.opened.gallery'
      if $overlay.length
        $gallery = $overlay.find '.gallery'

        width = $gallery.width()
        height = $gallery.height()

        $gallery.find('img').each (i, img) ->
          $img = $ img

          if $img.attr('width') / $img.attr('height') > width / height
            $img.attr
              width:  width
              height: Math.floor $img.attr('height') * width / $img.attr 'width'
          else
            $img.attr
              width:  Math.floor $img.attr('width') * height / $img.attr 'height'
              height: height

      return
  
  return