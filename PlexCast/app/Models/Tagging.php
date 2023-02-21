<?php

namespace App\Models;

use App\Events\TaggingSavedEvent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Tagging extends PlexCastModel
{
    use Notifiable;

    /**
     * @var string
     */
    protected $table = 'taggings';

    /**
     * @var string[]
     */
    protected $fillable = [
        'metadata_item_id',
        'tag_id',
        'index'
    ];

    /**
     * @var string[]
     */
    protected $dispatchesEvents = [
        'saved' => TaggingSavedEvent::class,
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function metadataItems(): BelongsTo
    {
        return $this->belongsTo(MetadataItem::class, 'metadata_item_id', 'id');
    }

    public function tags(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }
}
