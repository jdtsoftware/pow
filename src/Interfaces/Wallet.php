<?php

namespace JDT\Pow\Interfaces;

use JDT\Pow\Interfaces\IdentifiableId as iIdentifiableId;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;

interface Wallet {

    public function getId();
    public function getUuid();
    public function balance($type);
    public function overdraft();
    public function credit(iIdentifiableId $creator, Redeemable $linker, iOrderItemEntity $orderItem);
    public function debit(iIdentifiableId $creator, Redeemable $linker, iOrderItemEntity $orderItem);

    public function getVatPerecentage();
    public function isVatExempt();

    public function getOwner();
}