<?php


namespace App\Network\CURL\Contract;


interface Wrapper
{
    public function setHandle($ch);

    public function exec();

    public function getContent();

    public function close();
}
