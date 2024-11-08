/**
 * @license 
 * jQuery Tools 1.2.5 Rangeinput - HTML5 <input type="range" /> for humans
 * 
 * NO COPYRIGHTS OR LICENSES. DO WHAT YOU LIKE.
 * 
 * http://flowplayer.org/tools/rangeinput/
 *
 * Since: Mar 2010
 * Date:    Wed Sep 22 06:02:10 2010 +0000 
 */
(function($) {
     
    $.tools = $.tools || {version: '1.2.5'};
     
    var tool;
    
    tool = $.tools.rangeinput = {
                                    
        conf: {
            min: 0,
            max: 100,        // as defined in the standard
            step: 'any',     // granularity of the value. a non-zero float or int (or "any")
            steps: 0,
            value: 0,            
            precision: undefined,
            vertical: 0,
            keyboard: true,
            progress: false,
            speed: 100,
            
            // set to null if not needed
            css: {
                input:        'range',
                slider:         'slider',
                progress:     'progress',
                handle:         'handle'                    
            }

        } 
    };
    
//{{{ fn.drag
        
    /* 
        FULL featured drag and drop. 0.7 kb minified, 0.3 gzipped. done.
        Who told d'n'd is rocket science? Usage:
        
        $(".myelement").drag({y: false}).bind("drag", function(event, x, y) {
            // do your custom thing
        });
         
        Configuration: 
            x: true,         // enable horizontal drag
            y: true,         // enable vertical drag 
            drag: true         // true = perform drag, false = only fire events 
            
        Events: dragStart, drag, dragEnd. 
    */
    var doc, draggable;
    
    $.fn.drag = function(conf) {
        
        // disable IE specialities
        document.ondragstart = function () { return false; };
        
        conf = $.extend({x: true, y: true, drag: true}, conf);
    
        doc = doc || $(document).bind("mousedown mouseup", function(e) {
                
            var el = $(e.target);  
            
            // start 
            if (e.type == "mousedown" && el.data("drag")) {
                
                var offset = el.position(),
                     x0 = e.pageX - offset.left, 
                     y0 = e.pageY - offset.top,
                     start = true;    
                
                doc.bind("mousemove.drag", function(e) {  
                    var x = e.pageX -x0, 
                         y = e.pageY -y0,
                         props = {};
                    
                    if (conf.x) { props.left = x; }
                    if (conf.y) { props.top = y; } 
                    
                    if (start) {
                        el.trigger("dragStart");
                        start = false;
                    }
                    if (conf.drag) { el.css(props); }
                    el.trigger("drag", [y, x]);
                    draggable = el;
                }); 
                
                e.preventDefault();
                
            } else {
                
                try {
                    if (draggable) {  
                        draggable.trigger("dragEnd");  
                    }
                } finally { 
                    doc.unbind("mousemove.drag");
                    draggable = null; 
                }
            } 
                            
        });
        
        return this.data("drag", true); 
    };    

//}}}
    

    
    function round(value, precision) {
        var n = Math.pow(10, precision);
        return Math.round(value * n) / n;
    }
    
    // get hidden element's width or height even though it's hidden
    function dim(el, key) {
        var v = parseInt(el.css(key), 10);
        if (v) { return v; }
        var s = el[0].currentStyle; 
        return s && s.width && parseInt(s.width, 10);    
    }
    
    function hasEvent(el) {
        var e = el.data("events");
        return e && e.onSlide;
    }
    
    function RangeInput(input, conf) {
        
        // private variables
        var self = this,  
             css = conf.css, 
             root = $("<div><div/><a href='#'/></div>").data("rangeinput", self),    
             vertical,        
             value,            // current value
             origo,            // handle's start point
             len,                // length of the range
             pos;                // current position of the handle        
         
        // create range     
        input.before(root);    
        
        var handle = root.addClass(css.slider).find("a").addClass(css.handle),     
             progress = root.find("div").addClass(css.progress);
        
        // get (HTML5) attributes into configuration
        $.each("min,max,step,value".split(","), function(i, key) {
            var val = input.attr(key);
            if (parseFloat(val)) {
                conf[key] = parseFloat(val, 10);
            }
        });               
        
        var range = conf.max - conf.min, 
             step = conf.step == 'any' ? 0 : conf.step,
             precision = conf.precision;
             
        if (precision === undefined) {
            try {
                precision = step.toString().split(".")[1].length;
            } catch (err) {
                precision = 0;    
            }
        }  
        
        // Replace built-in range input (type attribute cannot be changed)
        if (input.attr("type") == 'range') {
            var tmp = $("<input/>");
            $.each("class,disabled,id,maxlength,name,readonly,required,size,style,tabindex,title,value".split(","), function(i, attr)  {
                tmp.attr(attr, input.attr(attr));        
            });
            tmp.val(conf.value);
            input.replaceWith(tmp);
            input = tmp;
        }
        
        input.addClass(css.input);
             
        var fire = $(self).add(input), fireOnSlide = true;

        
        /**
             The flesh and bone of this tool. All sliding is routed trough this.
            
            @param evt types include: click, keydown, blur and api (setValue call)
            @param isSetValue when called trough setValue() call (keydown, blur, api)
            
            vertical configuration gives additional complexity. 
         */
        function slide(evt, x, val, isSetValue) { 

            // calculate value based on slide position
            if (val === undefined) {
                val = x / len * range;  
                
            // x is calculated based on val. we need to strip off min during calculation    
            } else if (isSetValue) {
                val -= conf.min;    
            }
            
            // increment in steps
            if (step) {
                val = Math.round(val / step) * step;
            }

            // count x based on value or tweak x if stepping is done
            if (x === undefined || step) {
                x = val * len / range;    
            }  
            
            // crazy value?
            if (isNaN(val)) { return self; }       
            
            // stay within range
            x = Math.max(0, Math.min(x, len));  
            val = x / len * range;   

            if (isSetValue || !vertical) {
                val += conf.min;
            }
            
            // in vertical ranges value rises upwards
            if (vertical) {
                if (isSetValue) {
                    x = len -x;
                } else {
                    val = conf.max - val;    
                }
            }    
            
            // precision
            val = round(val, precision); 
            
            // onSlide
            var isClick = evt.type == "click";
            if (fireOnSlide && value !== undefined && !isClick) {
                evt.type = "onSlide";           
                fire.trigger(evt, [val, x]); 
                if (evt.isDefaultPrevented()) { return self; }  
            }                
            
            // speed & callback
            var speed = isClick ? conf.speed : 0,
                 callback = isClick ? function()  {
                    evt.type = "change";
                    fire.trigger(evt, [val]);
                 } : null;
            
            if (vertical) {
                handle.animate({top: x}, speed, callback);
                if (conf.progress) { 
                    progress.animate({height: len - x + handle.width() / 2}, speed);    
                }                
                
            } else {
                handle.animate({left: x}, speed, callback);
                if (conf.progress) { 
                    progress.animate({width: x + handle.width() / 2}, speed); 
                }
            }
            
            // store current value
            value = val; 
            pos = x;             
            
            // se input field's value
            input.val(val);

            return self;
        } 
        
        
        $.extend(self, {  
            
            getValue: function() {
                return value;    
            },
            
            setValue: function(val, e) {
                init();
                return slide(e || $.Event("api"), undefined, val, true); 
            },               
            
            getConf: function() {
                return conf;    
            },
            
            getProgress: function() {
                return progress;    
            },

            getHandle: function() {
                return handle;    
            },            
            
            getInput: function() {
                return input;    
            }, 
                
            step: function(am, e) {
                e = e || $.Event();
                var step = conf.step == 'any' ? 1 : conf.step;
                self.setValue(value + step * (am || 1), e);    
            },
            
            // HTML5 compatible name
            stepUp: function(am) { 
                return self.step(am || 1);
            },
            
            // HTML5 compatible name
            stepDown: function(am) { 
                return self.step(-am || -1);
            }
            
        });
        
        // callbacks
        $.each("onSlide,change".split(","), function(i, name) {
                
            // from configuration
            if ($.isFunction(conf[name]))  {
                $(self).bind(name, conf[name]);    
            }
            
            // API methods
            self[name] = function(fn) {
                if (fn) { $(self).bind(name, fn); }
                return self;    
            };
        }); 
            

        // dragging                                          
        handle.drag({drag: false}).bind("dragStart", function() {
        
            /* do some pre- calculations for seek() function. improves performance */            
            init();
            
            // avoid redundant event triggering (= heavy stuff)
            fireOnSlide = hasEvent($(self)) || hasEvent(input);
            
                
        }).bind("drag", function(e, y, x) {        
            
            if (input.is(":disabled")) { return false; } 
            slide(e, vertical ? y : x); 
            
        }).bind("dragEnd", function(e) {
            if (!e.isDefaultPrevented()) {
                e.type = "change";
                fire.trigger(e, [value]);     
            }
            
        }).click(function(e) {
            return e.preventDefault();     
        });        
        
        // clicking
        root.click(function(e) { 
            if (input.is(":disabled") || e.target == handle[0]) { 
                return e.preventDefault(); 
            }                  
            init(); 
            var fix = handle.width() / 2;   
            slide(e, vertical ? len-origo-fix + e.pageY  : e.pageX -origo -fix);  
        });

        if (conf.keyboard) {
            
            input.keydown(function(e) {
        
                if (input.attr("readonly")) { return; }
                
                var key = e.keyCode,
                     up = $([75, 76, 38, 33, 39]).index(key) != -1,
                     down = $([74, 72, 40, 34, 37]).index(key) != -1;                     
                     
                if ((up || down) && !(e.shiftKey || e.altKey || e.ctrlKey)) {
                
                    // UP:     k=75, l=76, up=38, pageup=33, right=39            
                    if (up) {
                        self.step(key == 33 ? 10 : 1, e);
                        
                    // DOWN:    j=74, h=72, down=40, pagedown=34, left=37
                    } else if (down) {
                        self.step(key == 34 ? -10 : -1, e); 
                    } 
                    return e.preventDefault();
                } 
            });
        }
        
        
        input.blur(function(e) {    
            var val = $(this).val();
            if (val !== value) {
                self.setValue(val, e);
            }
        });    
        
        
        // HTML5 DOM methods
        $.extend(input[0], { stepUp: self.stepUp, stepDown: self.stepDown});
        
        
        // calculate all dimension related stuff
        function init() { 
             vertical = conf.vertical || dim(root, "height") > dim(root, "width");
         
            if (vertical) {
                len = dim(root, "height") - dim(handle, "height");
                origo = root.offset().top + len; 
                
            } else {
                len = dim(root, "width") - dim(handle, "width");
                origo = root.offset().left;      
            }       
        }
        
        function begin() {
            init();    
            self.setValue(conf.value !== undefined ? conf.value : conf.min);
        } 
        begin();
        
        // some browsers cannot get dimensions upon initialization
        if (!len) {  
            $(window).load(begin);
        }
    }
    
    $.expr[':'].range = function(el) {
        var type = el.getAttribute("type");
        return type && type == 'range' || !!$(el).filter("input").data("rangeinput");
    };
    
    
    // jQuery plugin implementation
    $.fn.rangeinput = function(conf) {

        // already installed
        if (this.data("rangeinput")) { return this; } 
        
        // extend configuration with globals
        conf = $.extend(true, {}, tool.conf, conf);        
        
        var els;
        
        this.each(function() {                
            var el = new RangeInput($(this), $.extend(true, {}, conf));         
            var input = el.getInput().data("rangeinput", el);
            els = els ? els.add(input) : input;    
        });        
        
        return els ? els : this; 
    };    
    
    
}) (jQuery);

// Chosen, a Select Box Enhancer for jQuery and Protoype
// by Patrick Filler for Harvest, http://getharvest.com
// 
// Version 0.9.7
// Full source at https://github.com/harvesthq/chosen
// Copyright (c) 2011 Harvest http://getharvest.com

// MIT License, https://github.com/harvesthq/chosen/blob/master/LICENSE.md
// This file is generated by `cake build`, do not edit it by hand.
((function(){var a;a=function(){function a(){this.options_index=0,this.parsed=[]}return a.prototype.add_node=function(a){return a.nodeName==="OPTGROUP"?this.add_group(a):this.add_option(a)},a.prototype.add_group=function(a){var b,c,d,e,f,g;b=this.parsed.length,this.parsed.push({array_index:b,group:!0,label:a.label,children:0,disabled:a.disabled}),f=a.childNodes,g=[];for(d=0,e=f.length;d<e;d++)c=f[d],g.push(this.add_option(c,b,a.disabled));return g},a.prototype.add_option=function(a,b,c){if(a.nodeName==="OPTION")return a.text!==""?(b!=null&&(this.parsed[b].children+=1),this.parsed.push({array_index:this.parsed.length,options_index:this.options_index,value:a.value,text:a.text,html:a.innerHTML,selected:a.selected,disabled:c===!0?c:a.disabled,group_array_index:b,classes:a.className,style:a.style.cssText})):this.parsed.push({array_index:this.parsed.length,options_index:this.options_index,empty:!0}),this.options_index+=1},a}(),a.select_to_array=function(b){var c,d,e,f,g;d=new a,g=b.childNodes;for(e=0,f=g.length;e<f;e++)c=g[e],d.add_node(c);return d.parsed},this.SelectParser=a})).call(this),function(){var a,b;b=this,a=function(){function a(a,b){this.form_field=a,this.options=b!=null?b:{},this.set_default_values(),this.is_multiple=this.form_field.multiple,this.default_text_default=this.is_multiple?"Select Some Options":"Select an Option",this.setup(),this.set_up_html(),this.register_observers(),this.finish_setup()}return a.prototype.set_default_values=function(){var a=this;return this.click_test_action=function(b){return a.test_active_click(b)},this.activate_action=function(b){return a.activate_field(b)},this.active_field=!1,this.mouse_on_container=!1,this.results_showing=!1,this.result_highlighted=null,this.result_single_selected=null,this.allow_single_deselect=this.options.allow_single_deselect!=null&&this.form_field.options[0]!=null&&this.form_field.options[0].text===""?this.options.allow_single_deselect:!1,this.disable_search_threshold=this.options.disable_search_threshold||0,this.choices=0,this.results_none_found=this.options.no_results_text||"No results match"},a.prototype.mouse_enter=function(){return this.mouse_on_container=!0},a.prototype.mouse_leave=function(){return this.mouse_on_container=!1},a.prototype.input_focus=function(a){var b=this;if(!this.active_field)return setTimeout(function(){return b.container_mousedown()},50)},a.prototype.input_blur=function(a){var b=this;if(!this.mouse_on_container)return this.active_field=!1,setTimeout(function(){return b.blur_test()},100)},a.prototype.result_add_option=function(a){var b,c;return a.disabled?"":(a.dom_id=this.container_id+"_o_"+a.array_index,b=a.selected&&this.is_multiple?[]:["active-result"],a.selected&&b.push("result-selected"),a.group_array_index!=null&&b.push("group-option"),a.classes!==""&&b.push(a.classes),c=a.style.cssText!==""?' style="'+a.style+'"':"",'<li id="'+a.dom_id+'" class="'+b.join(" ")+'"'+c+">"+a.html+"</li>")},a.prototype.results_update_field=function(){return this.result_clear_highlight(),this.result_single_selected=null,this.results_build()},a.prototype.results_toggle=function(){return this.results_showing?this.results_hide():this.results_show()},a.prototype.results_search=function(a){return this.results_showing?this.winnow_results():this.results_show()},a.prototype.keyup_checker=function(a){var b,c;b=(c=a.which)!=null?c:a.keyCode,this.search_field_scale();switch(b){case 8:if(this.is_multiple&&this.backstroke_length<1&&this.choices>0)return this.keydown_backstroke();if(!this.pending_backstroke)return this.result_clear_highlight(),this.results_search();break;case 13:a.preventDefault();if(this.results_showing)return this.result_select(a);break;case 27:return this.results_showing&&this.results_hide(),!0;case 9:case 38:case 40:case 16:case 91:case 17:break;default:return this.results_search()}},a.prototype.generate_field_id=function(){var a;return a=this.generate_random_id(),this.form_field.id=a,a},a.prototype.generate_random_char=function(){var a,b,c;return a="0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ",c=Math.floor(Math.random()*a.length),b=a.substring(c,c+1)},a}(),b.AbstractChosen=a}.call(this),function(){var a,b,c,d,e=Object.prototype.hasOwnProperty,f=function(a,b){function d(){this.constructor=a}for(var c in b)e.call(b,c)&&(a[c]=b[c]);return d.prototype=b.prototype,a.prototype=new d,a.__super__=b.prototype,a};d=this,a=jQuery,a.fn.extend({chosen:function(c){return!a.browser.msie||a.browser.version!=="6.0"&&a.browser.version!=="7.0"?a(this).each(function(d){if(!a(this).hasClass("chzn-done"))return new b(this,c)}):this}}),b=function(b){function e(){e.__super__.constructor.apply(this,arguments)}return f(e,b),e.prototype.setup=function(){return this.form_field_jq=a(this.form_field),this.is_rtl=this.form_field_jq.hasClass("chzn-rtl")},e.prototype.finish_setup=function(){return this.form_field_jq.addClass("chzn-done")},e.prototype.set_up_html=function(){var b,d,e,f;return this.container_id=this.form_field.id.length?this.form_field.id.replace(/(:|\.)/g,"_"):this.generate_field_id(),this.container_id+="_chzn",this.f_width=this.form_field_jq.outerWidth(),this.default_text=this.form_field_jq.data("placeholder")?this.form_field_jq.data("placeholder"):this.default_text_default,b=a("<div />",{id:this.container_id,"class":"chzn-container"+(this.is_rtl?" chzn-rtl":""),style:"width: "+this.f_width+"px;"}),this.is_multiple?b.html('<ul class="chzn-choices"><li class="search-field"><input type="text" value="'+this.default_text+'" class="default" autocomplete="off" style="width:25px;" /></li></ul><div class="chzn-drop" style="left:-9000px;"><ul class="chzn-results"></ul></div>'):b.html('<a href="javascript:void(0)" class="chzn-single"><span>'+this.default_text+'</span><div><b></b></div></a><div class="chzn-drop" style="left:-9000px;"><div class="chzn-search"><input type="text" autocomplete="off" /></div><ul class="chzn-results"></ul></div>'),this.form_field_jq.hide().after(b),this.container=a("#"+this.container_id),this.container.addClass("chzn-container-"+(this.is_multiple?"multi":"single")),this.dropdown=this.container.find("div.chzn-drop").first(),d=this.container.height(),e=this.f_width-c(this.dropdown),this.dropdown.css({width:e+"px",top:d+"px"}),this.search_field=this.container.find("input").first(),this.search_results=this.container.find("ul.chzn-results").first(),this.search_field_scale(),this.search_no_results=this.container.find("li.no-results").first(),this.is_multiple?(this.search_choices=this.container.find("ul.chzn-choices").first(),this.search_container=this.container.find("li.search-field").first()):(this.search_container=this.container.find("div.chzn-search").first(),this.selected_item=this.container.find(".chzn-single").first(),f=e-c(this.search_container)-c(this.search_field),this.search_field.css({width:f+"px"})),this.results_build(),this.set_tab_index(),this.form_field_jq.trigger("liszt:ready",{chosen:this})},e.prototype.register_observers=function(){var a=this;return this.container.mousedown(function(b){return a.container_mousedown(b)}),this.container.mouseup(function(b){return a.container_mouseup(b)}),this.container.mouseenter(function(b){return a.mouse_enter(b)}),this.container.mouseleave(function(b){return a.mouse_leave(b)}),this.search_results.mouseup(function(b){return a.search_results_mouseup(b)}),this.search_results.mouseover(function(b){return a.search_results_mouseover(b)}),this.search_results.mouseout(function(b){return a.search_results_mouseout(b)}),this.form_field_jq.bind("liszt:updated",function(b){return a.results_update_field(b)}),this.search_field.blur(function(b){return a.input_blur(b)}),this.search_field.keyup(function(b){return a.keyup_checker(b)}),this.search_field.keydown(function(b){return a.keydown_checker(b)}),this.is_multiple?(this.search_choices.click(function(b){return a.choices_click(b)}),this.search_field.focus(function(b){return a.input_focus(b)})):this.container.click(function(a){return a.preventDefault()})},e.prototype.search_field_disabled=function(){this.is_disabled=this.form_field_jq[0].disabled;if(this.is_disabled)return this.container.addClass("chzn-disabled"),this.search_field[0].disabled=!0,this.is_multiple||this.selected_item.unbind("focus",this.activate_action),this.close_field();this.container.removeClass("chzn-disabled"),this.search_field[0].disabled=!1;if(!this.is_multiple)return this.selected_item.bind("focus",this.activate_action)},e.prototype.container_mousedown=function(b){var c;if(!this.is_disabled)return c=b!=null?a(b.target).hasClass("search-choice-close"):!1,b&&b.type==="mousedown"&&b.stopPropagation(),!this.pending_destroy_click&&!c?(this.active_field?!this.is_multiple&&b&&(a(b.target)[0]===this.selected_item[0]||a(b.target).parents("a.chzn-single").length)&&(b.preventDefault(),this.results_toggle()):(this.is_multiple&&this.search_field.val(""),a(document).click(this.click_test_action),this.results_show()),this.activate_field()):this.pending_destroy_click=!1},e.prototype.container_mouseup=function(a){if(a.target.nodeName==="ABBR")return this.results_reset(a)},e.prototype.blur_test=function(a){if(!this.active_field&&this.container.hasClass("chzn-container-active"))return this.close_field()},e.prototype.close_field=function(){return a(document).unbind("click",this.click_test_action),this.is_multiple||(this.selected_item.attr("tabindex",this.search_field.attr("tabindex")),this.search_field.attr("tabindex",-1)),this.active_field=!1,this.results_hide(),this.container.removeClass("chzn-container-active"),this.winnow_results_clear(),this.clear_backstroke(),this.show_search_field_default(),this.search_field_scale()},e.prototype.activate_field=function(){return!this.is_multiple&&!this.active_field&&(this.search_field.attr("tabindex",this.selected_item.attr("tabindex")),this.selected_item.attr("tabindex",-1)),this.container.addClass("chzn-container-active"),this.active_field=!0,this.search_field.val(this.search_field.val()),this.search_field.focus()},e.prototype.test_active_click=function(b){return a(b.target).parents("#"+this.container_id).length?this.active_field=!0:this.close_field()},e.prototype.results_build=function(){var a,b,c,e,f;this.parsing=!0,this.results_data=d.SelectParser.select_to_array(this.form_field),this.is_multiple&&this.choices>0?(this.search_choices.find("li.search-choice").remove(),this.choices=0):this.is_multiple||(this.selected_item.find("span").text(this.default_text),this.form_field.options.length<=this.disable_search_threshold?this.container.addClass("chzn-container-single-nosearch"):this.container.removeClass("chzn-container-single-nosearch")),a="",f=this.results_data;for(c=0,e=f.length;c<e;c++)b=f[c],b.group?a+=this.result_add_group(b):b.empty||(a+=this.result_add_option(b),b.selected&&this.is_multiple?this.choice_build(b):b.selected&&!this.is_multiple&&(this.selected_item.find("span").text(b.text),this.allow_single_deselect&&this.single_deselect_control_build()));return this.search_field_disabled(),this.show_search_field_default(),this.search_field_scale(),this.search_results.html(a),this.parsing=!1},e.prototype.result_add_group=function(b){return b.disabled?"":(b.dom_id=this.container_id+"_g_"+b.array_index,'<li id="'+b.dom_id+'" class="group-result">'+a("<div />").text(b.label).html()+"</li>")},e.prototype.result_do_highlight=function(a){var b,c,d,e,f;if(a.length){this.result_clear_highlight(),this.result_highlight=a,this.result_highlight.addClass("highlighted"),d=parseInt(this.search_results.css("maxHeight"),10),f=this.search_results.scrollTop(),e=d+f,c=this.result_highlight.position().top+this.search_results.scrollTop(),b=c+this.result_highlight.outerHeight();if(b>=e)return this.search_results.scrollTop(b-d>0?b-d:0);if(c<f)return this.search_results.scrollTop(c)}},e.prototype.result_clear_highlight=function(){return this.result_highlight&&this.result_highlight.removeClass("highlighted"),this.result_highlight=null},e.prototype.results_show=function(){var a;return this.is_multiple||(this.selected_item.addClass("chzn-single-with-drop"),this.result_single_selected&&this.result_do_highlight(this.result_single_selected)),a=this.is_multiple?this.container.height():this.container.height()-1,this.dropdown.css({top:a+"px",left:0}),this.results_showing=!0,this.search_field.focus(),this.search_field.val(this.search_field.val()),this.winnow_results()},e.prototype.results_hide=function(){return this.is_multiple||this.selected_item.removeClass("chzn-single-with-drop"),this.result_clear_highlight(),this.dropdown.css({left:"-9000px"}),this.results_showing=!1},e.prototype.set_tab_index=function(a){var b;if(this.form_field_jq.attr("tabindex"))return b=this.form_field_jq.attr("tabindex"),this.form_field_jq.attr("tabindex",-1),this.is_multiple?this.search_field.attr("tabindex",b):(this.selected_item.attr("tabindex",b),this.search_field.attr("tabindex",-1))},e.prototype.show_search_field_default=function(){return this.is_multiple&&this.choices<1&&!this.active_field?(this.search_field.val(this.default_text),this.search_field.addClass("default")):(this.search_field.val(""),this.search_field.removeClass("default"))},e.prototype.search_results_mouseup=function(b){var c;c=a(b.target).hasClass("active-result")?a(b.target):a(b.target).parents(".active-result").first();if(c.length)return this.result_highlight=c,this.result_select(b)},e.prototype.search_results_mouseover=function(b){var c;c=a(b.target).hasClass("active-result")?a(b.target):a(b.target).parents(".active-result").first();if(c)return this.result_do_highlight(c)},e.prototype.search_results_mouseout=function(b){if(a(b.target).hasClass("active-result"))return this.result_clear_highlight()},e.prototype.choices_click=function(b){b.preventDefault();if(this.active_field&&!a(b.target).hasClass("search-choice")&&!this.results_showing)return this.results_show()},e.prototype.choice_build=function(b){var c,d,e=this;return c=this.container_id+"_c_"+b.array_index,this.choices+=1,this.search_container.before('<li class="search-choice" id="'+c+'"><span>'+b.html+'</span><a href="javascript:void(0)" class="search-choice-close" rel="'+b.array_index+'"></a></li>'),d=a("#"+c).find("a").first(),d.click(function(a){return e.choice_destroy_link_click(a)})},e.prototype.choice_destroy_link_click=function(b){return b.preventDefault(),this.is_disabled?b.stopPropagation:(this.pending_destroy_click=!0,this.choice_destroy(a(b.target)))},e.prototype.choice_destroy=function(a){return this.choices-=1,this.show_search_field_default(),this.is_multiple&&this.choices>0&&this.search_field.val().length<1&&this.results_hide(),this.result_deselect(a.attr("rel")),a.parents("li").first().remove()},e.prototype.results_reset=function(b){this.form_field.options[0].selected=!0,this.selected_item.find("span").text(this.default_text),this.show_search_field_default(),a(b.target).remove(),this.form_field_jq.trigger("change");if(this.active_field)return this.results_hide()},e.prototype.result_select=function(a){var b,c,d,e;if(this.result_highlight)return b=this.result_highlight,c=b.attr("id"),this.result_clear_highlight(),this.is_multiple?this.result_deactivate(b):(this.search_results.find(".result-selected").removeClass("result-selected"),this.result_single_selected=b),b.addClass("result-selected"),e=c.substr(c.lastIndexOf("_")+1),d=this.results_data[e],d.selected=!0,this.form_field.options[d.options_index].selected=!0,this.is_multiple?this.choice_build(d):(this.selected_item.find("span").first().text(d.text),this.allow_single_deselect&&this.single_deselect_control_build()),(!a.metaKey||!this.is_multiple)&&this.results_hide(),this.search_field.val(""),this.form_field_jq.trigger("change"),this.search_field_scale()},e.prototype.result_activate=function(a){return a.addClass("active-result")},e.prototype.result_deactivate=function(a){return a.removeClass("active-result")},e.prototype.result_deselect=function(b){var c,d;return d=this.results_data[b],d.selected=!1,this.form_field.options[d.options_index].selected=!1,c=a("#"+this.container_id+"_o_"+b),c.removeClass("result-selected").addClass("active-result").show(),this.result_clear_highlight(),this.winnow_results(),this.form_field_jq.trigger("change"),this.search_field_scale()},e.prototype.single_deselect_control_build=function(){if(this.allow_single_deselect&&this.selected_item.find("abbr").length<1)return this.selected_item.find("span").first().after('<abbr class="search-choice-close"></abbr>')},e.prototype.winnow_results=function(){var b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r;this.no_results_clear(),i=0,j=this.search_field.val()===this.default_text?"":a("<div/>").text(a.trim(this.search_field.val())).html(),f=new RegExp("^"+j.replace(/[-[\]{}()*+?.,\\^$|#\s]/g,"\\$&"),"i"),m=new RegExp(j.replace(/[-[\]{}()*+?.,\\^$|#\s]/g,"\\$&"),"i"),r=this.results_data;for(n=0,p=r.length;n<p;n++){c=r[n];if(!c.disabled&&!c.empty)if(c.group)a("#"+c.dom_id).css("display","none");else if(!this.is_multiple||!c.selected){b=!1,h=c.dom_id,g=a("#"+h);if(f.test(c.html))b=!0,i+=1;else if(c.html.indexOf(" ")>=0||c.html.indexOf("[")===0){e=c.html.replace(/\[|\]/g,"").split(" ");if(e.length)for(o=0,q=e.length;o<q;o++)d=e[o],f.test(d)&&(b=!0,i+=1)}b?(j.length?(k=c.html.search(m),l=c.html.substr(0,k+j.length)+"</em>"+c.html.substr(k+j.length),l=l.substr(0,k)+"<em>"+l.substr(k)):l=c.html,g.html(l),this.result_activate(g),c.group_array_index!=null&&a("#"+this.results_data[c.group_array_index].dom_id).css("display","list-item")):(this.result_highlight&&h===this.result_highlight.attr("id")&&this.result_clear_highlight(),this.result_deactivate(g))}}return i<1&&j.length?this.no_results(j):this.winnow_results_set_highlight()},e.prototype.winnow_results_clear=function(){var b,c,d,e,f;this.search_field.val(""),c=this.search_results.find("li"),f=[];for(d=0,e=c.length;d<e;d++)b=c[d],b=a(b),b.hasClass("group-result")?f.push(b.css("display","auto")):!this.is_multiple||!b.hasClass("result-selected")?f.push(this.result_activate(b)):f.push(void 0);return f},e.prototype.winnow_results_set_highlight=function(){var a,b;if(!this.result_highlight){b=this.is_multiple?[]:this.search_results.find(".result-selected.active-result"),a=b.length?b.first():this.search_results.find(".active-result").first();if(a!=null)return this.result_do_highlight(a)}},e.prototype.no_results=function(b){var c;return c=a('<li class="no-results">'+this.results_none_found+' "<span></span>"</li>'),c.find("span").first().html(b),this.search_results.append(c)},e.prototype.no_results_clear=function(){return this.search_results.find(".no-results").remove()},e.prototype.keydown_arrow=function(){var b,c;this.result_highlight?this.results_showing&&(c=this.result_highlight.nextAll("li.active-result").first(),c&&this.result_do_highlight(c)):(b=this.search_results.find("li.active-result").first(),b&&this.result_do_highlight(a(b)));if(!this.results_showing)return this.results_show()},e.prototype.keyup_arrow=function(){var a;if(!this.results_showing&&!this.is_multiple)return this.results_show();if(this.result_highlight)return a=this.result_highlight.prevAll("li.active-result"),a.length?this.result_do_highlight(a.first()):(this.choices>0&&this.results_hide(),this.result_clear_highlight())},e.prototype.keydown_backstroke=function(){return this.pending_backstroke?(this.choice_destroy(this.pending_backstroke.find("a").first()),this.clear_backstroke()):(this.pending_backstroke=this.search_container.siblings("li.search-choice").last(),this.pending_backstroke.addClass("search-choice-focus"))},e.prototype.clear_backstroke=function(){return this.pending_backstroke&&this.pending_backstroke.removeClass("search-choice-focus"),this.pending_backstroke=null},e.prototype.keydown_checker=function(a){var b,c;b=(c=a.which)!=null?c:a.keyCode,this.search_field_scale(),b!==8&&this.pending_backstroke&&this.clear_backstroke();switch(b){case 8:this.backstroke_length=this.search_field.val().length;break;case 9:this.results_showing&&!this.is_multiple&&this.result_select(a),this.mouse_on_container=!1;break;case 13:a.preventDefault();break;case 38:a.preventDefault(),this.keyup_arrow();break;case 40:this.keydown_arrow()}},e.prototype.search_field_scale=function(){var b,c,d,e,f,g,h,i,j;if(this.is_multiple){d=0,h=0,f="position:absolute; left: -1000px; top: -1000px; display:none;",g=["font-size","font-style","font-weight","font-family","line-height","text-transform","letter-spacing"];for(i=0,j=g.length;i<j;i++)e=g[i],f+=e+":"+this.search_field.css(e)+";";return c=a("<div />",{style:f}),c.text(this.search_field.val()),a("body").append(c),h=c.width()+25,c.remove(),h>this.f_width-10&&(h=this.f_width-10),this.search_field.css({width:h+"px"}),b=this.container.height(),this.dropdown.css({top:b+"px"})}},e.prototype.generate_random_id=function(){var b;b="sel"+this.generate_random_char()+this.generate_random_char()+this.generate_random_char();while(a("#"+b).length>0)b+=this.generate_random_char();return b},e}(AbstractChosen),c=function(a){var b;return b=a.outerWidth()-a.width()},d.get_side_border_padding=c}.call(this)

jQuery.noConflict();

var wpbloxxAdmin = {
    init : function() {
        wpbloxxAdmin.loadTemplates('#block_general\\[type\\]');
        wpbloxxAdmin.loadMasks('#styles_combo');
        wpbloxxAdmin.exportStyle();
        wpbloxxAdmin.menuAdd();
        wpbloxxAdmin.menuEdit();
        wpbloxxAdmin.menuDelete();
        wpbloxxAdmin.colorPicker();
    },
    
    loadTemplates : function(elem) {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action : 'get_templates_cb', // required
                postid : jQuery('input[name="postid"]').val(),
            },                    
            success: function(data){
                jQuery(elem).html(data);
                if ( data == 0 ) {
                    jQuery("#templates").html('<a href="#" id="setup_templates" class="btn btn-primary">Import <i class="icon-th icon-white"></i></a><span id="message_setup_templates"></span>');        
                } else {
                    jQuery("#templates").html(data);                         
                }                   
            }
        });    
    },
    
    loadMasks : function(elem) {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action : 'load_masks_cb',
                postid : jQuery('input[name="postid"]').val(), 
            },                    
            success: function(data){
                jQuery(elem).html(data);
            }
        });    
    },
    saveMask : function(elem) {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action : 'save_mask_cb',
                postid : jQuery('input[name="postid"]').val(),
                stylename : elem.val() 
            },                    
            success: function(){
                wpbloxxAdmin.loadMasks('#styles_combo');
            }
        });    
    },
    applyMask : function(mask) {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action : 'apply_mask_cb',
                postid : jQuery('input[name="postid"]').val(),
                stylename : mask 
            }
        });
    },
        
    importStyle : function() {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action : 'import_style_cb',
                text: jQuery('#style_import').val(),
                postid : jQuery('#style_import_button').data('id'),
            },
        });    
    },    
    exportStyle : function() {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action : 'export_style_cb',
                postid : jQuery('#style_import_button').data('id'),
            },
            success: function(data){
                jQuery('#style_export').val(data);
            }
        });    
    },
    
    clearSliderHeight : function() {
        jQuery('#block_content').css({height: ''});
    },    
    refreshSliderHeight : function() {
        jQuery('#block_content').css({
              height: function(index, value) {
                return parseFloat(value);
              }
        });
    },        
    refreshMenuKeys : function (_this) {
        var _this = _this,
            addKeys = new Array;
        
        sliderIDs = _this.find('li')
        sliderIDs.each(function(i) {
            var thisID = jQuery(this).attr('id').match(/\d+/g);
            addKeys.push(thisID);
        });
        addKeys.push('#');
        _this.parent().find('.menu-keys').val(addKeys.toString());
    },    
    menuAdd : function() {
        jQuery('.cow_add_menu').live('click', function(e){
            
            wpbloxxAdmin.clearSliderHeight();

            var _this = jQuery(this).parent().parent(),
                 append = _this.find('.menu-to-edit'),
                 menuItem = _this.find('.sample-to-edit li'),
                 menuEdit = append.find('li'),
                 count = menuEdit.length,
                 allIds = new Array;
            
            menuEdit.each( function() {
                if(jQuery(this).attr('id')){
                    menuEditId = jQuery(this).attr('id').match(/\d+/g);
                    if(menuEditId){
                        allIds.push(parseInt(menuEditId));
                    }
                }
            });
            
            var newID = ( jQuery(append).css('display') == 'none' )? count : count + 1,
                template = menuItem;
            
            if(newID==0) newID = 1; 
            
            while (jQuery.inArray(newID, allIds) != -1 ) {
                newID++;
            }
            
            var newClone = template.clone()

                .attr('id',template.attr('id').replace('#',newID))
                .find('*').each( function() {

                    if( jQuery(this).hasClass('item-title') ) {
                        var newTitle = jQuery(this).text().replace(/\d+/g,newID);
                        jQuery(this).text(newTitle);
                    }

                    var attrId = jQuery(this).attr('id');
                    if (attrId) jQuery(this).attr('id', attrId.replace('#',newID));

                    var attrHref = jQuery(this).attr('href');
                    if (attrHref) jQuery(this).attr('href', attrHref.replace('#',newID));

                    var attrFor = jQuery(this).attr('for');
                    if (attrFor) jQuery(this).attr('for', attrFor.replace('#',newID));

                    var attrName = jQuery(this).attr('name');
                    if (attrName) jQuery(this).attr('name', attrName.replace('#',newID));

                }).end();

            var newAppend = jQuery(append).append(function(index, html) {
                if( jQuery(this).css('display') == 'none' ){
                    jQuery(_this).find('.menu_clear').css('display','block');
                    jQuery(this).empty();
                    jQuery(this).css('display','block');
                }
                
                return newClone;
            });

            if(newAppend) {
                wpbloxxAdmin.clearSliderHeight();
                
                var _regex = new RegExp( "(.*)menu-item-settings-" + newID, "i"),
                    _match,
                    _item;
                
                append.find('li').children().filter(function() {
                    _find = this.id.match(_regex);
                    if(_find){
                        _match = _find;
                    }
                });
                
                if(_match){
                    _item = jQuery('#'+_match[0]).parent();
                    
                    jQuery('#'+_match[0]).slideToggle('fast', function() {
                        wpbloxxAdmin.refreshSliderHeight();
                    });

                    if(_item.hasClass('menu-item-edit-inactive')){
                        _item.removeClass('menu-item-edit-inactive');
                        _item.addClass('menu-item-edit-active');
                    }else{
                        _item.removeClass('menu-item-edit-active');
                        _item.addClass('menu-item-edit-inactive');
                    }
                }

                wpbloxxAdmin.refreshMenuKeys(append);            
            }

            e.preventDefault();
        });
    },    
    menuSort : function() {
        jQuery(".menu-to-edit").sortable({
            handle: '.menu-item-handle',
            placeholder: 'sortable-placeholder',

            start: function() {
                jQuery('#wpwrap').css('overflow','hidden');
            },
            update: function(event, ui) {
                _this = jQuery(this);
                wpbloxxAdmin.refreshMenuKeys(_this);
            }
            
        });
    },   
    menuEdit : function() {
        jQuery('.item-edit').live( 'click', function(e) {
            
            jQuery.fx.off = false;

            wpbloxxAdmin.clearSliderHeight();

            var settings = jQuery(this).attr('href');

            var item = jQuery('#'+settings).parent();

            jQuery('#'+settings).slideToggle('fast', function() {
                wpbloxxAdmin.refreshSliderHeight();
            });

            if(item.hasClass('menu-item-edit-inactive')){
                item.removeClass('menu-item-edit-inactive');
                item.addClass('menu-item-edit-active');
            }else{
                item.removeClass('menu-item-edit-active');
                item.addClass('menu-item-edit-inactive');
            }
            
            e.preventDefault();
        });
    },     
    menuDelete : function() {
        jQuery('.links_deletion').live( 'click', function(e) {
            
            if (confirm('Are you sure?')){
                var _this = jQuery(this).parent().parent().parent().parent();
                
                var sliderRM = jQuery(this).attr('id').replace('delete-','');
                var el = _this.find('#'+sliderRM);

                el.addClass('deleting').animate({
                    opacity : 0,
                    height: 0
                }, 350, function() {
                    el.remove();
                    wpbloxxAdmin.refreshMenuKeys(_this);
                    wpbloxxAdmin.clearSliderHeight();
                    if(jQuery(_this).is(':empty')){
                        jQuery(_this).parent().find('.menu_clear').css('display','none');
                        jQuery(_this).css('display','none');
                    }
                });

                e.preventDefault();    
            }           
        });
    },    
    menuRefresh : function() {
        jQuery('.links_option_set').find('li').each(function(i) {
                var newID = i + 1;
                jQuery(this).find('.item-title').text('Links ' + newID);
        });
    },
    
    colorPicker : function() {
        jQuery('[id*=_picker]').each(function(i){
            var _this = jQuery(this),
                _color = _this.next('input');
            
            jQuery(_this).children('div').css('backgroundColor', '#' + _color.val());
            jQuery(_color).keyup(function(event) { 
                var s = jQuery(this).val();
                jQuery(this).val(s);
                //var sub1 = s.substr(0, 1),
//                    sub2 = s.substr(0, 2);
//                if( (sub1 != '#' && sub1 != 'i' && sub1 != 't') && (sub2) ){
//                    jQuery(this).val('#'+sub1);
//                }
                jQuery(_this).children('div').css('backgroundColor', '#' + jQuery(this).val());
            });
            
            jQuery(_this).ColorPicker({
                color: _color.val(),
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    jQuery(_this).children('div').css('backgroundColor', '#' + hex);
                    jQuery(_this).next('input').attr('value', hex);
                }
            });
        }); //end each
    }                
} // End wpbloxxAdmin


jQuery(document).ready(function($){

    wpbloxxAdmin.init();
    
    $( "#cow-tabs" ).tabs();

    // Toggle multiselect
    $("select.chosen").chosen();
          
    // Save as mask
    $('[data-action="save_mask"]').on('click', function(){
        var elem = $('input[name="save_style_text"]');
        if (elem.val() != "") {
            wpbloxxAdmin.saveMask(elem);    
        }                   
    });
    // Apply mask
    $('[data-action="apply_mask"]').on('click', function(){
        var mask = $("select#styles_combo").val();
        wpbloxxAdmin.applyMask(mask)                      
    });
    
    // Handle range input
    $(".range-input-wrap :range").rangeinput();

    $('#style_import_button').on('click', function(){
        alert('Importing...');
        wpbloxxAdmin.importStyle();        
    });     
    $("#style_export,#style_import").focus(function() {
        $this = $(this);
        $this.select();
        // Work around Chrome's little problem
        $this.mouseup(function() {
            $this.unbind("mouseup");
            return false;
        });
    }); 
    
    // Upgrade from free version to pro
    $('[data-action="upgrade"]').click(function(){
        $('[data-message="upgrade"]').html(' One moment please. Currently upgrading... ' + '<img style="vertical-align: middle;" src="' + plugin_uri + '/assets/images/ajax-loader.gif' + '" alt="" />');
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action : 'upgrade_cb', 
            },                    
            success: function(data){
                $('[data-message="upgrade"]').html(' ' + data).addClass('alert alert-success').fadeOut(7000);
            }
        });             
    }); 
    
    $('[data-action="update_google_fonts"]').click(function(){
        $('[data-message="update_google_fonts"]').html(' One moment please. Currently updating... ' + '<img style="vertical-align: middle;" src="' + plugin_uri + '/assets/images/ajax-loader.gif' + '" alt="" />');
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action : 'update_google_fonts_cb', 
            },                    
            success: function(result){
                $('[data-message="update_google_fonts"]').html(' ' + result).addClass('alert alert-success').fadeOut(7000);
            }
        });             
    }); 
    
    // Shows an example of the chosen font family
    var changeFont = function( elem )
    {
        var font = elem.val();
        
        WebFontConfig = {
            google: { families: [ font ] }
        };     
        
        (function() {
            var wf = document.createElement('script');
            wf.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
            wf.type = 'text/javascript';
            wf.async = 'true';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(wf, s);
        })();
        
        elem.parent().next().css({
            'font-family' : "'" + font + "'",
            'font-size' : '20px'    
        });            
    }
    
    // Show Google Font preview
    $('select[id*="[font_family]"]').change(function(){
        changeFont($(this)); 
    }); 
});

 