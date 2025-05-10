<?php

namespace PinIndia\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    public $timestamps = false;
    protected $fillable = ['circle_id', 'name'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_regions' : 'regions';
    }

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }
}