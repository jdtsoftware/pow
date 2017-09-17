<?php

namespace JDT\Pow\Composers;

class WalletComposer {

    public function compose($view)
    {
        $pow = app('pow');
        $pow->wallet();


    }

}