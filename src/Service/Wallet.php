<?php

namespace JDT\Pow\Service;

use JDT\Pow\Entities\Wallet\WalletTokenType;
use JDT\Pow\Entities\Wallet\WalletTransaction;
use JDT\Pow\Entities\Wallet\WalletTransactionType;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Entities\WalletToken;
use JDT\Pow\Interfaces\Entities\WalletTokenType as iWalletTokenTypeEntity;
use JDT\Pow\Interfaces\Redeemable;
use JDT\Pow\Interfaces\WalletOwner as iWalletOwner;
use JDT\Pow\Interfaces\IdentifiableId as iIdentifiableId;
use Ramsey\Uuid\Uuid;

/**
 * Class Pow.
 */
class Wallet implements \JDT\Pow\Interfaces\Wallet
{
    protected $models;
    protected $walletOwner;
    protected $wallet;

    /**
     * Wallet constructor.
     * @param iWalletOwner $walletOwner
     */
    public function __construct(iWalletOwner $walletOwner)
    {
        $this->models = \Config::get('pow.models');
        $this->walletOwner = $walletOwner;
        $this->wallet = $this->models['wallet']::find($walletOwner->getWalletId());
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return (bool) $this->models['wallet']::find($this->walletOwner->getWalletId());
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return (int) $this->wallet->getId();
    }

    /**
     * @return string
     */
    public function getUuid() : string
    {
        return (string) $this->wallet->getUuid();
    }

    /**
     * @param $type
     * @return int
     */
    public function balance($type) : int
    {
        $tokenType = WalletTokenType::where('handle', $type)->first();
        $walletToken = $this->wallet->token($tokenType)->first();

        return (int)  $walletToken->tokens ?? 0;
    }

    /**
     * @return int
     */
    public function overdraft() : int
    {
        return (int) $this->wallet->overdraft;
    }

    /**
     * @param iIdentifiableId $creator
     * @param Redeemable $linker
     * @param iOrderItemEntity $orderItem
     */
    public function credit(iIdentifiableId $creator, Redeemable $linker, iOrderItemEntity $orderItem) : void
    {
        $walletToken = $this->findOrCreateWalletToken($linker->getTokenType());
        $transaction = $this->createTransaction($creator, $linker, $orderItem, $walletToken, WalletTransactionType::CREDIT);
        $walletToken->update(['tokens' => $walletToken->tokens + $transaction->tokens]);
    }

    /**
     * @param iIdentifiableId $creator
     * @param Redeemable $linker
     * @param iOrderItemEntity $orderItem
     */
    public function debit(iIdentifiableId $creator, Redeemable $linker, iOrderItemEntity $orderItem) : void
    {
        $walletToken = $this->findOrCreateWalletToken($linker->getTokenType());
        $transaction = $this->createTransaction($creator, $linker, $orderItem, $walletToken, WalletTransactionType::DEBIT);
        $walletToken->update(['tokens' => $walletToken->tokens - $transaction->tokens]);
    }

    /**
     * @param iIdentifiableId $creator
     * @param Redeemable $linker
     * @param iOrderItemEntity $orderItem
     * @param WalletToken $walletToken
     * @param $transactionType
     * @return WalletTransaction
     */
    protected function createTransaction(iIdentifiableId $creator, Redeemable $linker, iOrderItemEntity $orderItem, WalletToken $walletToken, $transactionType) : WalletTransaction
    {
        $tokenValue = (int) $linker->getTokenValue();
        if($tokenValue < 0) {
            throw new Exception('Token cost cannot be negative, please use credit');
        }

        $transactionTypeEntity = $this->transactionType($transactionType);
        if(empty($transactionTypeEntity)) {
            throw new Exception('Cannot find transaction type: '.$transactionType);
        }

        return WalletTransaction::create([
            'uuid' => Uuid::uuid4()->toString(),
            'wallet_id' => $this->getId(),
            'wallet_token_id' => $walletToken->getId(),
            'wallet_transaction_type_id' => $transactionTypeEntity->id,
            'tokens' => $tokenValue,
            'order_item_id' => $orderItem->getId(),
            'transaction_linker_id' => $linker->getId(),
            'transaction_linker_type' => get_class($linker),
            'created_user_id' => $creator->getId(),
        ]);
    }

    /**
     * @param iWalletTokenTypeEntity $type
     * @return mixed
     */
    protected function findOrCreateWalletToken(iWalletTokenTypeEntity $type)
    {
        $walletToken = $this->wallet->token($type)->first();

        if(!isset($walletToken)) {
            $walletToken = $this->wallet->createToken($type);
        }

        return $walletToken;
    }

    /**
     * @param $type
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    protected function transactionType($type)
    {
        return $this->models['wallet_transaction_type']::where('handle', 'debit')->first();
    }
}
