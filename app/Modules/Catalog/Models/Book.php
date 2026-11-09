<?php

namespace Modules\Catalog\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Catalog\ValueObjects\Isbn;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'isbn',
        'author_id',
        'publisher_id',
        'description',
        'price',
        'stock',
        'low_stock_threshold',
        'published_year',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('added_at');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock', '>', 0)->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $builder) use ($keyword) {
            $builder->where('title', 'like', "%{$keyword}%")
                ->orWhere('isbn', 'like', "%{$keyword}%");
        });
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        $category = Category::with('children')->find($categoryId);

        if (! $category) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('categories', function (Builder $builder) use ($category) {
            $builder->whereIn('categories.id', $category->descendantIds());
        });
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'low_stock_threshold');
    }

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    public function getIsbnObjectAttribute(): Isbn
    {
        return new Isbn($this->isbn);
    }

    public function setIsbnAttribute(string $value): void
    {
        $this->attributes['isbn'] = (string) new Isbn($value);
    }

    protected static function newFactory(): BookFactory
    {
        return BookFactory::new();
    }
}
