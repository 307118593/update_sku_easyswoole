<?php


namespace App\Pool;


class StdPool extends \EasySwoole\Pool\AbstractPool{

    protected function createObject()
    {
        return new Std();
    }
}