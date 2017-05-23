<?php
namespace BrownBox\Express;

/*
Title: View Statistics
Post Type: panel
Context: normal
Priority: high
Order: 1
New: false
*/

global $post;

$view_tracking = new Addon\ViewTracking();
$views = $view_tracking->get_post_views($post->ID);
?>
<style>
.bbx_stats_tile {width: 25%; float: left; text-align: center;}
.bbx_stats_section_title {font-size: 1.5rem; margin-bottom: 0.5rem; padding-top: 2rem; clear: both;}
.bbx_stats_heading {font-size: 1.2rem; margin-bottom: 0;}
.bbx_stats_value {font-size: 1.75rem; font-weight: bold; margin-top: 0.5rem;}
</style>
<?php
foreach ($views as $time => $stats) {
    switch ($time) {
        case 'week':
            $title = 'Last 7 Days';
            break;
        case 'month':
            $title = 'Last 30 Days';
            break;
        case 'forever':
        default:
            $title = 'All Time';
    }
    $includes = $stats[Addon\ViewTracking::RECORD_TYPE_INCLUSION];
    $views = $stats[Addon\ViewTracking::RECORD_TYPE_VIEWPORT];
    $mouseovers = $stats[Addon\ViewTracking::RECORD_TYPE_MOUSEOVER];
    $clicks = $stats[Addon\ViewTracking::RECORD_TYPE_CLICK];
?>
<p class="bbx_stats_section_title"><?php echo $title; ?></h2>
<div class="bbx_stats_tile">
    <p class="bbx_stats_heading">Included on Page</p>
    <p class="bbx_stats_value"><?php echo $includes; ?></p>
</div>
<div class="bbx_stats_tile">
    <p class="bbx_stats_heading">View Count</p>
    <p class="bbx_stats_value"><?php echo $views; ?></p>
</div>
<div class="bbx_stats_tile">
    <p class="bbx_stats_heading">Mouseover Count</p>
    <p class="bbx_stats_value"><?php echo $mouseovers; ?></p>
</div>
<div class="bbx_stats_tile">
    <p class="bbx_stats_heading">Click Count</p>
    <p class="bbx_stats_value"><?php echo $clicks; ?></p>
</div>
<hr>
<?php
}
