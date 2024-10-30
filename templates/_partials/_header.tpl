{% autoescape false %}
<div class="block_heading {{ box.header.class }}" style="height:{{ box.header.height }}px;text-align:{{ box.header.align }};font-family:'{{ box.header.font_family }}';font-size:{{ box.header.font_size }}px;line-height:{{ box.header.line_height }}px;color:{{  box.header.color }};background-color:{{ box.header.bgcolor }};">
{{ box.header.text|trim }}
</div>
{% endautoescape %}
