// Copyright (c) 2010 Ivan Vanderbyl
// Originally found at http://ivan.ly/ui
// 
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

(function( $ ){
  // Simple wrapper around jQuery animate to simplify animating progress from your app
  // Inputs: Progress as a percent, Callback
  // TODO: Add options and jQuery UI support.
  $.fn.animateProgress = function(progress, callback) {    
    return this.each(function() {
      $(this).animate({
        width: progress+'%'
      }, {
        duration: 2000, 
        
        // swing or linear
        easing: 'swing',

        // this gets called every step of the animation, and updates the label
        step: function( progress ){
          var labelEl = $('.ui-label', this),
              valueEl = $('.value', labelEl);
          
          if (Math.ceil(progress) < 20 && $('.ui-label', this).is(":visible")) {
            labelEl.hide();
          }else{
            if (labelEl.is(":hidden")) {
              labelEl.fadeIn();
            };
          }
          
          if (Math.ceil(progress) == 100) {
            labelEl.text('完成');
            setTimeout(function() {
              labelEl.fadeOut();
            }, 1000);
          }else{
            valueEl.text(Math.ceil(progress) + '%');
          }
        },
        complete: function(scope, i, elem) {
          if (callback) {
            callback.call(this, i, elem );
          };
        }
      });
    });
  };
})( jQuery );

$(function() {
  // Hide the label at start
  $('.progress_bar .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar .ui-progress').animateProgress(79, function() {
    $(this).animateProgress(79, function() {
      setTimeout(function() {
        $('.progress_bar .ui-progress').animateProgress(100, function() {
          $('.main_content').slideDown();
          $('.fork_me').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 二
$(function() {
  // Hide the label at start
  $('.progress_bar1 .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar1 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar1 .ui-progress').animateProgress(71, function() {
    $(this).animateProgress(71, function() {
      setTimeout(function() {
        $('.progress_bar1 .ui-progress').animateProgress(100, function() {
          $('.main_content1').slideDown();
          $('.fork_me1').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 三
$(function() {
  // Hide the label at start
  $('.progress_bar3 .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar3 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar3 .ui-progress').animateProgress(65, function() {
    $(this).animateProgress(65, function() {
      setTimeout(function() {
        $('.progress_bar3 .ui-progress').animateProgress(100, function() {
          $('.main_content3').slideDown();
          $('.fork_me3').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 四
$(function() {
  // Hide the label at start
  $('.progress_bar4 .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar4 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar4 .ui-progress').animateProgress(60, function() {
    $(this).animateProgress(60, function() {
      setTimeout(function() {
        $('.progress_bar4 .ui-progress').animateProgress(100, function() {
          $('.main_content4').slideDown();
          $('.fork_me4').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 五
$(function() {
  // Hide the label at start
  $('.progress_bar5.ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar5 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar5 .ui-progress').animateProgress(58, function() {
    $(this).animateProgress(58, function() {
      setTimeout(function() {
        $('.progress_bar5 .ui-progress').animateProgress(100, function() {
          $('.main_content5').slideDown();
          $('.fork_me5').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 六
$(function() {
  // Hide the label at start
  $('.progress_bar6 .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar6 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar6 .ui-progress').animateProgress(54, function() {
    $(this).animateProgress(54, function() {
      setTimeout(function() {
        $('.progress_bar6 .ui-progress').animateProgress(100, function() {
          $('.main_content6').slideDown();
          $('.fork_me6').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 七
$(function() {
  // Hide the label at start
  $('.progress_bar7 .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar7 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar7 .ui-progress').animateProgress(46, function() {
    $(this).animateProgress(46, function() {
      setTimeout(function() {
        $('.progress_bar7 .ui-progress').animateProgress(100, function() {
          $('.main_content7').slideDown();
          $('.fork_me7').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 八
$(function() {
  // Hide the label at start
  $('.progress_bar8 .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar8 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar8 .ui-progress').animateProgress(40, function() {
    $(this).animateProgress(40, function() {
      setTimeout(function() {
        $('.progress_bar8 .ui-progress').animateProgress(100, function() {
          $('.main_content8').slideDown();
          $('.fork_me8').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 九
$(function() {
  // Hide the label at start
  $('.progress_bar9 .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar9 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar9 .ui-progress').animateProgress(39, function() {
    $(this).animateProgress(39, function() {
      setTimeout(function() {
        $('.progress_bar9 .ui-progress').animateProgress(100, function() {
          $('.main_content9').slideDown();
          $('.fork_me9').fadeIn();
        });
      }, 2000);
    });
  });
  
});

// 十
$(function() {
  // Hide the label at start
  $('.progress_bar10 .ui-progress .ui-label').hide();
  // Set initial value
  $('.progress_bar10 .ui-progress').css('width', '7%');

  // Simulate some progress
  $('.progress_bar10 .ui-progress').animateProgress(28, function() {
    $(this).animateProgress(28, function() {
      setTimeout(function() {
        $('.progress_bar10 .ui-progress').animateProgress(100, function() {
          $('.main_content10').slideDown();
          $('.fork_me10').fadeIn();
        });
      }, 2000);
    });
  });
  
});


