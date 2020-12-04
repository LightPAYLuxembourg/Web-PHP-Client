<?php


namespace LightPAY\Framework;


class Responder
{

    public function __construct($data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }
}