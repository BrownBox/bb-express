<?php
namespace BrownBox\Express;

/*
Title: Custom Styles for this Post
Context: normal
Order: 50
*/

piklist('field', array(
        'label' => 'Post Styles',
        'field' => 'bbx_style',
        'type' => 'textarea',
        'attributes' => array(
                'rows' => 10,
                'cols' => 50,
                'class' => 'large-text',
        ),
));

piklist('field', array(
        'label' => 'Post Class',
        'field' => 'bbx_class',
        'type' => 'text',
));
