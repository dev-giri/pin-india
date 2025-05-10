<?php

namespace PinIndia\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    public $timestamps = false;
    protected $fillable = ['region_id', 'name'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_divisions' : 'divisions';
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function pincodes(): HasMany
    {
        return $this->hasMany(Pincode::class);
    }
}
