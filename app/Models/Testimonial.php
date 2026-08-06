<?php

namespace App\Models;

use App\Support\AssetUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    public const CREATED_AT = 'createdAt';
    public const UPDATED_AT = 'updatedAt';

    protected $table = 'testimonial';

    protected $fillable = ['name', 'role', 'quote', 'imageUrl', 'published', 'sortOrder'];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'sortOrder' => 'integer',
            'createdAt' => 'datetime',
            'updatedAt' => 'datetime',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => AssetUrl::relative($value),
            set: fn (?string $value) => AssetUrl::relative($value),
        );
    }
}
