<?php
/**
 * Post type defaults template.
 *
 * @var array $post_types
 * @var array $post_type_templates
 * @var array $post_type_descriptions
 * @var array $post_type_keywords
 *
 * @package WPSEOPilot
 */

?>
<div class="wrap wpseopilot-settings">
	<h1><?php esc_html_e( 'WP SEO Pilot — Post Type Defaults', 'wp-seo-pilot' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Define fallback SEO templates for each content type. These values are used whenever an editor leaves a field blank.', 'wp-seo-pilot' ); ?>
	</p>

	<form action="options.php" method="post" class="wpseopilot-settings__form">
		<?php settings_fields( 'wpseopilot' ); ?>

		<div class="wpseopilot-type-grid">
			<?php foreach ( $post_types as $slug => $object ) : ?>
				<?php
				$label = $object->labels->singular_name ?: $object->label ?: ucfirst( $slug );
				?>
				<section class="wpseopilot-card">
					<h2>
						<?php echo esc_html( $label ); ?>
						<span class="wpseopilot-type-slug"><?php echo esc_html( $slug ); ?></span>
					</h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="wpseopilot_template_<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Title template', 'wp-seo-pilot' ); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text" id="wpseopilot_template_<?php echo esc_attr( $slug ); ?>" name="wpseopilot_post_type_title_templates[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $post_type_templates[ $slug ] ?? '' ); ?>" />
								<p class="description"><?php esc_html_e( 'Available tags: %post_title%, %site_title%, %tagline%, %post_author%', 'wp-seo-pilot' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="wpseopilot_desc_<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Default meta description', 'wp-seo-pilot' ); ?></label>
							</th>
							<td>
								<textarea class="large-text" rows="3" id="wpseopilot_desc_<?php echo esc_attr( $slug ); ?>" name="wpseopilot_post_type_meta_descriptions[<?php echo esc_attr( $slug ); ?>]"><?php echo esc_textarea( $post_type_descriptions[ $slug ] ?? '' ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Used only when a post/page does not have its own description set.', 'wp-seo-pilot' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="wpseopilot_keywords_<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Default keywords', 'wp-seo-pilot' ); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text" id="wpseopilot_keywords_<?php echo esc_attr( $slug ); ?>" name="wpseopilot_post_type_keywords[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $post_type_keywords[ $slug ] ?? '' ); ?>" />
								<p class="description"><?php esc_html_e( 'Optional comma-separated keywords meta tag.', 'wp-seo-pilot' ); ?></p>
							</td>
						</tr>
					</table>
				</section>
			<?php endforeach; ?>
		</div>

		<?php submit_button( __( 'Save post type defaults', 'wp-seo-pilot' ) ); ?>
	</form>
</div>
