<?php

namespace App\Services;

interface JokeProviderInterface
{
    /**
     * Fetch up to $numberOfJokes jokes from the provider.
     *
     * @param int $numberOfJokes
     * @return array Array of jokes in normalized format ['text' => '...', 'source' => '...'].
     */
    public function getJokes(int $numberOfJokes): array;

    /**
     * Fetch up the provider jokes limit per request, configured on the env file
     *
     * @return int Provider Jokes limit
     */
    public function getJokesLimit(): int;

}
