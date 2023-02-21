<?php

namespace App\Events;

use App\Models\Tagging;
use Illuminate\Queue\SerializesModels;

class TaggingSavedEvent
{
    use SerializesModels;

    /**
     * @param Tagging $tagging
     */
    public function __construct(public Tagging $tagging) {}
}
