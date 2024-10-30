{# Nivo Slider #}
{% if boxes %}
    {% spaceless %}
    <style>      
    #slider-{{ id }} {
        margin: 30px auto 60px;
    }
    #slider-{{ id }} .nivoSlider {
        width:{{ width }}px;
        height:{{ height }}px;
    }
    #slider-{{ id }} .nivo-caption {
        font-size:{{ font_size }}px;
    }
    </style>
    <div id="slider-{{ id }}" class="slider-wrapper theme-{{ theme }}" style="width:{{ width }}px">
        <div class="nivoSlider">
            {% for box in boxes %}    
                {{ box.image.src|raw }}        
            {% endfor %}
        </div>
    </div>
    <script>
    jQuery("#slider-{{ id }} .nivoSlider").nivoSlider({
        pauseTime : {{ duration }} 
    });
    </script>
    <div class="clearboth"></div>
    {% endspaceless %}
{% endif %}