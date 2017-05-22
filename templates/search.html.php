<div class="wrap bibliodata">
	<h1>Bibliodata</h1>
	<p><a href="tools.php?page=bibliodata-tools&action=edit">Add a work</a></p>

	<form action="tools.php" method="get">
		<input type="hidden" name="page" value="bibliodata-tools" />
		<input type="search" name="term" placeholder="Search by label or Q-number" size="40" value="<?php echo esc_attr($term) ?>" />
		<input type="submit" value="Search" />
	</form>

	<?php if ($results): ?>
	<table class='wp-list-table widefat table'>
		<thead>
		<tr>
			<th></th>
			<th>Wikidata ID</th>
			<th>Title</th>
			<th>Subtitle</th>
			<th>Author(s)</th>
			<th>Editions</th>
		</tr>
		</thead>
		<?php foreach($results as $result): ?>
			<tr>
				<td>
					<a href="tools.php?page=bibliodata-tools&action=edit&item=<?php echo $result->getId() ?>">Edit</a>
				</td>
				<td><a href="<?php echo $result->getWikidataUrl() ?>"><?php echo $result->getId() ?></a></td>
				<td><?php echo $result->getTitle() ?></td>
				<td><?php echo $result->getSubtitle() ?></td>
				<td>
					<?php foreach ($result->getAuthors() as $author): ?>
						<?php echo $author->getTitle() ?>
					<?php endforeach ?>
				</td>
				<td><?php echo count($result->getEditions()) ?> editions</td>
				<td>
					<?php foreach ($result->getSubjects() as $subject): ?>
						<?php echo $subject->getTitle() ?>
					<?php endforeach ?>
				</td>
			</tr>
		<?php endforeach ?>
	</table>
	<?php endif ?>

</div>
