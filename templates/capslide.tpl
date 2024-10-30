{% spaceless %}
<div id="capslide_{{ box.ID }}" class="ic_container">
    {% include '_partials/_image.tpl' %}
    <div class="overlay"></div>
    <div class="ic_caption">
        {% include '_partials/_header.tpl' %}
        {% include '_partials/_content.tpl' %}
    </div>
</div>
<script>
    (function($) {
        $("#capslide_{{ box.ID }}").capslide({
            caption_color : 'black',
            caption_bgcolor : 'white',
            overlay_bgcolor : '{{ box.bgcolor }}',
            border : 'none',
            showcaption : true
        });
    })(jQuery);
</script>                 
{% endspaceless %}