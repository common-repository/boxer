{# Roundabout #}
{% if boxes %}
    {% spaceless %}
    <style> 
        #roundabout-{{ id }} ul {
            list-style: none;
            padding: 0;
            margin: 0 auto;
            width: {{ width }}px;    
            height: {{ height }}px;     
        }
        #roundabout-{{ id }} .roundabout-moveable-item {
            height: 90%; 
            width: 90%; 
        }    
    </style>

    <div id="roundabout-{{ id }}">
        <ul>
            {% for box in boxes %}
                <li>
                {{ box.image.src|raw }}
                </li>                        
            {% endfor %}
        </ul>        
    </div>     
    <div class="clearboth"></div>
    
    <script>
        jQuery('#roundabout-{{ id }} ul').roundabout({                   
            autoplay : {{ autoplay }},
            autoplayDuration : {{ duration }},
            maxOpacity : {{ opacity }}
        });
    </script>
    {% endspaceless %}
{% endif %}