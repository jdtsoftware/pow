<?php

return [
    'payment_gateway' => 'stripe',
    'stripe_options' => [
        'publishable_key' => 'pk_test_nrA07aKBATY8fEMJ2yDwOldr',
        'secret_key' => 'sk_test_emymmJlZEmEs0sobaxM2WSYR',
        'currency' => 'GBP',
    ],

    'vat' => '20.00',

    //see http://php.net/manual/en/function.money-format.php
    'locale' => 'en_GB.UTF-8',
    'money_format' => '%.2n',
    //see http://php.net/manual/en/function.money-format.php

    'route_prefix' => 'pow',
    'route_domain' => config('jdt.domain'),
    'route_middleware' => ['auth', 'web'],

    'models' => [
        'product' => JDT\Pow\Entities\Product\Product::class,
        'product_token' => JDT\Pow\Entities\Product\ProductToken::class,
        'order' => JDT\Pow\Entities\Order\Order::class,
        'order_item' => JDT\Pow\Entities\Order\OrderItem::class,
        'wallet' => JDT\Pow\Entities\Wallet\Wallet::class,
        'wallet_token' => JDT\Pow\Entities\Wallet\WalletToken::class,
        'wallet_token_type' => \JDT\Pow\Entities\Wallet\WalletTokenType::class,
    ],
    'classes' => [
        'product' => \JDT\Pow\Service\Product::class,
        'order' => \JDT\Pow\Service\Order::class,
        'wallet' => \JDT\Pow\Service\Wallet::class,
        'basket' => \JDT\Pow\Service\Basket::class,
    ],
    'gateways' => [
        'stripe' => \JDT\Pow\Service\Gateway\Stripe::class,
    ]
];
