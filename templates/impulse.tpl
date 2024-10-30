{% spaceless %}

{% include '_partials/_image.tpl' %}
<div class="box_content">
	<div class="cow_one_half">
		<div style="border-right: 1px dotted #CCC;min-height:111px;">
        	<span class="date" style="color:#C1C1C1;font-size:12px;margin-bottom:8px;">{{ date }}</span>
			{% if box.header.show == 1 %}
				{% include '_partials/_header.tpl' %}
			{% endif %}	
    	</div>
	</div>
	<div class="cow_one_half cow_last">
		{% include '_partials/_content.tpl' %}
	</div>

	{% if box.footer.show == 1 %}
		{% include '_partials/_footer.tpl' %}
	{% endif %}		
</div>

{% endspaceless %}