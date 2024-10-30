{% spaceless %}
<div class="cow_three_fourth">
	{% if box.header.show == 1 %}
		{% include '_partials/_header.tpl' %}
	{% endif %}	
	
	{% include '_partials/_content.tpl' %}
	
	{% if box.footer.show == 1 %}
		{% include '_partials/_footer.tpl' %}
	{% endif %}
</div>
<div class="cow_one_fourth cow_last">
	{% include '_partials/_image.tpl' %}	
</div>
{% endspaceless %}