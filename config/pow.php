<?php

return [
    'payment_gateway' => 'stripe',
    'stripe_options' => [
        'publishable_key' => 'pk_test_nrA07aKBATY8fEMJ2yDwOldr',
        'secret_key' => 'sk_test_emymmJlZEmEs0sobaxM2WSYR',
    ],

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
    ]
];
