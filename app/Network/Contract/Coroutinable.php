<?php


namespace App\Network\Contract;


interface Coroutinable
{
    public function setCoroutineMode(bool $mode);
}
