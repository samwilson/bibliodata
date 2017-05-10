<div class="wrap">
	<h1>Bibliodata</h1>

	<p>
		You can request access and manage existing authorisations on the
		<a href="https://meta.wikimedia.org/wiki/Special:OAuthConsumerRegistration">Wikimedia Meta wiki</a>
	</p>

	<form action="options-general.php" method="post">
		<input type="hidden" name="page" value="bibliodata-options" />
		<input type="hidden" name="action" value="save" />
		<?php wp_nonce_field('bibliodata-options-save') ?>

		<?php if (!current_user_can('manage_options')): ?>
			<p>Please ask your admin to do a thing.</p>

		<?php else: ?>
		<table class="form-table">
		<tr>
			<th><label for="api_key">Wikidata API key:</label></th>
			<td><input id="api_key" type="text" name="api_key" value="<?php echo esc_attr($apiKey) ?>" size="60" /></td>
		</tr>
		<tr>
			<th><label for="api_secret">Wikidata API secret:</label></th>
			<td><input id="api_secret" type="text" name="api_secret" value="<?php echo esc_attr($apiSecret) ?>" size="60" /></td>
		</tr>
		</table>
		<?php endif ?>

		<p><input type="submit" value="Save" /></p>
	</form>

	<?php if ($apiKey && $apiSecret): ?>
		<form action="options-general.php" method="post">
			<input type="hidden" name="page" value="bibliodata-options" />

			<?php if ($oauthIdentity): ?>
			<input type="hidden" name="action" value="logout" />
			<?php wp_nonce_field('bibliodata-options-logout') ?>
			<p>
				You are logged in as <a href="https://www.wikidata.org/wiki/User:<?php echo $oauthIdentity->username ?>">
					<?php echo $oauthIdentity->username ?>
				</a>
				<input type="submit" value="Log out of Wikidata" />
			</p>

			<?php else: ?>
			<input type="hidden" name="action" value="oauth" />
			<?php wp_nonce_field('bibliodata-options-oauth') ?>
			<p><input type="submit" value="Log in to Wikidata" /></p>

			<?php endif ?>
		</form>
	<?php endif ?>

</div>
