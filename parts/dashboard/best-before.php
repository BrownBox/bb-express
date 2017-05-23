<?php

use BrownBox\Express\Helper as Helper;

/**
 *
 * Title: "Best Before" Posts Report
 */
?>

<?php

/**
 * Set up arguments for get_posts() to:
 *
 *  1) Filter out epiry date in the future
 *  2) Order by the expiry dates ascending
 */
$args = [

	'post_type' => [ 'post', 'page' ],
	'meta_key' => 'bbx_best_before_expiry_date',
	'orderby'   => 'meta_value',
	'order' => 'ASC',
	'meta_query' => array(
		array(
			'key' => 'bbx_best_before_expiry_date',
			'value' => date("Y-m-d"),
			'compare' => '<=',
			'type' => 'DATE'
		)
	)

];

$stale_posts = get_posts( $args );
$current_date = time();

?>

<div id="published-posts" class="activity-block">

	<h3>Posts that have expired and require your attention</h3>

	<table class="widefat">

		<tbody>

			<?php if ( ! empty( $stale_posts ) ) : ?>

				<tr>
					<th class="row-title">Post title</th>
					<th>Past due</th>
					<th>&nbsp;</th>
				</tr>

				<?php foreach ( $stale_posts as $stale_post ) : ?>

					<?php

					// Work out different between today's date and expiry date in seconds
					$dates_difference = $current_date - strtotime( $stale_post->bbx_best_before_expiry_date );

					// Convert difference in seconds into chunks of years, days, minutes and seconds
					$converted_time = Helper\DateTime::seconds_to_time( $dates_difference );

					?>

					<?php if ( $converted_time['days'] > 0 ) : ?>

						<tr>

							<td class="row-title">
								<a href="/wp-admin/post.php?post=<?= $stale_post->ID ?>&action=edit#bb_express_best_before" target="_blank"><?= $stale_post->post_title ?></a>
								<small>(<?= $stale_post->post_type ?>)</small>
							</td>

							<td><span class="highlighted-value"><?= $converted_time['days'] ?> day(s)</span></td>
							<td><a href="/wp-admin/post.php?post=<?= $stale_post->ID ?>&action=edit#bb_express_best_before" target="_blank">Edit</a></td>

						</tr>

					<?php endif; ?>

				<?php endforeach; ?>

			<?php else : ?>

				<tr>

					<td class="row-title" colspan="3">
						Hooray! There aren't any expired posts.
					</td>

				</tr>

			<?php endif; ?>

		</tbody>

	</table>

</div>