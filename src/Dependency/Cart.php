<?php

namespace BrownBox\Express\Dependency;

use BrownBox\Express\Interfaces as Interfaces;
use BrownBox\Express\Base as Base;

class Cart extends Base\Dependency implements Interfaces\Dependency {

    /**
     * Class constructor
     */
    public function __construct() {

        $this->_name = 'Cart';
        $this->_plugin_name = 'bb_cart';

    }

}