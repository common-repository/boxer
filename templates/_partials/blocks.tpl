{# Generic stuff #}
{% spaceless %}
<style>
{{ import|raw }}
</style>
{% endspaceless %}


{# Regular content blocks #}
{% if boxes %}
    {% spaceless %}
        <div class="content_block_wrapper">    
        {% for box in boxes %}    
            <div class="content_block {{ box.width }} {{ box.template }}" style="background-color:{{ box.bgcolor }};">
                <div class="block_inner {{ box.class }}" style="height:{{ box.height }};">
                    {% include box.template ~ '.tpl' %}
                </div>
            </div>
            {% if columns %}                     
                {% if loop.index is divisibleby(columns) %}                        
                    <div class="clearboth"></div>                   
                {% endif %}            
                   
            {% endif %}
        {% endfor %}
        </div>
        {% if pagination %}
            {% autoescape false %}
            {{ pagination }}
            {% endautoescape %}
        {% endif %}
        <div class="clearboth"></div> 
    {% endspaceless %}
{% endif %}