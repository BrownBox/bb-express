<?php

namespace BrownBox\Express;

/*
Title: "Best Before" settings
Post Type: page, post
Context: side
Order: 12
*/

global $post;

if ( ! empty( $post->bbx_best_before_expiry_date ) ) {
    $custom_text = '<span class="highlighted-value">will expire</span> on <span class="highlighted-value">' . $post->bbx_best_before_expiry_date . '</span>';
} elseif( ! empty( $post->bbx_best_before_months ) ) {
    $custom_text = '<span class="highlighted-value">best before</span> <span class="highlighted-value">' . $post->bbx_best_before_months . '</span>';
} else {
    $custom_text = '<span class="highlighted-value">never expires</span>';
}

if ( $post->bbx_best_before_unpublish_when_expired ) {
    $custom_text .= ' <br/><br/>It will be <span class="highlighted-value">unpublished</span> once expired';
}

$time_now = time();

$three_months = date( 'Y-m-d', strtotime("+3 months", $time_now ) );
$six_months = date( 'Y-m-d', strtotime("+6 months", $time_now ) );
$nine_months = date( 'Y-m-d', strtotime("+9 months", $time_now ) );

// Set up options for the bbx_best_before_expiry_presets dropdown
$time_periods = array(
        '3-months' => date( 'Y-m-d', strtotime("+3 months", $time_now ) ),
        '6-months' => date( 'Y-m-d', strtotime("+6 months", $time_now ) ),
        '9-months' => date( 'Y-m-d', strtotime("+9 months", $time_now ) ),
        'never' => "",
);

// Define meta fields -----------------------------------------------------------------------------------------------

// Display the date until which the content is not considered stale
piklist( 'field', array(
        'type' => 'html',
        'label' => '',
        'value' => '<div class="callout"><p>The content of this post ' . $custom_text . '</span></p></div>',
));

$click_tiles_markup = <<<MULTI

<ul class="click-tiles">
    <li class="click-tile click-tile-3-months" data-period="{$three_months}"><a href="#">3m</a></li>
    <li class="click-tile click-tile-6-months" data-period="{$six_months}"><a href="#">6m</a></li>
    <li class="click-tile click-tile-9-months" data-period="{$nine_months}"><a href="#">9m</a></li>
    <li class="click-tile click-tile-never" data-period=""><a href="#">Never</a></li>
</ul>

MULTI;

// Click tiles
piklist( 'field', array(
        'type' => 'html',
        'field' => 'bbx_best_before_click_tiles',
        'label' => 'Review in',
        'value' => $click_tiles_markup,
));

// OR
piklist( 'field', array(
        'type' => 'html',
        'field' => 'bbx_best_before_or',
        'label' => '',
        'value' => '<h3 class="or">-OR-</h3>',
));

// Expiry date
piklist('field', array(
        'type' => 'datepicker',
        'field' => 'bbx_best_before_expiry_date',
        'label' => 'Select a custom expiration date',
        'value' => '',
        'options' => array(
                'dateFormat' => 'yy-mm-dd',
                'minDate' => -1, // 1 day from today, i.e. tomorrow
        ),
));

piklist( 'field', array(
        'type' => 'checkbox',
        'field' => 'bbx_best_before_unpublish_when_expired',
        'label' => 'Unpublish when expired',
        'value' => '1',
        'choices' => array(
                '1' => 'Yes',
        ),
));
