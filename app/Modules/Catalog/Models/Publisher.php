<?php

namespace Modules\Catalog\Models;

use Database\Factories\PublisherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'address', 'phone'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    protected static function newFactory(): PublisherFactory
    {
        return PublisherFactory::new();
    }
}
