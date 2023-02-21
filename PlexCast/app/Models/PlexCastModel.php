<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class PlexCastModel extends Model
{
    /**
     * Save the model to the database.
     * This is REALLY LAME.  Plex locks the database. A lot. We're just gonna try and brute force writes
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        $retryCount = config('database.connections.sqlite.retry_count');

        for ($i = 0; $i < $retryCount; $i++) {
            try {
                return parent::save($options);
            } catch (QueryException $e) {
                // Probably locked. Catching and doing nothing so we can keep trying

                if ($i == $retryCount - 1) {    // Welp... Hit the limit. Throw it
                    throw $e;
                }
            }
        }
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public static function create(array $attributes = []): Model
    {
        $retryCount = config('database.connections.sqlite.retry_count');

        for ($i = 0; $i < $retryCount; $i++) {
            try {
                return static::query()->create($attributes);
            } catch (QueryException $e) {
                // Probably locked. Catching and doing nothing so we can keep trying

                if ($i == $retryCount - 1) {    // Welp... Hit the limit. Throw it
                    throw $e;
                }
            }
        }
    }
}
