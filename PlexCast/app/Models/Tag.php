<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends PlexCastModel
{
    use HasFactory;

    const TAG_TYPE = 6;

    /**
     * @var string
     */
    protected $table = 'tags';

    /**
     * @var string[]
     */
    protected $fillable = [
        'metadata_item_id',
        'tag',
        'tag_type',
        'user_thumb_url',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return HasMany
     */
    public function tagging(): HasMany
    {
        return $this->hasMany(Tagging::class, 'tag_id', 'id');
    }
}
