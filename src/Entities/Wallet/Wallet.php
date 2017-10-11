<?php

declare(strict_types=1);

namespace JDT\Pow\Entities\Wallet;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use \JDT\Pow\Interfaces\Entities\Wallet as iWalletEntity;
use \JDT\Pow\Interfaces\Entities\WalletTokenType as iWalletTokenTypeEntity;
use JDT\Pow\Pow;

/**
 * Class Wallet.
 */
class Wallet extends Model implements iWalletEntity
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallet';

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
        'overdraft',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function token(\JDT\Pow\Interfaces\Entities\WalletTokenType $type = null)
    {
        $models = \Config::get('pow.models');
        if($type) {
            return $this->hasOne($models['wallet_token'], 'wallet_id', 'id')
                ->where('wallet_token_type_id', $type->getId());
        }

        return $this->hasMany($models['wallet_token'], 'wallet_id', 'id');
    }

    public function tokenCount(\JDT\Pow\Interfaces\Entities\WalletTokenType $type = null)
    {
        $tokens = $this->token($type);
        if($tokens instanceof HasOne) {
            $token = $tokens->first();
            return $token ? $token->tokens : 0;
        }

        $count = 0;
        foreach($tokens->get() as $token) {
            $count += $token->tokens;
        }

        return $count;
    }

    /**
     * @return integer
     */
    public function getId()
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
     * @return float
     */
    public function getOverdraft()
    {
        return $this->overdraft;
    }

    /**
     * @param iWalletTokenTypeEntity $type
     * @return WalletToken
     */
    public function createToken(iWalletTokenTypeEntity $type) : WalletToken
    {
        $models = \Config::get('pow.models');
        return $models['wallet_token']::create([
            'wallet_id' => $this->getId(),
            'wallet_token_type_id' => $type->getId(),
            'tokens' => 0
        ]);
    }

    public function getOwner()
    {
        return Pow::getWalletOwner($this->id);
    }
}
