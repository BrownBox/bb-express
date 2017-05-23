<?php

namespace BrownBox\Express\Addon;

use BrownBox\Express\Interfaces as Interfaces;
use BrownBox\Express\Base as Base;

class Giving extends Base\Addon implements Interfaces\Addon {

    /**
     * Class constructor
     */
    public function __construct() {

        $this->_name = __( 'Branded Giving', 'bb' );
        $this->_description = __( 'Provide your donors with an Optimised, mobile responsive and branded giving page.', 'bb' );
        $this->_current_version = '2.3';

        $dependencies = [
            'GravityForms',
            'Cart',
        ];

        $this->set_dependencies( $dependencies );

    }


}