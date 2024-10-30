{% spaceless %}

{% if box.header.show == 1 %}
	{% include '_partials/_header.tpl' %}
{% endif %}	

{% include '_partials/_content.tpl' %}

{% endspaceless %}