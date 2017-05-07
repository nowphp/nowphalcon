<?php

namespace Single\Models;

use Phalcon\Mvc\Model;

class Record extends Model
{
    public function initialize()
    {
        $this->setConnectionService("db");
        $this->setSource("record");
    }
}
