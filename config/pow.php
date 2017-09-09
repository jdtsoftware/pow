<?php

return [
    'route_domain' => config('jdt.admin_domain'),
    'route_middleware' => 'web',

    'models' => [
        'product' => JDT\Pow\Entities\Product::class,
        'order' => JDT\Pow\Entities\Order::class,
        'wallet' => JDT\Pow\Entities\Wallet::class,
        'wallet_token_type' => \JDT\Pow\Entities\WalletTokenType::class,
    ],
    'classes' => [
        'product' => \JDT\Pow\Service\Product::class,
        'order' => \JDT\Pow\Service\Order::class,
        'wallet' => \JDT\Pow\Service\Wallet::class,
        'basket' => \JDT\Pow\Service\Basket::class,
    ],
    'closures' => [
        'wallet_owner' => function() {
            return Auth::user()->organisations()->first();
        },
    ]
];
