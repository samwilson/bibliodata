<?php
use Samwilson\Bibliodata\Wikidata\BookItem;
?>

<div class="wrap">
	<h1>Bibliodata: <?php echo ($work) ? 'Editing ' . $work->getId() : 'Create a new work' ?></h1>
	<p><a href="tools.php?page=bibliodata-tools">&larr; Return to search</a></p>
	<form action="tools.php" method="post">
		<input type="hidden" name="page" value="bibliodata-tools" />
		<input type="hidden" name="action" value="save" />
		<?php wp_nonce_field('bibliodata-tools-save') ?>
		<?php if ($work): ?>
			<input type="hidden" name="item" value="<?php echo $work->getId() ?>" />
		<?php endif ?>

		<h2>Work</h2>
		<table>
			<tr>
				<th><label for="title">Title:</label></th>
				<td><input type="text" name="title" value="<?php echo $work ? esc_attr($work->getTitle()) : '' ?>" size="80" /></td>
			</tr>
			<tr>
				<th><label for="title">Subtitle:</label></th>
				<td><input type="text" name="subtitle" value="<?php echo $work ? esc_attr($work->getSubtitle()) : '' ?>" size="80" /></td>
			</tr>
		</table>

		<div class="editions">
			<h2>Editions</h2>
			<ol>
			<?php if ($work instanceof BookItem): ?>
				<?php foreach ($work->getEditions() as $edition): ?>
					<li>
						<?php echo $edition->getTitle() ?>
					</li>
				<?php endforeach ?>
			<?php endif ?>
				<li class="new">
					New:
				</li>
			</ol>
		</div>
		<p>
			<input type="submit" value="Save" name="save" />
		</p>
	</form>
</div>
