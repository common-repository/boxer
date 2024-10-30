{% autoescape false %}
<div class="block_footer">
<dl>
	<dt>Author:</dt>
	<dd>{{ box.author.display_name }}</dd>

	<dt>Date:</dt>
	<dd>{{ box.date|raw|date("d/m/Y") }}</dd>
</dl>
</div>
{% endautoescape %}