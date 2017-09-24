<?php

namespace JDT\Pow\Entities\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;
use JDT\Pow\Http\Requests\SaveProductRequest;

/**
 * Class OrderForm.
 */
class OrderForm extends Model implements \JDT\Pow\Interfaces\Entities\Shop
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_shop_order_form';

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
        'type',
        'name',
        'hidden',
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
     * @var array
     */
    protected $validTypes = [
        'text', 'textarea', 'file', 'checkbox'
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
     * @param $type
     * @return bool
     */
    public function isValidType($type)
    {
        return in_array($type, $this->validTypes);
    }


}
