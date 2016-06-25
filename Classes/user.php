<?php

namespace ppm;

class User
{
    public $id;

    public $last_login;


    public function getDaysInActive()
    {
        return floor((time() - strtotime($this->last_login)) / 86400);
    }
}