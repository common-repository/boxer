{% spaceless %}
{% if box.header.show == 1 %}
	{% include '_partials/_header.tpl' %}
{% endif %}

{% include '_partials/_content.tpl' %}

{% include '_partials/_image.tpl' %}

{% if box.footer.show == 1 %}
	{% include '_partials/_footer.tpl' %}
{% endif %}
{% endspaceless %}