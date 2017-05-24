<?php
use Samwilson\Bibliodata\Wikidata\BookItem;
use Samwilson\Bibliodata\Wikidata\EditionItem;

?>

<div class="wrap bibliodata">
	<h1>Bibliodata: <?php echo ($work) ? 'Editing ' . $work->getId() : 'Create a new work' ?></h1>
	<p><a href="tools.php?page=bibliodata-tools">&larr; Return to search</a></p>
	<form action="tools.php" method="post">
		<input type="hidden" name="page" value="bibliodata-tools" />
		<input type="hidden" name="action" value="save" />
		<?php wp_nonce_field('bibliodata-tools-save') ?>
		<?php if ($work): ?>
			<input type="hidden" name="item" value="<?php echo $work->getId() ?>" />
		<?php endif ?>

		<h2>
			Work (<?php echo $work && $work->getInstanceOf() ? $work->getInstanceOf()->getTitle() : '' ?>)
			<?php if ($work): ?>
			<a href="https://www.wikidata.org/wiki/<?php echo $work->getid() ?>" class="wikidata">
				<?php echo $work->getid() ?>
			</a>
			<?php endif ?>
		</h2>
		<table>
			<tr>
				<th><label for="instance_of">Instance of:</label></th>
				<td>
					<select name="instance_of">
						<?php foreach ($bookTypes as $book_type): ?>
							<option value="<?php echo $book_type->getId() ?>"<?php if ($book_type->getId() == $work->getInstanceOf()->getId()) echo ' selected' ?>>
								<?php echo $book_type->getTitle() ?>
							</option>
						<?php endforeach ?>
					</select>
					<?php echo $work->getInstanceOf()->getId() ?>
				</td>
			</tr>
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
				<li class="existing edition">
					<h3>
						<a href="https://www.wikidata.org/wiki/<?php echo $edition->getid() ?>" class="wikidata" title="View on Wikidata">
							<?php echo $edition->getid() ?>
						</a>
					</h3>
					<?php echo $edition->getInstanceOf() ? $edition->getInstanceOf()->getTitle() : '[No instance of]' ?>:
					<?php echo $edition instanceof EditionItem ? $edition->getPublicationYear() : '[No date]' ?>
					<?php echo $edition->getTitle() ?>
				</li>
				<?php endforeach ?>
			<?php endif ?>
				<li class="new edition">
					<h3>Add a new edition</h3>
					<label>Publication date:</label>
					<input type="date" value="" name="editions[new][publication_date]" />
				</li>
			</ol>
		</div>
		<p>
			<input type="submit" value="Save" name="save" />
		</p>
	</form>
</div>
