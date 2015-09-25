// Generated by CoffeeScript 1.6.3
(function() {
  $(function() {
    FastClick.attach(document.body);
  });

  // $(function() {
  //   var $helper, resize;
  //   $helper = $('#zoom-helper');
  //   resize = function() {
  //     var zoom;
  //     zoom = Math.round((detectZoom.zoom() || detectZoom.device()) * 100);
  //     $helper.width(zoom + '%');
  //   };
  //   $(window).on('resize', resize);
  //   resize();
  // });

  $(function() {
    var $header, className;
    $header = $('#header');
    className = 'opened';
    $('#mobile-header').find('.open-header, .close-header, p').on('click', function(e) {
      e.preventDefault();
      $header.toggleClass(className);
	  $('#checkin3').slideUp();
    });
  });

  $(function() {
    $('.fake-select select').each(function() {
      var $select;
      $select = $(this);
      $select.siblings('.value').text($select.find('option:selected').text());
    });
    $('.fake-select select').on('change', function() {
      var $select;
      $select = $(this);
      $select.siblings('.value').text($select.find('option:selected').text());
    });
  });

  $(function() {
    $('#header select').on('change', function(event) {
      var $target;
      $target = $(event.target);
      if ($target.attr('id') === 'language') {
        window.location.href = window.location.pathname.replace($target.data('current-language'), $target.val());
      } else if ($target.attr('id') === 'currency') {
        window.location.href = window.location.pathname + (window.location.search.length ? window.location.search + '&' : '?') + $.param({
          currency: $target.val()
        });
      }
    });
  });

  $(function() {
    var $inputs;
    if (!$('#mobile-header').is(':visible')) {
      $inputs = $('.field.date input[type="date"]');
      if ($inputs.length) {
        $inputs.attr({
          type: 'text'
        }).datepicker({
          buttonText: '',
          dateFormat: 'yy-mm-dd',
          defaultDate: 0,
          firstDay: 1,
          minDate: 0,
          showMonthAfterYear: true,
          showOn: 'button',
          onClose: function(date) {
            var $input;
            $input = $(this);
            if ($input.hasClass('from')) {
              //$input.closest('form').find('input.to').datepicker('option', 'minDate', date);
            } else if ($input.hasClass('to')) {
              //$input.closest('form').find('input.from').datepicker('option', 'maxDate', date);
            }
          }
        });
      }
    }
  });

  $(function() {
    var $carousel, $navigation, $pagination, $slides, $tooltips, disabledNavigation, i, resize, rotate, timer, _i, _ref;
    disabledNavigation = false;
    $carousel = $('#carousel');
    $slides = $carousel.find('.slides > li');
    $tooltips = $carousel.find('.tooltips li');
    $navigation = $carousel.find('.navigation');
    timer = null;
    $carousel.addClass('disabled-transitions');
    $slides.eq(0).addClass('current fast-tooltips');
    $slides.eq(0).height();
    $carousel.removeClass('disabled-transitions');
    resize = function() {
      var height, zoom;
      zoom = detectZoom.zoom() || detectZoom.device();
      height = $(window).height();
      if ($('#mobile-header').is(':visible')) {
        height -= $('#mobile-header').height();
      } else if ($carousel.hasClass('small')) {
        height *= .7;
      }
      $carousel.height(height * zoom);
    };
    $(window).on('resize', resize);
    resize();
    $pagination = $('<ul class="pagination"></ul>');
    for (i = _i = 0, _ref = $slides.length - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; i = 0 <= _ref ? ++_i : --_i) {
      $pagination.append('<li' + (!i ? ' class="current"' : '') + '></li>');
    }
    $carousel.append($pagination);
    $navigation.on('click', function(event) {
      var $currentSlide, $target, $targetSlide, nextCurrentClass, targetClass;
      $target = $(event.target);
      if (!disabledNavigation && $target.is('.prev, .next')) {
        disabledNavigation = true;
        $currentSlide = $slides.filter('.current');
        if ($target.is('.prev')) {
          $targetSlide = $currentSlide.prev().length ? $currentSlide.prev() : $slides.eq($slides.length - 1);
          targetClass = 'prev';
          nextCurrentClass = 'next';
        } else if ($target.is('.next')) {
          $targetSlide = $currentSlide.next().length ? $currentSlide.next() : $slides.eq(0);
          targetClass = 'next';
          nextCurrentClass = 'prev';
        }
        $carousel.addClass('disabled-transitions');
        $targetSlide.addClass(targetClass);
        $targetSlide.height();
        $carousel.removeClass('disabled-transitions');
        $currentSlide.removeClass('current fast-tooltips').addClass(nextCurrentClass);
        $targetSlide.removeClass(targetClass).addClass('current');
        $pagination.find('li').eq($targetSlide.prevAll().length).addClass('current').siblings().removeClass('current');
      }
    });
    $navigation.on('mouseenter', function() {
      window.clearTimeout(timer);
    });
    $navigation.on('mouseleave', function() {
      rotate();
    });
    $carousel.hammer().on('swipeleft', function() {
      $navigation.find('.next').trigger('click');
    }).on('swiperight', function() {
      $navigation.find('.prev').trigger('click');
    });
    $slides.on('transitionend webkitTransitionEnd', function(event) {
      var $target;
      $target = $(event.target);
      if ($target.is('.slides > .current')) {
        $target.addClass('fast-tooltips');
      }
      if ($target.is('.slides > .prev, .slides > .next')) {
        $(this).removeClass('prev next');
        disabledNavigation = false;
      }
    });
    rotate = function() {
      timer = window.setTimeout(function() {
        $navigation.find('.next').trigger('click');
        rotate();
      }, 8000);
    };
    $tooltips.on('mouseenter', function() {
      var $div;
      window.clearTimeout(timer);
      $div = $(this).find('div');
      $div.css({
        marginTop: -1 * Math.floor($div.height() / 2)
      });
    });
    $tooltips.on('mouseleave', function() {
      rotate();
    });
    rotate();
  });

  $(function() {
    var elements;
    elements = [
       {
        selector: '',
        headerSelector: '.page-title',
        headerOffset: 30,
        footerSelector: '#footer',
        footerOffset: 30
      }
    ];
    elements.forEach(function(element) {
      var $element, $footer, $header, reposition;
      $element = $(element.selector);
      if ($element.length && $element.css('position') === 'fixed') {
        element.fixed = true;
        element.height = $element.outerHeight();
        if (element.headerSelector) {
          $header = $(element.headerSelector);
          element.fixedTop = $header.outerHeight() + element.headerOffset;
          element.scrollTop = element.fixedTop - (Math.floor($element.position().top) + parseInt($element.css('marginTop')));
        }
        if (element.footerSelector) {
          $footer = $(element.footerSelector);
          element.fixedBottom = $(document).outerHeight() - $footer.outerHeight() - element.footerOffset - $element.outerHeight();
          element.scrollBottom = element.fixedBottom - (Math.floor($element.position().top) + parseInt($element.css('marginTop')));
        }
        reposition = function() {
          var top;
          top = $element.offset().top;
          if (element.fixed) {
            if (top < element.fixedTop) {
              element.fixed = false;
              element.restoreMargin = $element.css('marginTop');
              $element.css({
                position: 'absolute',
                top: element.fixedTop + 'px',
                marginTop: 0
              });
            } else if (top > element.fixedBottom) {
              element.fixed = false;
              element.restoreMargin = $element.css('marginTop');
              $element.css({
                position: 'absolute',
                top: element.fixedBottom + 'px',
                marginTop: 0
              });
            }
          } else {
            if ($(window).scrollTop() > element.scrollTop && $(window).scrollTop() < element.scrollBottom) {
              element.fixed = true;
              $element.css({
                position: 'fixed',
                top: $element.data('top'),
                marginTop: element.restoreMargin
              });
            }
          }
        };
        $(window).on('scroll', function() {
          reposition();
        });
        reposition();
      }
    });
  });

  $(function() {
    var $map, bubble, map, options;
    if ($('#map-container').length) {
      $map = $('#map-container .map');
      options = {
        mapTypeControl: false,
        scrollwheel: false,
        streetViewControl: false,
        zoomControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: new google.maps.LatLng(47.498072, 19.062733),
        zoom: 15,
        styles: [
          {
            featureType: 'poi.business',
            stylers: [
              {
                visibility: 'off'
              }
            ]
          }
        ]
      };
      map = new google.maps.Map($('#map-container > div').get(0), options);
      bubble = new google.maps.InfoWindow();
      $.ajax({
        url: $map.data('poi-url'),
        type: 'GET',
        dataType: 'json',
        success: function(pois) {
          var bounds, icon, latlng, marker, poi, shape, _i, _len;
          bounds = new google.maps.LatLngBounds();
          for (_i = 0, _len = pois.length; _i < _len; _i++) {
            poi = pois[_i];
            latlng = new google.maps.LatLng(poi.lat, poi.lng);
            bounds.extend(latlng);
            if (poi.image) {
              icon = {
                url: poi.image,
                size: new google.maps.Size(174, 103),
                anchor: new google.maps.Point(44, 52),
                zIndex: poi.zIndex
              };
              shape = {
                type: 'poly',
                coord: [0, 0, 174, 0, 174, 90, 101, 90, 88, 103, 76, 90, 0, 90]
              };
              marker = new google.maps.Marker({
                map: map,
                position: latlng,
                title: poi.name,
                icon: icon,
                shape: shape,
                anchorPoint: new google.maps.Point(0, -52),
                zIndex: poi.zIndex
              });
            } else {
              marker = new google.maps.Marker({
                map: map,
                position: latlng,
                title: poi.name,
                zIndex: poi.zIndex
              });
              google.maps.event.addListener(marker, 'click', function() {
                bubble.setContent(this.title);
                bubble.open(map, this);
              });
            }
            map.fitBounds(bounds);
          }
        },
        error: function(request, status, error) {
          console.log(request, status, error);
        }
      });
    }
  });

  $(function() {
    $('.rooms').on('click', function(e) {
      var $extra, $li, $target;
      $target = $(e.target);
      if ($target.is('h2 a') || $target.is('.open') || $target.is('.close')) {
        e.preventDefault();
        $li = $target.closest('li');
        $extra = $li.find('.extra');
        if ($target.is('h2 a')) {
          if (!$li.hasClass('opened')) {
            $li.find('.open').trigger('click');
          } else {
            $li.find('.close').trigger('click');
          }
        } else if ($target.is('.open')) {
          $li.addClass('opened');
          $extra.hide().slideDown(200);
        } else if ($target.is('.close')) {
          $li.removeClass('opened');
          $extra.slideUp(200);
        }
      }
    });
  });

  $(function() {
    var $container;
    $container = $('#booking-filter');
    $container.find('.open-filter').on('click', function(e) {
      var $fieldset;
      e.preventDefault();
      $fieldset = $container.find('.filter > fieldset');
      if ($container.hasClass('opened')) {
        $fieldset.slideUp(200, function() {
          $container.removeClass('opened');
        });
      } else {
        $container.addClass('opened');
        $fieldset.hide().slideDown(200);
      }
    });
  });

  $(function() {
    var updateSummary;
    if ($('form.update-summary').length || $('form.auto-update-summary').length) {
      updateSummary = function() {
        var $form;
        $form = $('form.update-summary').length ? $('form.update-summary') : $('form.auto-update-summary');
        $.ajax({
          url: $form.data('refresh'),
          type: $form.attr('method'),
          data: $form.serialize(),
          dataType: 'json',
          success: function(response) {
            $('#booking-summary .details li').addClass('inactive').text(function() {
              $(this).data('label');
            });
            Object.keys(response.details).forEach(function(key) {
              var $container;
              if (response.details[key].length) {
                $container = $('#booking-summary .details li.' + key);
                $container.removeClass('inactive').empty();
                response.details[key].forEach(function(data) {
                  var $p;
                  if (data.title) {
                    $container.text(data.title);
                  } else {
                    $p = $('<p></p>');
                    ['name', 'description', 'price'].forEach(function(index) {
                      $('<span class="' + index + '"></span>').text(data[index]).appendTo($p);
                    });
                    $p.appendTo($container);
                  }
                });
              }
            });
            $('#booking-summary .total').text(response.total);
          },
          error: function(request, status, error) {
            console.log(request, status, error);
          }
        });
      };
      $('form.update-summary').on('change', updateSummary);
      updateSummary();
    }
  });

  $(function() {
    if ($('#mobile-header').is(':visible') && $(window).height() < 640) {
      $('#header').addClass('small');
    }
  });

  $(function() {
    if ($('.open-overlay').length) {
      $('body').on('click', function(e) {
        var $target;
        $target = $(e.target);
        if ($target.is('#overlay .close')) {
          e.preventDefault();
          $target.closest('#overlay').removeClass('opened');
          window.setTimeout(function() {
            $target.closest('#overlay').remove();
          }, 200);
        }
      });
      $('.open-overlay').on('click', function(e) {
        var $overlay, $target, error, latitude, longitude, success;
        e.preventDefault();
        $target = $(e.target).closest('.open-overlay');
        $overlay = $('<section id="overlay"><div><h1 class="title"></h1><a class="close" href="" style="position: absolute; z-index: 999999; top: 0; right: 0; font-size: 30px; margin: 15px; text-decoration: none;">X</a><div class="clearfix"></div><div class="content"></div></div></section>');
        $overlay.find('.title').prepend($target.data('overlay-title'));
        /*
        if $target.data 'overlay-class'
          $overlay.addClass $target.data('overlay-class')
        */

        if ($target.data('overlay-content-selector')) {
          $overlay.find('.content').html($($target.data('overlay-content-selector')).html());
          $overlay.appendTo('body');
          $overlay.height();
          $overlay.addClass('opened');
        } else if ($target.data('overlay-content-url')) {
          $.ajax({
            type: 'GET',
            url: $target.data('overlay-content-url'),
            success: function(response) {
              $overlay.find('.content').html(response);
              $overlay.appendTo('body');
              $overlay.height();
              $overlay.addClass('opened');
            }
          });
        } else if ($target.data('overlay-gallery-url')) {
          $.ajax({
            type: 'GET',
            url: $target.data('overlay-gallery-url'),
            success: function(response) {
              var $content, $pagination, height, width;
              $overlay.addClass('gallery');
              $overlay.find('.content').html(response);
              $overlay.appendTo('body');
              $overlay.height();
              $overlay.addClass('opened');
              $content = $overlay.find('.content');
              width = $content.find('.gallery').width();
              height = $content.find('.gallery').height();
              $pagination = $('<ul class="pagination"></ul>');
              $content.find('.gallery img').each(function(i, img) {
                var $img;
                $img = $(img);
                if (!i) {
                  $img.closest('li').addClass('current');
                  $pagination.append('<li class="current"></li>');
                } else {
                  $pagination.append('<li></li>');
                }
                $img.wrap('<div></div>');
                if ($img.attr('width') / $img.attr('height') >  width / height) {
                  $img.attr({
                    width: width,
                    height: Math.floor($img.attr('height') * width / $img.attr('width'))
                  });
                } else {
                  $img.attr({
                    width: Math.floor($img.attr('width') * height / $img.attr('height')),
                    height: height
                  });
                }
              });
              $content.append($pagination);
              $content.append('<nav class="navigation"><span class="prev"></span><span class="next"></span></nav>');
              $content.on('click', function(event) {
                var $current, $images, $next;
                $target = $(event.target);
                $current = $overlay.find('.gallery .current');
                $images = $current.closest('ul').find('li');
                if ($target.is('.prev')) {
                  $next = $current.prev().length ? $current.prev() : $images.eq($images.length - 1);
                } else {
                  $next = $current.next().length ? $current.next() : $images.eq(0);
                }
                $current.removeClass('current');
                $next.addClass('current');
                $pagination.find('li').eq($next.prevAll().length).addClass('current').siblings().removeClass('current');
              });
              $content.hammer().on('swipeleft', function() {
                $overlay.find('.navigation .next').trigger('click');
              }).on('swiperight', function() {
                $overlay.find('.navigation .prev').trigger('click');
              });
            }
          });
        } else if ($target.data('overlay-type') && $target.data('overlay-type') === 'map') {
          $overlay.find('.content').append($('<div class="map"></div>'));
          $overlay.appendTo('body');
          $overlay.height();
          $overlay.addClass('opened map');
          latitude = parseFloat($target.data('latitude'));
          longitude = parseFloat($target.data('longitude'));
          success = function(position) {
            var directionsRenderer, directionsService, end, map, options, request, start;
            start = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            end = new google.maps.LatLng(latitude, longitude);
            options = {
              mapTypeControl: false,
              scrollwheel: false,
              streetViewControl: false,
              zoomControl: true,
              mapTypeId: google.maps.MapTypeId.ROADMAP,
              center: start,
              zoom: 15,
              styles: [
                {
                  featureType: 'poi.business',
                  stylers: [
                    {
                      visibility: 'off'
                    }
                  ]
                }
              ]
            };
            map = new google.maps.Map($overlay.find('.map').get(0), options);
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
              map: map
            });
            request = {
              travelMode: google.maps.TravelMode.WALKING,
              origin: start,
              destination: end
            };
            directionsService.route(request, function(response, status) {
              if (status === google.maps.DirectionsStatus.OK) {
                directionsRenderer.setDirections(response);
              } else {

              }
            });
          };
          error = function(message) {};
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(success, error);
          } else {

          }
        } else {
          $overlay.appendTo('body');
          $overlay.height();
          $overlay.addClass('opened');
        }
      });
      $(window).on('resize', function() {
        var $gallery, $overlay, height, width;
        $overlay = $('#overlay.opened.gallery');
        if ($overlay.length) {
          $gallery = $overlay.find('.gallery');
          width = $gallery.width();
          height = $gallery.height();
          $gallery.find('img').each(function(i, img) {
            var $img;
            $img = $(img);
            if ($img.attr('width') / $img.attr('height') >  width / height) {
              return $img.attr({
                width: width,
                height: Math.floor($img.attr('height') * width / $img.attr('width'))
              });
            } else {
              return $img.attr({
                width: Math.floor($img.attr('width') * height / $img.attr('height')),
                height: height
              });
            }
          });
        }
      });
    }
  });

}).call(this);
