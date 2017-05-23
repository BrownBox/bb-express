<?php

namespace BrownBox\Express\Addon;

use BrownBox\Express\Interfaces as Interfaces;
use BrownBox\Express\Base as Base;

class Webhooks extends Base\Addon implements Interfaces\Addon {

    /**
     * Class constructor
     */
    public function __construct() {

        $this->_name = __( 'Webhooks', 'bb' );
        $this->_description = __( 'Manage webhooks in one place', 'bb' );
        $this->_current_version = '0.1a';

        $dependencies = [];

        $this->set_dependencies( $dependencies );

    }


}