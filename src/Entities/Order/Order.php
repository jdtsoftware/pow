<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Order;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use JDT\Pow\Interfaces\IdentifiableId;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Entities\Product as iProductEntity;
use JDT\Pow\Interfaces\Entities\Shop as iProductShopEntity;
use JDT\Pow\Interfaces\Entities\Order as iOrderEntity;

/**
 * Class Order.
 */
class Order extends Model implements iOrderEntity, IdentifiableId
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'wallet_id',
        'order_status_id',
        'address_id',
        'address_type',
        'payment_gateway_id',
        'original_total_price',
        'adjusted_total_price',
        'original_vat_price',
        'adjusted_vat_price',
        'po_number',
        'payment_gateway_reference',
        'payment_gateway_blob',
        'created_user_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @return integer
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return bool
     */
    public function hasSpentTokens()
    {
        foreach($this->items as $item) {
            if($item->tokens_spent > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function tokensSpent()
    {
        $tokens = 0;
        foreach($this->items as $item) {
            $tokens += $item->tokens_spent;
        }

        return $tokens;
    }

    /**
     * @return int
     */
    public function tokensTotal()
    {
        $tokens = 0;
        foreach($this->items as $item) {
            $tokens += $item->tokens_total;
        }

        return $tokens;
    }

    /**
     * @return float|int
     */
    public function getOriginalVATCharge() : float
    {
        return (float) $this->original_vat_price;
    }

    /**
     * @return float
     */
    public function getAdjustedVATCharge() : float
    {
        return (float) $this->adjusted_vat_price;
    }

    /**
     * return float
     */
    public function getOriginalPrice() : float
    {
        return (float) $this->original_total_price;
    }

    /**
     * @return float
     */
    public function getAdjustedPrice() : float
    {
        return (float) $this->adjusted_total_price;
    }

    /**
     * @return float
     */
    public function getSubTotalPrice()
    {
        return $this->getOriginalPrice() - $this->getOriginalVATCharge();
    }

    /**
     * @return float
     */
    public function getDiscountPrice()
    {
        $totalDiscount = ($this->original_total_price - $this->original_vat_price) -
            ($this->adjusted_total_price - $this->adjusted_vat_price);

        return $totalDiscount > 0 ?  (-1 * abs($totalDiscount)) : null;
    }

    /**
     * @param iProductEntity $product
     * @param iProductShopEntity $productShop
     * @param int $qty
     * @param int|null $vatPercentage
     *
     * @return iOrderItemEntity
     */
    public function addLineItem(iProductEntity $product, iProductShopEntity $productShop = null, int $qty = 1, $vatPercentage = null) : iOrderItemEntity
    {
        $qty = (int) $qty;
        if($qty <= 0) { //lets be safe.
            $qty = 1;
        }

        $models = \Config::get('pow.models');
        $orderItem = $models['order_item']::create([
            'order_id' => $this->getId(),
            'product_id' => $product->getId(),
            'product_shop_id' => isset($productShop) ? $productShop->getId() : null,
            'tokens_total' => isset($product->token->tokens) ? ($product->token->tokens * $qty) : 0,
            'tokens_spent' => 0,
            'quantity' => $qty,
            'original_unit_price' => $product->getOriginalPrice(),
            'adjusted_unit_price' => $product->getAdjustedPrice(),
            'original_total_price' => $product->getOriginalPrice($qty),
            'adjusted_total_price' => $product->getAdjustedPrice($qty),
            'vat_percentage' => $vatPercentage,
            'original_vat_price' => $product->getVatCharge($product->getOriginalPrice($qty), $vatPercentage),
            'adjusted_vat_price' => $product->getVatCharge($product->getAdjustedPrice($qty), $vatPercentage),
        ]);

        return $orderItem;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        $models = \Config::get('pow.models');
        return $this->hasMany($models['order_item'], 'order_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function status()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['order_status'], 'id', 'order_status_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function wallet()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['wallet'], 'id', 'wallet_id');
    }

    /**
     * @return bool
     */
    public function isComplete() : bool
    {
        return $this->order_status_id === OrderStatus::handleToId('complete');
    }

    /**
     * @param bool $includePartially
     * @return bool
     */
    public function isRefunded($includePartially = true) : bool
    {
        return $this->order_status_id === OrderStatus::handleToId('refuned')
            || $includePartially === true
                && $this->order_status_id === OrderStatus::handleToId('partial_refund');
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->order_status_id !== OrderStatus::handleToId('pending_approval');
    }

    /**
     * @return mixed
     */
    public function creator()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['user'], 'id', 'created_user_id');
    }

    /**
     * @return MorphTo
     */
    public function address() : MorphTo
    {
        return $this->morphTo('address');
    }

}
