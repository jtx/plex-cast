<?php

namespace App\Listeners;

use App\Events\TaggingSavedEvent;
use App\Models\MetadataItem;
use App\Models\Tag;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TaggingSavedListener
{
    /**
     * We can't use Eloquent to save data on the metadata_item table because Plex decided to make a custom
     * version of sqlite.  The only way you can do this is to run their own custom version. It's super lame,
     * but unless someone has more info on what they did and can write a new driver, we're stuck here.  Maybe
     * some day?
     * 
     * @param TaggingSavedEvent $event
     * @return void
     */
    public function handle(TaggingSavedEvent $event): void
    {
        $metadataItem = MetadataItem::findOrFail($event->tagging->metadata_item_id);

        $userFields = $metadataItem->user_fields;
        preg_match('/lockedFields=(.*)?/', $userFields, $matches);
        $fields = isset($matches[1]) && strlen($matches[1]) ? explode('|', $matches[1]): [];
        // This is redundant, the above line should make sure of it. However... I don't know EVERYTHING plex will throw at us
        if (!is_array($fields)) {
            $fields = [];
        }
        $fields[] = MetadataItem::CAST_LOCKED_FIELD;
        $fields = array_unique($fields);
        $fields = array_filter($fields, 'strlen');
        sort($fields);

        $metadataItem->user_fields = 'lockedFields=' . join('|', $fields);

        $tag = Tag::findOrFail($event->tagging->tag_id);

        $tagsStar = $metadataItem->tags_star;
        $stars = explode('|', $tagsStar);
        $index = $event->tagging->index ?? 0;
        array_splice($stars, $index, 0, $tag->tag);
        $stars = array_unique($stars);
        $stars = join('|', $stars);
        $metadataItem->tags_star = $stars;

        $process = Process::fromShellCommandline('rm /var/www/html/test.html');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();
    }
}
