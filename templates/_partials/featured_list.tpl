{# Featured List #}
{% if boxes %}
<style>
div.feature_list {
    width: {{ width + 287 }}px;
    height: {{ count * 80 }}px;
    }        
ul.output {
    width: {{ width }}px;
    height: {{ count * 80 }}px;
    }
    ul.output li {
        width: {{ width }}px;
        height: {{ count * 80 }}px;
        }
</style>

<div class="feature_list">
    <ul class="wpbp_tabs">
        {% for box in boxes %}
        <li>
            <a href="javascript:void;">
                <h3>{{ box.header.text|title }}</h3>
                <span>{{ box.content.shorttext }}</span>
            </a>
        </li>
        {% endfor %}
    </ul>
    
    <ul class="output">
        {% for box in boxes %}
        <li>
            {% include '_partials/_image.tpl' %}
            <a href="{{ box.link }}">See Details</a>
        </li>
        {% endfor %}
    </ul>
</div>
<div class="clearboth"></div>
{% endif %}