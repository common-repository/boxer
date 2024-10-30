{% spaceless %}

{% if box.header.show == 1 %}
	{% include '_partials/_header.tpl' %}
{% endif %}	

{% include '_partials/_content.tpl' %}

<ul class="box_meta">
    <li>Posted on: <strong>{{ box.date }}</strong></li>
    <li>Author: <strong><a rel="author" href="{{ box.author.homepage }}">{{ box.author.display_name }}</a></strong></li>
</ul>

{% endspaceless %}