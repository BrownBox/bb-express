<?php

namespace BrownBox\Express\Dependency;

use BrownBox\Express\Interfaces as Interfaces;
use BrownBox\Express\Base as Base;

class GravityForms extends Base\Dependency implements Interfaces\Dependency {

    /**
     * Class constructor
     */
    public function __construct() {

        $this->_name = 'Gravity Forms';
        $this->_plugin_name = 'gravityforms';

    }

}