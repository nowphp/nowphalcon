<?php

namespace Single\Models;

use Phalcon\Mvc\Model;

class Products extends Model
{
    public function initialize()
    {
        $this->setConnectionService("dxfdb");
        $this->setSource("x_user");
    }
}
