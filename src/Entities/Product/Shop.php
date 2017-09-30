<?php

namespace JDT\Pow\Entities\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;
use JDT\Pow\Http\Requests\SaveProductRequest;

/**
 * Class Shop.
 */
class Shop extends Model implements \JDT\Pow\Interfaces\Entities\Shop
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_shop';

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
        'product_id',
        'order_approval_required',
        'quantity',
        'quantity_lock',
        'name',
        'description',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var array
     */
    protected $with = [
        'product'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product'], 'id', 'product_id');
    }

    /**
     * @return integer
     */
    public function getId() : int
    {
        return $this->id;
    }

}
