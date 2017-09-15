<?php

namespace JDT\Pow\Interfaces;

use JDT\Pow\Interfaces\IdentifiableId as iIdentifiableId;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Entities\WalletTokenType as iWalletTokenTypeEntity;

interface Wallet {

    public function getId();
    public function getUuid();
    public function balance($type);
    public function overdraft();
    public function credit(iIdentifiableId $creator, int $tokens, iWalletTokenTypeEntity $type, iIdentifiableId $linker, iOrderItemEntity $orderItem);
    public function debit(iIdentifiableId $creator, int $tokens, iWalletTokenTypeEntity $type, iIdentifiableId $linker, iOrderItemEntity $orderItem);
}