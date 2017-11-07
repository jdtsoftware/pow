<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Order;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use JDT\Pow\Entities\Wallet\WalletTokenType;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;
use JDT\Pow\Interfaces\Redeemable;

/**
 * Class OrderItemRefund.
 */
class OrderItemRefund extends Model implements Redeemable
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_item_refund';

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
        'order_id',
        'order_item_id',
        'total_amount',
        'total_vat',
        'tokens_adjustment',
        'reason',
        'payment_gateway_reference',
        'payment_gateway_blob',
        'created_user_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function order()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['order'], 'id', 'order_id');
    }

    public function order_item()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['order_item'],'id', 'order_item_id');
    }

    /**
     * Return the true total including VAT
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        return $this->total_amount + $this->total_vat;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getTokenValue()
    {
        return $this->tokens_adjustment;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->order_item->getTokenType();
    }

    /**
     * @return integer
     */
    public function getLinkerId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLinkerType()
    {
        return get_class($this);
    }

}
