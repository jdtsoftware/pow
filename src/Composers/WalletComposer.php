<?php

namespace JDT\Pow\Composers;

class WalletComposer {

    public function compose($view)
    {
        $pow = app('pow');

        if($pow->hasWallet()) {
            $wallet = $pow->wallet();

            $view
                ->with('has_wallet', $pow->hasWallet())
                ->with('balance', $wallet->balance())
                ->with('tokens', $wallet->token());
        }
    }

}