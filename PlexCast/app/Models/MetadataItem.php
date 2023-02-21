<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class MetadataItem extends PlexCastModel
{
    const METADATA_TYPE_MOVIE = 1;  // We only want movies right now
    const CAST_LOCKED_FIELD = 19;  // The locked field in user_fields for actors is 19!

    /**
     * @var string
     */
    protected $table = 'metadata_items';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_fields',
        'tags_star',
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
        return $this->hasMany(Tagging::class, 'metadata_item_id', 'id');
    }
}
