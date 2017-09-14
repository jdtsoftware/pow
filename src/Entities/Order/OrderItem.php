<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Order;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use JDT\Pow\Interfaces\Entities\OrderItem as iOrderItemEntity;

/**
 * Class OrderItem.
 */
class OrderItem extends Model implements iOrderItemEntity
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
        'quantity',
        'tokens_total',
        'tokens_spent',
        'original_unit_price',
        'adjusted_unit_price',
        'original_total_price',
        'adjusted_total_price',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getId()
    {
        return $this->id;
    }
}
