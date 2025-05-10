<?php

namespace PinIndia\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class District extends Model
{
    public $timestamps = false;
    protected $fillable = ['state_id', 'name'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_districts' : 'districts';
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function pincodes(): HasMany
    {
        return $this->hasMany(Pincode::class);
    }

    public function divisions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Division::class,
            PostOffice::class,
            'district_id',     // Foreign key on PostOffice table
            'id',              // Local key on Division table
            'id',              // Local key on District table
            'division_id'      // Foreign key on PostOffice table
        )->distinct();
    }
}
