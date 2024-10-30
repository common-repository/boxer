{% spaceless %}
<div class="block_content {{ box.content.class }} {{ box.content.columns }}" style="font-family:'{{box.content.font_family}}';font-size:{{ box.content.font_size }}px;line-height:{{ box.content.line_height }}px;color:{{ box.content.color }};text-align:{{ box.content.align }};">
{{ box.content.text|raw }} 
</div>
{% endspaceless %}