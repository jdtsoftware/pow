<?php

declare(strict_types=1);

namespace App\Modules\Address\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Services\Geocoder\Geocoder;

/**
 * Class Wallet.
 */
class WalletToken extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallet_token';

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
        'wallet_id',
        'wallet_token_type_id',
        'tokens',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $with = [
    ];

}
