<?php

return [
    'models' => [
        'product' => JDT\Pow\Entities\Product::class,
        'order' => JDT\Pow\Entities\Order::class,
        'wallet' => JDT\Pow\Entities\Wallet::class
    ],
    'classes' => [
        'product' => \JDT\Pow\Product::class,
        'order' => \JDT\Pow\Order::class,
        'wallet' => \JDT\Pow\Wallet::class,
    ]
];
