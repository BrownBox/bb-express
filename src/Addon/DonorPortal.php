<?php

namespace BrownBox\Express\Addon;

use BrownBox\Express\Interfaces as Interfaces;
use BrownBox\Express\Base as Base;

class DonorPortal extends Base\Addon implements Interfaces\Addon {

    /**
     * Class constructor
     */
    public function __construct() {

        $this->_name =  __( 'Donor Portal', 'bb' );
        $this->_description = __( 'Spend time on your cause, not on Donor Administration.', 'bb' );
        $this->_current_version = 1.7;

        $dependencies = [
            'GravityForms',
            'Cart',
            'GravityFormsPaydockAddon'
        ];

        $this->set_dependencies( $dependencies );

    }

}