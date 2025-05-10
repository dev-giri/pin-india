<?php

namespace PinIndia\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circle extends Model
{
    public $timestamps = false;
    protected $fillable = ['name'];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_circles' : 'circles';
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}
