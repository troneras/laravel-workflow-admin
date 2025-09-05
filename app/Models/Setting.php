<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'description', 'is_public'];
    
    protected $casts = [
        'is_public' => 'boolean',
    ];
    
    public function getDecodedValueAttribute()
    {
        return match ($this->type) {
            'integer' => (int) json_decode($this->value),
            'boolean' => (bool) json_decode($this->value),
            'array', 'json' => json_decode($this->value, true),
            default => json_decode($this->value),
        };
    }
    
    public function setDecodedValueAttribute($value)
    {
        $this->attributes['value'] = json_encode($value);
    }
    
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
    
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}