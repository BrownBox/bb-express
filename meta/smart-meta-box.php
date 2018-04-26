<?php
// include('classes/cpt_.php');
// include('classes/meta_.php');
// include('classes/tax_.php');

// add_action('admin_init', 'get_all_post_type');
// function get_all_post_type(){
//     $args = array(
//             'public' => true,
//     );
//     $post_types = get_post_types($args, 'object', 'and');
//     return $post_types;
// }

function post_metabox() {
    $exclude = ( null !== SMARTBOX_EXCLUDE ) ? unserialize(SMARTBOX_EXCLUDE) : array();

    global $post;

    // metabox_content_analysis
    if( !in_array($post->post_type, $exclude['metabox_content_analysis'])) {
        add_meta_box( 'cpt_'.$post->post_type.'_box', __( 'BB Content Analysis', '' ), 'metabox_content_analysis', $post->post_type, 'side', 'high' );
    }

    // metabox_has_children & metabox_has_parent
    if( is_post_type_hierarchical($post->post_type) ) {

        // metabox_has_children
        if( !in_array($post->post_type, $exclude['metabox_has_children'])) {
            if ( false === $children = get_transient( 'bb_has_children_'.$post->ID )) {
                $args = array(
                        'posts_per_page'   => -1,
                        'post_type'        => $post->post_type,
                        'post_parent'      => $post->ID,
                        'post_status'      => 'any',
                        'suppress_filters' => true
                );
                $children = get_posts( $args );
                set_transient('bb_has_children_'.$post->ID, $children, 30 * DAY_IN_SECONDS);
            }

            if(count($children) > 0 ) {
                add_meta_box( 'cpt_'.$post->post_type.'_has_children_box', __( 'Post Children', '' ), 'metabox_has_children', $post->post_type, 'side', 'high' );
            }
        }

        // metabox_has_parent
        if( !in_array($post->post_type, $exclude['metabox_has_parent'])) {
            $ancestors = get_ancestors($post->ID, $post->post_type);
            if(!empty($ancestors)){
                add_meta_box( 'cpt_'.$post->post_type.'_has_parent_box', __( 'Post Parent', '' ), 'metabox_has_parent', $post->post_type, 'side', 'high' );
            }
        }
    }

}
add_action( 'add_meta_boxes', 'post_metabox' );

function metabox_has_parent($post){
    $ancestors = get_ancestors($post->ID, $post->post_type);
    foreach ($ancestors as $ancestor){
        $ancestor_post = get_post($ancestor);
        echo '<p><a href="/wp-admin/post.php?post='.$ancestor.'&action=edit">'.$ancestor_post->post_title.'</a> ('. ucwords($ancestor_post->post_status).')</p>';
    }
}

function metabox_has_children($post){
    $args = array(
            'posts_per_page'   => -1,
            'post_type'        => $post->post_type,
            'post_parent'      => $post->ID,
            'post_status'      => 'any',
            'suppress_filters' => true
    );
    $children = get_posts( $args );
    foreach($children as $child) {
        echo '<p><a href="/wp-admin/post.php?post='.$child->ID.'&action=edit">'.$child->post_title.'</a> ('. ucwords($child->post_status).')</p>';
    }
}


function metabox_content_analysis( $post ) {

    $min = 2;

    //delete_transient('bb_content_analysis_'.$post->ID);
    //$results = get_transient( 'bb_content_analysis_'.$post->ID );
    if ( false === $results = get_transient( 'bb_content_analysis_'.$post->ID )) {

        //$post_types = get_all_post_type();
        $meta = bb_get_post_meta($post->ID);

        if($post->post_status !== 'auto-draft' && strlen($post->post_name) > $min) {

            $args = array(
                    'posts_per_page'   => -1,
                    'post_type'        => $post->post_type,
                    'post_parent'      => $post->ID,
                    'post_status'      => 'publish',
                    'suppress_filters' => true
            );
            $children = get_posts( $args );

            $args = array(
                    'posts_per_page'   => -1,
                    'post_type'        => $post->post_name,
                    'post_status'      => 'publish',
                    'suppress_filters' => true
            );
            $post_types = get_posts( $args );

            $count = 0;
            $results = array();
            $results['fails'] = 0;
            $results['date'] = time()+36000;
            $results['ID'] = $post->ID;

            // Title test 2
            $count++;
            $results[$count]['name'] = 'Has a <span>Post Title</span>';
            $results[$count]['result'] = (strlen($post->post_title) > $min) ? 'yes' : 'no';
            if($results[$count]['result'] == 'no') $results['fails']++;
            $results[$count]['markup'] = '<p class="'.$results[$count]['result'].'"><span class="dashicons dashicons-'.$results[$count]['result'].'"></span>'.$results[$count]['name'].'</p>';
            // End Test

            // Content test 2
            $count++;
            $results[$count]['name'] = 'Has <span>Post Content</span>';
            $results[$count]['result'] = (strlen($post->post_content) > $min || count($children) > 0 || count($post_types) > 0 ) ? 'yes' : 'no';
            if(count($children) > 0) $notes[] = 'has children';
            if(count($post_types) > 0) $notes[] = 'archive for cpt '.$post->post_name;
            if(count($notes)>0){
                $results[$count]['name'] .= ' ('.implode(', ', $notes).')</span>';
                unset($notes);
            }
            if($results[$count]['result'] == 'no') $results['fails']++;
            $results[$count]['markup'] = '<p class="'.$results[$count]['result'].'"><span class="dashicons dashicons-'.$results[$count]['result'].'"></span>'.$results[$count]['name'].'</p>';
            // End Test

            // Content test 1
            $count++;
            $results[$count]['name'] = 'No <span>Lorem Ipsum</span>';
            $results[$count]['description'] = 'Lorem Ipsum is often used as placeholder content.';
            $results[$count]['result'] = ((strstr(strtolower($post->post_content), 'lorem')) == false && (strstr(strtolower($post->post_title), 'lorem')) == false && (strstr(strtolower($post->post_excerpt), 'lorem')) == false ) ? 'yes' : 'no';
            if((strstr(strtolower($post->post_content), 'lorem')) !== false) $notes[] = 'post_content';
            if((strstr(strtolower($post->post_title), 'lorem')) !== false) $notes[] = 'post_title';
            if((strstr(strtolower($post->post_excerpt), 'lorem')) !== false) $notes[] = 'post_excerpt';
            if(count($notes)>0){
                $results[$count]['name'] .= ' ('.implode(', ', $notes).')</span>';
                unset($notes);
            }
            if($results[$count]['result'] == 'no') $results['fails']++;
            $results[$count]['markup'] = '<p class="'.$results[$count]['result'].'"><span class="dashicons dashicons-'.$results[$count]['result'].'"></span>'.$results[$count]['name'].'</p>';
            // End Test

            // Excerpt test 2
            if(post_type_supports( $post->post_type, 'excerpt' )) {
                $count++;
                $results[$count]['name'] = 'Has <span>Post Excerpt</span>';
                $results[$count]['result'] = (strlen($post->post_excerpt) > $min) ? 'yes' : 'no';
                if($results[$count]['result'] == 'no') $results['fails']++;
                $results[$count]['markup'] = '<p class="'.$results[$count]['result'].'"><span class="dashicons dashicons-'.$results[$count]['result'].'"></span>'.$results[$count]['name'].'</p>';
                // End Test
            }

            // Featured Image test
            if(post_type_supports( $post->post_type, 'thumbnail' ) && $post->post_type == 'product') {
                $count++;
                $results[$count]['name'] = 'Has <span>Featured Image</span>';
                $results[$count]['result'] = (has_post_thumbnail($post->ID) == true) ? 'yes' : 'no';
                if($results[$count]['result'] == 'no') $results['fails']++;
                $results[$count]['markup'] = '<p class="'.$results[$count]['result'].'"><span class="dashicons dashicons-'.$results[$count]['result'].'"></span>'.$results[$count]['name'].'</p>';
                // End Test
            }

            // BB Hero Meta tests
            $hero_post_types = array('page','project');
            if(in_array($post->post_type, $hero_post_types)){

                // BB Hero Meta Image Large
                $count++;
                $results[$count]['name'] = 'Has <span>Default Hero Image</span>';
                $results[$count]['result'] = (!empty($meta['hero_image'])) ? 'yes' : 'no';
                if($results[$count]['result'] == 'no') $results['fails']++;
                $results[$count]['markup'] = '<p class="'.$results[$count]['result'].'"><span class="dashicons dashicons-'.$results[$count]['result'].'"></span>'.$results[$count]['name'].'</p>';
                // End Test


                // BB Hero Meta Image Medium
                $count++;
                $results[$count]['name'] = 'Has <span>Medium Hero Image</span>';
                $results[$count]['result'] = (!empty($meta['hero_image_medium'])) ? 'yes' : 'no';
                if($results[$count]['result'] == 'no') $results['fails']++;
                $results[$count]['markup'] = '<p class="'.$results[$count]['result'].'"><span class="dashicons dashicons-'.$results[$count]['result'].'"></span>'.$results[$count]['name'].'</p>';
                // End Test


            // BB Hero Meta Image Small
                $count++;
                $results[$count]['name'] = 'Has <span>Small Hero Image</span>';
                $results[$count]['result'] = (!empty($meta['hero_image_small'])) ? 'yes' : 'no';
                if($results[$count]['result'] == 'no') $results['fails']++;
                $results[$count]['markup'] = '<p class="'.$results[$count]['result'].'"><span class="dashicons dashicons-'.$results[$count]['result'].'"></span>'.$results[$count]['name'].'</p>';
                // End Test

            }

        set_transient('bb_content_analysis_'.$post->ID, $results, 30 * DAY_IN_SECONDS);

        }

    }

    if($post->post_status !== 'auto-draft' && strlen($post->post_name) > $min) {
        echo '<small style="display:block;opacity:0.25; margin-bottom:0.2rem;font-size:0.6rem;">Results Transient: bb_content_analysis_'.$post->ID.'</small>';
        foreach ($results as $key => $result) {
            if(strlen($result['markup']) > $min) echo $result['markup'];
        }
        echo '<p class="summary"><span>Score:</span>'.round((((count($results) - 3 - $results['fails'])/(count($results) - 3 )) * 100 ), 1).'%<br><span>Last tested:</span>'.date('d/m/Y', $results['date']).'</p>';
    } else {
        echo '<p>Not yet tested - Please publish the post</p>';
        delete_transient('bb_content_analysis_'.$post->ID);
    }


}

add_action( 'save_post', 'reanalyse_content' );

function reanalyse_content( $post_id ) {
    delete_transient('bb_content_analysis_'.$post_id);
    delete_transient('bb_has_children_'.$post_id);
}

function bb_content_analysis_add_dashboard_widgets() {

    wp_add_dashboard_widget(
            'bb_content_analysis_dashboard_widget',         // Widget slug.
            'BB Content Analysis',         // Title.
            'bb_content_analysis_dashboard_widget_function' // Display function.
            );
}
add_action( 'wp_dashboard_setup', 'bb_content_analysis_add_dashboard_widgets' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function bb_content_analysis_dashboard_widget_function() {

    echo '<style>.value {display: inline-block;width: 20px;text-align: right;}</style>';

    // Display whatever it is you want to show.
    $transients = BB_Transients::get('bb_content_analysis_');
    $summary = array();
    $summary['count'] = 0;
    foreach ($transients as $transient){
        //var_dump(str_replace('_transient_', '', $transient->name));
        //if($_GET['bb_content_analysis_'] == false) delete_transient(str_replace('_transient_', '', $transient->name));
        $array = unserialize($transient->value);

        foreach ($array as $data){
            if($data["result"] == 'no' && !empty($summary[$data["name"]])) {
                $summary[$data["name"]]++;
            }
            if($data["result"] == 'no' && empty($summary[$data["name"]])) {
                $summary[$data["name"]] = 1;
            }

        }
        $summary['count']++;
    }
    foreach($summary as $key => $value){
        if( $key == 'count' ) {
            $count = $value / 2;
            echo '<strong><span class="value">'.$count.'</span> Pages Tested</strong><br>';
        } else {
            echo '<span class="value">'.$value.'</span> pages failed "'.$key.'"<br>';
        }

    }
}



