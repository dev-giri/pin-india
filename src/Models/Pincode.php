<?php

namespace PinIndia\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pincode extends Model
{
    public $timestamps = false;
    protected $fillable = ['pincode', 'district_id', 'division_id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_pincodes' : 'pincodes';
    }

    public function district(): BelongsTo {
        return $this->belongsTo(District::class);
    }

    public function postOffices(): HasMany
    {   
        return $this->hasMany(PostOffice::class);
    }

    public function division(): BelongsTo {
        return $this->belongsTo(Division::class);
    }
}
