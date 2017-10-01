<?php

return [
    'payment_gateway' => 'stripe',
    'stripe_options' => [
        'publishable_key' => '',
        'secret_key' => '',
        'currency' => 'GBP',
    ],

    'vat' => '20.00',

    //see http://php.net/manual/en/function.money-format.php
    'locale' => 'en_GB.UTF-8',
    'money_format' => '%.2n',
    'currency_sign' => '£',

    //see http://php.net/manual/en/function.money-format.php

    'route_prefix' => 'pow',
    'route_domain' => '',
    'route_middleware' => '',

    'models' => [
        'user' => 'Change_Me_To_Your_User_Model',
        'product' => JDT\Pow\Entities\Product\Product::class,
        'product_shop' => JDT\Pow\Entities\Product\Shop::class,
        'product_token' => JDT\Pow\Entities\Product\ProductToken::class,
        'product_adjustment_price' => \JDT\Pow\Entities\Product\ProductAdjustmentPrice::class,
        'order' => JDT\Pow\Entities\Order\Order::class,
        'order_item' => JDT\Pow\Entities\Order\OrderItem::class,
        'order_status' => \JDT\Pow\Entities\Order\OrderStatus::class,
        'wallet' => JDT\Pow\Entities\Wallet\Wallet::class,
        'wallet_token' => JDT\Pow\Entities\Wallet\WalletToken::class,
        'wallet_token_type' => \JDT\Pow\Entities\Wallet\WalletTokenType::class,
        'wallet_transaction_type' => \JDT\Pow\Entities\Wallet\WalletTransactionType::class,
    ],
    'classes' => [
        'product' => \JDT\Pow\Service\Product::class,
        'shop' => \JDT\Pow\Service\Shop::class,
        'order' => \JDT\Pow\Service\Order::class,
        'wallet' => \JDT\Pow\Service\Wallet::class,
        'basket' => \JDT\Pow\Service\Basket::class,
    ],
    'gateways' => [
        'stripe' => \JDT\Pow\Service\Gateway\Stripe::class,
    ]
];
