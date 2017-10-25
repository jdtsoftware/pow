<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Order;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;

/**
 * Class OrderStatus.
 */
class OrderStatus extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_status';

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
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $with = [
    ];

    /**
     * @param $handle
     * @return mixed
     */
    public static function handleToId($handle)
    {
        $status = self::where('handle', $handle)->first();
        return isset($status->id) ? $status->id : null;
    }

}
