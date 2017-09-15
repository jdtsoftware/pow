<?php

namespace JDT\Pow\Service;

use JDT\Pow\Entities\Wallet\WalletTokenType;
use JDT\Pow\Entities\Wallet\WalletTransaction;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Entities\WalletTokenType as iWalletTokenTypeEntity;
use JDT\Pow\Interfaces\WalletOwner as iWalletOwner;
use JDT\Pow\Interfaces\IdentifiableId as iIdentifiableId;
use Ramsey\Uuid\Uuid;

/**
 * Class Pow.
 */
class Wallet implements \JDT\Pow\Interfaces\Wallet
{
    public function __construct(iWalletOwner $walletOwner)
    {
        $this->models = \Config::get('pow.models');
        $this->wallet = $this->models['wallet']::find($walletOwner->getWalletId());
    }

    public function getId()
    {
        return $this->wallet->getId();
    }

    public function getUuid()
    {
        return $this->wallet->getUuid();
    }

    public function balance($type)
    {
        $tokenType = WalletTokenType::where('handle', $type)->first();
        $walletToken = $this->wallet->token($tokenType)->first();

        return $walletToken->tokens ?? 0;
    }

    public function overdraft()
    {
        return $this->wallet->overdraft;
    }

    public function credit(iIdentifiableId $creator, int $tokens, iWalletTokenTypeEntity $type, iIdentifiableId $linker, iOrderItemEntity $orderItem)
    {
        $walletToken = $this->findOrCreateWalletToken($type);

        WalletTransaction::create([
            'uuid' => Uuid::uuid4()->toString(),
            'wallet_id' => $this->getId(),
            'wallet_token_id' => $walletToken->getId(),
            'wallet_transaction_type_id' => 1,
            'tokens' => $tokens,
            'order_item_id' => $orderItem->getId(),
            'transaction_linker_id' => $linker->getId(),
            'transaction_linker_type' => get_class($linker),
            'created_user_id' => $creator->getId(),
        ]);

        $walletToken->update(['tokens' => $walletToken->tokens + $tokens]);
    }

    public function debit(iIdentifiableId $creator, int $tokens, iWalletTokenTypeEntity $type, iIdentifiableId $linker, iOrderItemEntity $orderItem)
    {
        $walletToken = $this->findOrCreateWalletToken($type);

        WalletTransaction::create([
            'uuid' => Uuid::uuid4()->toString(),
            'wallet_id' => $this->getId(),
            'wallet_token_id' => $walletToken->getId(),
            'wallet_transaction_type_id' => 2,
            'tokens' => $tokens,
            'order_item_id' => $orderItem->getId(),
            'transaction_linker_id' => $linker->getId(),
            'transaction_linker_type' => get_class($linker),
            'created_user_id' => $creator->getId(),
        ]);

        $walletToken->update(['tokens' => $walletToken->tokens - $tokens]);
    }

    protected function findOrCreateWalletToken(iWalletTokenTypeEntity $type)
    {
        $walletToken = $this->wallet->token($type)->first();

        if(!isset($walletToken)) {
            $walletToken = $this->wallet->createToken($type);
        }

        return $walletToken;
    }
}
