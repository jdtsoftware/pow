<?php

namespace JDT\Pow\Entities\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;
use JDT\Pow\Http\Requests\SaveProductRequest;

/**
 * Class Product.
 */
class Product extends Model implements \JDT\Pow\Interfaces\Entities\Product
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product';

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
        'handle',
        'name',
        'description',
        'total_price',
        'created_user_id',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function boot()
    {
        $updateHandle = function ($model) {
            $model->handle = $model->toHandle($model->name);
        };

        static::creating($updateHandle);
        static::updating($updateHandle);
    }

    /**
     * @param $string
     * @return string
     * @todo move to model
     */
    public function toHandle($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($string)); // Removes special chars.
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function token()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product_token'], 'product_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function adjustment()
    {
        $models = \Config::get('pow.models');
        return $this->hasOne($models['product_adjustment_price'], 'product_id', 'id');
    }

    /**
     * @return integer
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param int $qty
     * @return float
     */
    public function getOriginalPrice($qty = 0) : float
    {
        $totalPrice = $this->total_price;
        if($qty > 0) {
            $totalPrice = $this->total_price * $qty;
        }

        return $totalPrice;
    }

    /**
     * @param int $qty
     * @return float
     */
    public function getAdjustedPrice($qty = 0) : float
    {
        return $this->adjustment ?
            $this->adjustment->getAdjustedPrice($this->getOriginalPrice($qty), $qty)
            : $this->getOriginalPrice($qty);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @param $totalPrice
     * @return float
     */
    public function getVATCharge($totalPrice = null) : float
    {
        $vat = \Config::get('pow.vat');
        if(empty($vat) || empty($totalPrice)) {
            return 0;
        }

        return ($vat / 100) * $totalPrice;
    }

    public function updateToken(SaveProductRequest $data)
    {
        if($this->token) {
            $this->token->delete();
        }

        $models = \Config::get('pow.models');

        $models['product_token']::create([
            'product_id' => $this->id,
            'wallet_token_type_id' => $data->wallet_token_type_id,
            'tokens' => $data->tokens
        ]);
    }

    public function updateAdjustment(SaveProductRequest $data)
    {
        if($this->adjustment) {
            $this->adjustment->delete();
        }

        $models = \Config::get('pow.models');

        $models['product_adjustment_price']::create([
            'product_id' => $this->id,
            'criteria' => $data->criteria,
            'adjustment' => $data->adjustment
        ]);
    }
}
