<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Order;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use JDT\Pow\Entities\Wallet\WalletTokenType;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Redeemable;

/**
 * Class OrderItem.
 */
class OrderItem extends Model implements iOrderItemEntity, Redeemable
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_item';

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
        'order_id',
        'product_id',
        'product_shop_id',
        'quantity',
        'tokens_total',
        'tokens_spent',
        'original_unit_price',
        'adjusted_unit_price',
        'original_total_price',
        'adjusted_total_price',
        'vat_percentage',
        'original_vat_price',
        'adjusted_vat_price',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->adjusted_total_price;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        if($this->product_shop) {
            return $this->product_shop();
        }

        $models = \Config::get('pow.models');
        return $this->hasOne($models['product'], 'id', 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product_shop()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product_shop'], 'id', 'product_shop_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['order'], 'id', 'order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function form()
    {
        $models = \Config::get('pow.models');
        return $this->hasMany($models['order_item_form'], 'order_item_id', 'id')
            ->join('product_shop_order_form', 'order_item_form.product_shop_order_form_id', '=', 'product_shop_order_form.id');
    }

    public function addFormItem($productshopOrderFormId, $value)
    {
        $models = \Config::get('pow.models');
        $orderFormItem = $models['order_item_form']::create([
            'order_item_id' => $this->getId(),
            'product_shop_order_form_id' => $productshopOrderFormId,
            'value' => $value
        ]);

        return $orderFormItem;
    }

    /**
     * @return WalletTokenType
     */
    public function getTokenType()
    {
        return isset($this->product->token->type) ? $this->product->token->type : null;
    }

    /**
     * @return integer
     */
    public function getTokenValue()
    {
        return isset($this->product->token->tokens) ? $this->product->token->tokens : 0;
    }

    /**
     * @return bool
     */
    public function hasTokens()
    {
        return (bool) $this->tokens_total > 0;
    }

    /**
     * @return integer
     */
    public function getLinkerId()
    {
        return $this->order->id;
    }

    /**
     * @return string
     */
    public function getLinkerType()
    {
        return get_class($this->order);
    }

    public static function findEarliestRedeemableOrderItem() : iOrderItemEntity
    {
        //@todo check order status is complete as well 
        return OrderItem::whereRaw('tokens_spent < tokens_total')
            ->orderBy('created_at', 'asc')
            ->first();
    }
}
