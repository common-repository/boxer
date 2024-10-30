
(function($) {
    $.fn.capslide = function(options) {
        var opts = $.extend({}, $.fn.capslide.defaults, options);
        return this.each(function() {
            $this = $(this);
            var o = $.meta ? $.extend({}, opts, $this.data()) : opts;
            
            if(!o.showcaption)    
                $this.find('.ic_caption').css('display','none');
            else 
                $this.find('.block_content').css('display','none');
                
            var _img = $this.find('img:first');
            var w = _img.attr('width');
            var h = _img.attr('height');
            $('.ic_caption', $this).css({
                'color' : o.caption_color,
                'background-color' : o.caption_bgcolor,
                'bottom' : '0px'
            });  
            $('.overlay',$this).css('background-color',o.overlay_bgcolor);
            $this.css({'width':w , 'height':h});
            $this.hover(
                function () {
                    if((navigator.appVersion).indexOf('MSIE 7.0') > 0)
                        $('.overlay',$(this)).show();
                    else
                        $('.overlay',$(this)).fadeIn();
                    if(!o.showcaption)
                        $(this).find('.ic_caption').slideDown(500);
                    else
                        $('.block_content',$(this)).slideDown(500);    
                },
                function () {
                    if((navigator.appVersion).indexOf('MSIE 7.0') > 0)
                        $('.overlay',$(this)).hide();
                    else
                        $('.overlay',$(this)).fadeOut();
                    if(!o.showcaption)
                        $(this).find('.ic_caption').slideUp(200);
                    else
                        $('.block_content',$(this)).slideUp(200);
                }
            );
        });
    };
    $.fn.capslide.defaults = {
        caption_color    : 'white',
        caption_bgcolor    : 'black',
        overlay_bgcolor : 'blue',
        border            : '1px solid #fff',
        showcaption        : true
    };
})(jQuery);