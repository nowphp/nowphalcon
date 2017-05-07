<?php

namespace Single\Models;

use Phalcon\Mvc\Model;

class Dm extends Model
{
    public function initialize()
    {
        $this->setConnectionService("db");
        $this->setSource("dm");
    }
}
