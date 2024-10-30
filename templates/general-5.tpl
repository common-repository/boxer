{% spaceless %}
<div class="cow_one_fourth">
	{% include '_partials/_image.tpl' %}	
</div>
<div class="cow_three_fourth cow_last">
	{% if box.header.show == 1 %}
		{% include '_partials/_header.tpl' %}
	{% endif %}	
	
	{% include '_partials/_content.tpl' %}
	
	{% if box.footer.show == 1 %}
		{% include '_partials/_footer.tpl' %}
	{% endif %}
</div>
{% endspaceless %}