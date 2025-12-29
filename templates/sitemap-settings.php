<?php
/**
 * Sitemap Settings Page Template
 *
 * @package WPSEOPilot
 */

defined( 'ABSPATH' ) || exit;

// Render top bar
\WPSEOPilot\Admin_Topbar::render( 'sitemap', '', [
	[
		'type'   => 'button',
		'label'  => __( 'View Sitemap', 'wp-seo-pilot' ),
		'url'    => home_url( '/sitemap_index.xml' ),
		'target' => '_blank',
		'class'  => 'button-secondary',
	],
	[
		'type'   => 'button',
		'label'  => __( 'Open llm.txt', 'wp-seo-pilot' ),
		'url'    => home_url( '/llm.txt' ),
		'target' => '_blank',
		'class'  => 'button-secondary',
	],
] );
?>

<div class="wrap wpseopilot-page wpseopilot-sitemap-page">
	<div class="wpseopilot-tabs" data-component="wpseopilot-tabs">
		<div class="nav-tab-wrapper wpseopilot-tabs__nav" role="tablist" aria-label="<?php esc_attr_e( 'Sitemap sections', 'wp-seo-pilot' ); ?>">
			<button
				type="button"
				class="nav-tab nav-tab-active"
				id="wpseopilot-tab-link-sitemap"
				role="tab"
				aria-selected="true"
				aria-controls="wpseopilot-tab-sitemap"
				data-wpseopilot-tab="wpseopilot-tab-sitemap"
			>
				<?php esc_html_e( 'XML Sitemap', 'wp-seo-pilot' ); ?>
			</button>
			<button
				type="button"
				class="nav-tab"
				id="wpseopilot-tab-link-llm"
				role="tab"
				aria-selected="false"
				aria-controls="wpseopilot-tab-llm"
				data-wpseopilot-tab="wpseopilot-tab-llm"
			>
				<?php esc_html_e( 'LLM.txt', 'wp-seo-pilot' ); ?>
			</button>
		</div>

		<!-- XML Sitemap Tab -->
		<div
			id="wpseopilot-tab-sitemap"
			class="wpseopilot-tab-panel is-active"
			role="tabpanel"
			aria-labelledby="wpseopilot-tab-link-sitemap"
		>
			<form method="post" action="">
				<?php wp_nonce_field( 'wpseopilot_sitemap_settings' ); ?>

				<div class="wpseopilot-settings-grid">
					<!-- Main Settings Column -->
					<div class="wpseopilot-settings-main">

						<!-- XML Sitemap Configuration Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h2><?php esc_html_e( 'XML Sitemap Configuration', 'wp-seo-pilot' ); ?></h2>
								<p><?php esc_html_e( 'Control which content appears in your XML sitemap and how it is organized.', 'wp-seo-pilot' ); ?></p>
							</div>
							<div class="wpseopilot-card-body">

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><?php esc_html_e( 'Automatic Updates', 'wp-seo-pilot' ); ?></label>
									</div>
									<div class="wpseopilot-form-control">
										<select name="wpseopilot_sitemap_schedule_updates" class="wpseopilot-select">
											<?php foreach ( $schedule_options as $value => $label ) : ?>
												<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $schedule_updates, $value ); ?>>
													<?php echo esc_html( $label ); ?>
												</option>
											<?php endforeach; ?>
										</select>
										<span class="wpseopilot-helper-text"><?php esc_html_e( 'Automatically regenerate sitemap on a schedule.', 'wp-seo-pilot' ); ?></span>
									</div>
								</div>

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><?php esc_html_e( 'Max URLs Per Page', 'wp-seo-pilot' ); ?></label>
									</div>
									<div class="wpseopilot-form-control">
										<input type="number"
											   name="wpseopilot_sitemap_max_urls"
											   value="<?php echo esc_attr( $max_urls ); ?>"
											   min="1"
											   max="50000"
											   class="wpseopilot-input small">
										<span class="wpseopilot-helper-text"><?php esc_html_e( 'Maximum number of URLs per sitemap page (recommended: 1000).', 'wp-seo-pilot' ); ?></span>
									</div>
								</div>

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><?php esc_html_e( 'Options', 'wp-seo-pilot' ); ?></label>
									</div>
									<div class="wpseopilot-form-control">
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="enable-index" name="wpseopilot_sitemap_enable_index" value="1" <?php checked( $enable_index, '1' ); ?>>
											<label for="enable-index"><?php esc_html_e( 'Enable sitemap indexes for better organization', 'wp-seo-pilot' ); ?></label>
										</div>
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="dynamic-gen" name="wpseopilot_sitemap_dynamic_generation" value="1" <?php checked( $dynamic_generation, '1' ); ?>>
											<label for="dynamic-gen"><?php esc_html_e( 'Dynamically generate sitemap on-demand', 'wp-seo-pilot' ); ?></label>
										</div>
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="exclude-images" name="wpseopilot_sitemap_exclude_images" value="1" <?php checked( $exclude_images, '1' ); ?>>
											<label for="exclude-images"><?php esc_html_e( 'Exclude images from sitemap entries', 'wp-seo-pilot' ); ?></label>
										</div>
									</div>
								</div>

							</div>
						</div>

						<!-- Content Types Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h2><?php esc_html_e( 'Content Types', 'wp-seo-pilot' ); ?></h2>
								<p><?php esc_html_e( 'Select which post types and taxonomies should be included in your sitemap.', 'wp-seo-pilot' ); ?></p>
							</div>
							<div class="wpseopilot-card-body">

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><strong><?php esc_html_e( 'Post Types', 'wp-seo-pilot' ); ?></strong></label>
									</div>
									<div class="wpseopilot-form-control">
										<div class="wpseopilot-type-grid">
											<?php foreach ( $post_types as $post_type ) : ?>
												<div class="wpseopilot-toggle">
													<input type="checkbox"
														   id="pt-<?php echo esc_attr( $post_type->name ); ?>"
														   name="wpseopilot_sitemap_post_types[]"
														   value="<?php echo esc_attr( $post_type->name ); ?>"
														   <?php checked( in_array( $post_type->name, $selected_post_types, true ) ); ?>>
													<label for="pt-<?php echo esc_attr( $post_type->name ); ?>"><?php echo esc_html( $post_type->label ); ?></label>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><strong><?php esc_html_e( 'Taxonomies', 'wp-seo-pilot' ); ?></strong></label>
									</div>
									<div class="wpseopilot-form-control">
										<div class="wpseopilot-type-grid">
											<?php foreach ( $taxonomies as $taxonomy ) : ?>
												<div class="wpseopilot-toggle">
													<input type="checkbox"
														   id="tax-<?php echo esc_attr( $taxonomy->name ); ?>"
														   name="wpseopilot_sitemap_taxonomies[]"
														   value="<?php echo esc_attr( $taxonomy->name ); ?>"
														   <?php checked( in_array( $taxonomy->name, $selected_taxonomies, true ) ); ?>>
													<label for="tax-<?php echo esc_attr( $taxonomy->name ); ?>"><?php echo esc_html( $taxonomy->label ); ?></label>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><?php esc_html_e( 'Archives', 'wp-seo-pilot' ); ?></label>
									</div>
									<div class="wpseopilot-form-control">
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="include-author" name="wpseopilot_sitemap_include_author_pages" value="1" <?php checked( $include_author, '1' ); ?>>
											<label for="include-author"><?php esc_html_e( 'Include author archive pages', 'wp-seo-pilot' ); ?></label>
										</div>
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="include-date" name="wpseopilot_sitemap_include_date_archives" value="1" <?php checked( $include_date, '1' ); ?>>
											<label for="include-date"><?php esc_html_e( 'Include date archive pages', 'wp-seo-pilot' ); ?></label>
										</div>
									</div>
								</div>

							</div>
						</div>

						<!-- Additional Sitemaps Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h2><?php esc_html_e( 'Additional Sitemaps', 'wp-seo-pilot' ); ?></h2>
								<p><?php esc_html_e( 'Enable specialized sitemaps for RSS feeds and Google News.', 'wp-seo-pilot' ); ?></p>
							</div>
							<div class="wpseopilot-card-body">

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><?php esc_html_e( 'RSS Sitemap', 'wp-seo-pilot' ); ?></label>
									</div>
									<div class="wpseopilot-form-control">
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="enable-rss" name="wpseopilot_sitemap_enable_rss" value="1" <?php checked( $enable_rss, '1' ); ?>>
											<label for="enable-rss"><?php esc_html_e( 'Generate RSS sitemap with latest 50 posts', 'wp-seo-pilot' ); ?></label>
										</div>
									</div>
								</div>

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><?php esc_html_e( 'Google News', 'wp-seo-pilot' ); ?></label>
									</div>
									<div class="wpseopilot-form-control">
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="enable-news" name="wpseopilot_sitemap_enable_google_news" value="1" <?php checked( $enable_google_news, '1' ); ?>>
											<label for="enable-news"><strong><?php esc_html_e( 'Enable Google News sitemap', 'wp-seo-pilot' ); ?></strong></label>
										</div>

										<div style="margin-top: 12px;">
											<input type="text"
												   name="wpseopilot_sitemap_google_news_name"
												   value="<?php echo esc_attr( $google_news_name ); ?>"
												   placeholder="<?php esc_attr_e( 'Publication Name', 'wp-seo-pilot' ); ?>"
												   class="wpseopilot-input">
											<span class="wpseopilot-helper-text"><?php esc_html_e( 'The name of your publication for Google News.', 'wp-seo-pilot' ); ?></span>
										</div>

										<div style="margin-top: 12px;">
											<div class="wpseopilot-checkbox-list">
												<?php foreach ( $post_types as $post_type ) : ?>
													<label>
														<input type="checkbox"
															   name="wpseopilot_sitemap_google_news_post_types[]"
															   value="<?php echo esc_attr( $post_type->name ); ?>"
															   <?php checked( in_array( $post_type->name, $google_news_post_types, true ) ); ?>>
														<?php echo esc_html( $post_type->label ); ?>
													</label>
												<?php endforeach; ?>
											</div>
											<span class="wpseopilot-helper-text"><?php esc_html_e( 'Post types to include in Google News sitemap.', 'wp-seo-pilot' ); ?></span>
										</div>
									</div>
								</div>

							</div>
						</div>

						<!-- Additional Pages Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h2><?php esc_html_e( 'Additional Pages', 'wp-seo-pilot' ); ?></h2>
								<p><?php esc_html_e( 'Add custom URLs to your sitemap that are not managed by WordPress.', 'wp-seo-pilot' ); ?></p>
							</div>
							<div class="wpseopilot-card-body">
								<div class="wpseopilot-additional-pages">
									<div id="additional-pages-container">
										<?php if ( ! empty( $additional_pages ) ) : ?>
											<?php foreach ( $additional_pages as $index => $page ) : ?>
												<div class="additional-page-row">
													<input type="url"
														   name="wpseopilot_sitemap_additional_pages[<?php echo esc_attr( $index ); ?>][url]"
														   value="<?php echo esc_url( $page['url'] ?? '' ); ?>"
														   placeholder="https://example.com/page"
														   class="wpseopilot-input">
													<input type="text"
														   name="wpseopilot_sitemap_additional_pages[<?php echo esc_attr( $index ); ?>][priority]"
														   value="<?php echo esc_attr( $page['priority'] ?? '0.5' ); ?>"
														   placeholder="0.5">
													<button type="button" class="button remove-page"><?php esc_html_e( 'Remove', 'wp-seo-pilot' ); ?></button>
												</div>
											<?php endforeach; ?>
										<?php endif; ?>
									</div>
									<button type="button" class="button" id="add-additional-page"><?php esc_html_e( 'Add Page', 'wp-seo-pilot' ); ?></button>
									<span class="wpseopilot-helper-text" style="display: block; margin-top: 8px;"><?php esc_html_e( 'Add custom URLs with their priority (0.0 to 1.0).', 'wp-seo-pilot' ); ?></span>
								</div>
							</div>
						</div>

						<!-- Save Button -->
						<p class="submit" style="margin-top: 20px;">
							<input type="submit" name="wpseopilot_sitemap_submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-seo-pilot' ); ?>">
							<button type="button" class="button" id="regenerate-sitemap"><?php esc_html_e( 'Regenerate Now', 'wp-seo-pilot' ); ?></button>
						</p>

					</div>

					<!-- Sidebar Column -->
					<div class="wpseopilot-settings-sidebar">

						<!-- Sitemap URLs Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h3><?php esc_html_e( 'Your Sitemaps', 'wp-seo-pilot' ); ?></h3>
								<p><?php esc_html_e( 'Access your generated sitemaps below.', 'wp-seo-pilot' ); ?></p>
							</div>
							<div class="wpseopilot-card-body">
								<div class="wpseopilot-sitemap-urls">
									<div class="sitemap-url-item">
										<strong><?php esc_html_e( 'Main Index:', 'wp-seo-pilot' ); ?></strong>
										<a href="<?php echo esc_url( home_url( '/sitemap_index.xml' ) ); ?>" target="_blank">
											<?php echo esc_html( home_url( '/sitemap_index.xml' ) ); ?>
										</a>
									</div>
									<?php if ( '1' === $enable_rss ) : ?>
									<div class="sitemap-url-item">
										<strong><?php esc_html_e( 'RSS:', 'wp-seo-pilot' ); ?></strong>
										<a href="<?php echo esc_url( home_url( '/sitemap-rss.xml' ) ); ?>" target="_blank">
											<?php echo esc_html( home_url( '/sitemap-rss.xml' ) ); ?>
										</a>
									</div>
									<?php endif; ?>
									<?php if ( '1' === $enable_google_news ) : ?>
									<div class="sitemap-url-item">
										<strong><?php esc_html_e( 'Google News:', 'wp-seo-pilot' ); ?></strong>
										<a href="<?php echo esc_url( home_url( '/sitemap-news.xml' ) ); ?>" target="_blank">
											<?php echo esc_html( home_url( '/sitemap-news.xml' ) ); ?>
										</a>
									</div>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<!-- Info Card -->
						<div class="wpseopilot-info-card">
							<p><strong><?php esc_html_e( 'Tip:', 'wp-seo-pilot' ); ?></strong> <?php esc_html_e( 'Submit your sitemap to Google Search Console and Bing Webmaster Tools for better indexing.', 'wp-seo-pilot' ); ?></p>
						</div>

					</div>
				</div>
			</form>
		</div>

		<!-- LLM.txt Tab -->
		<div
			id="wpseopilot-tab-llm"
			class="wpseopilot-tab-panel"
			role="tabpanel"
			aria-labelledby="wpseopilot-tab-link-llm"
		>
			<form method="post" action="">
				<?php wp_nonce_field( 'wpseopilot_llm_txt_settings' ); ?>

				<div class="wpseopilot-settings-grid">
					<!-- Main Settings Column -->
					<div class="wpseopilot-settings-main">

						<!-- LLM.txt Introduction Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h2><?php esc_html_e( 'LLM.txt', 'wp-seo-pilot' ); ?></h2>
								<p><?php esc_html_e( 'The llm.txt is a specialized file designed to help AI engines (such as language models) discover the content on your site more easily. Similar to how XML sitemaps assist search engines, the llm.txt file guides AI crawlers by providing important details about the available site content, improving visibility and discoverability across AI-driven tools.', 'wp-seo-pilot' ); ?>
								<a href="https://llmstxt.org/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn More →', 'wp-seo-pilot' ); ?></a>
								</p>
							</div>
							<div class="wpseopilot-card-body">
								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><?php esc_html_e( 'Enable llm.txt', 'wp-seo-pilot' ); ?></label>
									</div>
									<div class="wpseopilot-form-control">
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="enable-llm-txt" name="wpseopilot_enable_llm_txt" value="1" <?php checked( $llm_enabled, '1' ); ?>>
											<label for="enable-llm-txt"><?php esc_html_e( 'Generate an llm.txt file to help AI engines discover the content on your site more easily.', 'wp-seo-pilot' ); ?></label>
										</div>
										<?php if ( '1' === $llm_enabled ) : ?>
										<div style="margin-top: 12px;">
											<a href="<?php echo esc_url( home_url( '/llm.txt' ) ); ?>" target="_blank" class="button button-secondary">
												<?php esc_html_e( 'Open llm.txt', 'wp-seo-pilot' ); ?> ↗
											</a>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>

						<?php if ( '1' === $llm_enabled ) : ?>
						<!-- LLM.txt Settings Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h2><?php esc_html_e( 'LLM.txt Settings', 'wp-seo-pilot' ); ?></h2>
								<p><?php esc_html_e( 'Customize how your llm.txt file is generated and what content it includes.', 'wp-seo-pilot' ); ?></p>
							</div>
							<div class="wpseopilot-card-body">
								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label for="llm-txt-title"><?php esc_html_e( 'Title', 'wp-seo-pilot' ); ?></label>
										<p class="wpseopilot-helper-text"><?php esc_html_e( 'The main title displayed at the top of your llm.txt file.', 'wp-seo-pilot' ); ?></p>
									</div>
									<div class="wpseopilot-form-control">
										<input type="text"
											   id="llm-txt-title"
											   name="wpseopilot_llm_txt_title"
											   value="<?php echo esc_attr( $llm_title ); ?>"
											   class="wpseopilot-input"
											   placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
										<span class="wpseopilot-helper-text"><?php esc_html_e( 'Defaults to your site name if left empty.', 'wp-seo-pilot' ); ?></span>
									</div>
								</div>

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label for="llm-txt-description"><?php esc_html_e( 'Description', 'wp-seo-pilot' ); ?></label>
										<p class="wpseopilot-helper-text"><?php esc_html_e( 'A brief description of your site displayed below the title.', 'wp-seo-pilot' ); ?></p>
									</div>
									<div class="wpseopilot-form-control">
										<textarea
											id="llm-txt-description"
											name="wpseopilot_llm_txt_description"
											rows="3"
											class="wpseopilot-textarea"
											placeholder="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>"><?php echo esc_textarea( $llm_description ); ?></textarea>
										<span class="wpseopilot-helper-text"><?php esc_html_e( 'Defaults to your site tagline if left empty.', 'wp-seo-pilot' ); ?></span>
									</div>
								</div>

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label for="llm-txt-posts-per-type"><?php esc_html_e( 'Max Posts Per Type', 'wp-seo-pilot' ); ?></label>
										<p class="wpseopilot-helper-text"><?php esc_html_e( 'Limit the number of posts included per post type.', 'wp-seo-pilot' ); ?></p>
									</div>
									<div class="wpseopilot-form-control">
										<input type="number"
											   id="llm-txt-posts-per-type"
											   name="wpseopilot_llm_txt_posts_per_type"
											   value="<?php echo esc_attr( $llm_posts_per_type ); ?>"
											   min="1"
											   max="500"
											   class="wpseopilot-input small">
										<span class="wpseopilot-helper-text"><?php esc_html_e( 'Set between 1-500 posts per post type (recommended: 50).', 'wp-seo-pilot' ); ?></span>
									</div>
								</div>

								<div class="wpseopilot-form-row">
									<div class="wpseopilot-form-label">
										<label><?php esc_html_e( 'Options', 'wp-seo-pilot' ); ?></label>
									</div>
									<div class="wpseopilot-form-control">
										<div class="wpseopilot-toggle">
											<input type="checkbox" id="include-excerpt" name="wpseopilot_llm_txt_include_excerpt" value="1" <?php checked( $llm_include_excerpt, '1' ); ?>>
											<label for="include-excerpt"><?php esc_html_e( 'Include post excerpts/descriptions in llm.txt', 'wp-seo-pilot' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Content Preview Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h2><?php esc_html_e( 'Content Types Included', 'wp-seo-pilot' ); ?></h2>
								<p><?php esc_html_e( 'The following post types will be included in your llm.txt file.', 'wp-seo-pilot' ); ?></p>
							</div>
							<div class="wpseopilot-card-body">
								<div class="wpseopilot-post-types-list">
									<?php foreach ( $post_types as $post_type ) : ?>
										<?php
										$count = wp_count_posts( $post_type->name );
										$published = $count->publish ?? 0;
										$will_include = min( $published, $llm_posts_per_type );
										?>
										<div class="wpseopilot-post-type-item">
											<div class="wpseopilot-post-type-icon">
												<span class="dashicons dashicons-admin-post"></span>
											</div>
											<div class="wpseopilot-post-type-info">
												<strong><?php echo esc_html( $post_type->label ); ?></strong>
												<span class="wpseopilot-helper-text">
													<?php
													/* translators: 1: number of posts to include, 2: total number of published posts */
													echo esc_html( sprintf( __( 'Including %1$d of %2$d published posts', 'wp-seo-pilot' ), $will_include, $published ) );
													?>
												</span>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>

						<!-- Save Button -->
						<p class="submit" style="margin-top: 20px;">
							<input type="submit" name="wpseopilot_llm_txt_submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-seo-pilot' ); ?>">
						</p>
						<?php endif; ?>

					</div>

					<!-- Sidebar -->
					<div class="wpseopilot-settings-sidebar">
						<?php if ( '1' === $llm_enabled ) : ?>
						<!-- Quick Info Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h3><?php esc_html_e( 'Quick Info', 'wp-seo-pilot' ); ?></h3>
							</div>
							<div class="wpseopilot-card-body">
								<p class="wpseopilot-helper-text">
									<?php esc_html_e( 'Your llm.txt file is accessible at:', 'wp-seo-pilot' ); ?>
								</p>
								<p>
									<code style="word-break: break-all; display: block; padding: 8px; background: #f6f7f7; border-radius: 4px;">
										<?php echo esc_html( home_url( '/llm.txt' ) ); ?>
									</code>
								</p>
								<p class="wpseopilot-helper-text" style="margin-top: 12px;">
									<?php
									echo sprintf(
										/* translators: %s: link to permalinks settings */
										esc_html__( 'Note: If the file is not accessible, visit %s and save to flush rewrite rules.', 'wp-seo-pilot' ),
										'<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'Permalink Settings', 'wp-seo-pilot' ) . '</a>'
									);
									?>
								</p>
							</div>
						</div>

						<!-- About llm.txt Card -->
						<div class="wpseopilot-card">
							<div class="wpseopilot-card-header">
								<h3><?php esc_html_e( 'About llm.txt', 'wp-seo-pilot' ); ?></h3>
							</div>
							<div class="wpseopilot-card-body">
								<p class="wpseopilot-helper-text">
									<?php esc_html_e( 'The llm.txt file helps AI language models like ChatGPT, Claude, and others discover and understand your content structure.', 'wp-seo-pilot' ); ?>
								</p>
								<p class="wpseopilot-helper-text">
									<?php esc_html_e( 'This can improve how AI systems reference and cite your content when answering questions.', 'wp-seo-pilot' ); ?>
								</p>
								<a href="https://llmstxt.org/" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="margin-top: 12px;">
									<?php esc_html_e( 'Learn More About llm.txt', 'wp-seo-pilot' ); ?> ↗
								</a>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<style>
.wpseopilot-post-types-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.wpseopilot-post-type-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background: #f6f7f7;
	border-radius: 6px;
}

.wpseopilot-post-type-icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #fff;
	border-radius: 6px;
}

.wpseopilot-post-type-icon .dashicons {
	color: #2271b1;
	width: 24px;
	height: 24px;
	font-size: 24px;
}

.wpseopilot-post-type-info {
	display: flex;
	flex-direction: column;
	gap: 2px;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Add additional page
	$('#add-additional-page').on('click', function() {
		var index = $('#additional-pages-container .additional-page-row').length;
		var html = '<div class="additional-page-row">' +
			'<input type="url" name="wpseopilot_sitemap_additional_pages[' + index + '][url]" placeholder="https://example.com/page" class="wpseopilot-input">' +
			'<input type="text" name="wpseopilot_sitemap_additional_pages[' + index + '][priority]" value="0.5" placeholder="0.5">' +
			'<button type="button" class="button remove-page"><?php esc_html_e( 'Remove', 'wp-seo-pilot' ); ?></button>' +
			'</div>';
		$('#additional-pages-container').append(html);
	});

	// Remove additional page
	$(document).on('click', '.remove-page', function() {
		$(this).closest('.additional-page-row').remove();
	});

	// Regenerate sitemap
	$('#regenerate-sitemap').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).text(WPSEOPilotSitemap.strings.regenerating);

		$.post(WPSEOPilotSitemap.ajax_url, {
			action: 'wpseopilot_regenerate_sitemap',
			nonce: WPSEOPilotSitemap.nonce
		}, function(response) {
			if (response.success) {
				alert(WPSEOPilotSitemap.strings.success);
			} else {
				alert(WPSEOPilotSitemap.strings.error);
			}
			$btn.prop('disabled', false).text('<?php esc_html_e( 'Regenerate Now', 'wp-seo-pilot' ); ?>');
		}).fail(function() {
			alert(WPSEOPilotSitemap.strings.error);
			$btn.prop('disabled', false).text('<?php esc_html_e( 'Regenerate Now', 'wp-seo-pilot' ); ?>');
		});
	});
});
</script>
