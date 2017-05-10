<div class="wrap">
	<h1>Bibliodata: Editing <?php echo $work->getId() ?></h1>
	<form action="tools.php">
		<input type="hidden" name="action" value="edit" />

		<h2>Work</h2>
		<table>
			<tr>
				<th><label for="title">Title:</label></th>
				<td><input type="text" name="title" value="<?php echo esc_attr($work->getTitle()) ?>" size="80" /></td>
			</tr>
			<tr>
				<th><label for="title">Publication date:</label></th>
				<td><input type="text" name="" value="<?php echo esc_attr($work->getPublicationDate()) ?>" size="80" /></td>
			</tr>
		</table>

		<h2>Editions</h2>
		<ol>
		<?php foreach ($work->getEditions() as $edition): ?>
			<li></li>
		<?php endforeach ?>
		</ol>
	</form>
</div>
