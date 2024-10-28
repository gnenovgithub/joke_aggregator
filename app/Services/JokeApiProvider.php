<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class JokeApiProvider implements JokeProviderInterface
{
    private Client $client;
    private string $apiKey;
    private string $apiUrl = 'https://jokeapi-v2.p.rapidapi.com/';
    private string $apiHost = 'jokeapi-v2.p.rapidapi.com';

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->apiKey = env('RAPID_API_KEY');
    }

    /*
     * Get random
     * @param int $numberOfJokes Jokes Limit
     * @return array Range of Random Ids
     */
    private function getRandomIds($numberOfJokes): array
    {
        try {
            $response = $this->client->get($this->apiUrl . 'info', [
                'headers' => [
                    'x-rapidapi-host' => $this->apiHost,
                    'x-rapidapi-key' => $this->apiKey
                ],
                'query' => ['format' => 'json']
            ]);
            $data = json_decode($response->getBody(), true);
            if(empty($data)){
                Log::error('The response body is empty!');
                return [];
            }

            if(!empty($data['jokes']['idRange'][env('JOKE_API_LANGUAGE')][1])){
                $maxJokes = $data['jokes']['idRange'][env('JOKE_API_LANGUAGE')][1];
                $range = range(1, $maxJokes);
                shuffle($range);
                $numberOfJokes = $this->getJokesLimit() > $numberOfJokes ? $numberOfJokes : $numberOfJokes - 1;
                return array_slice($range, 0, $numberOfJokes);
            }else{
                Log::warning('We were not able to find jokes for the following language: ' . env('JOKE_API_LANGUAGE'));
            }
        } catch (Exception $e) {
            Log::error('Error fetching jokes from JokeApiProvider: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Get Joke by id
     *
     * @param int $jokeId
     * @return string The joke or empty string
     */
    private function getJokeById($jokeId): string
    {
        try {
            $response = $this->client->get($this->apiUrl . 'joke/Any', [
                'headers' => [
                    'x-rapidapi-host' => $this->apiHost,
                    'x-rapidapi-key' => $this->apiKey
                ],
                'query' => ['format' => 'json', 'idRange' => $jokeId]
            ]);

            $data = json_decode($response->getBody(), true);
            if(!empty($data['joke'])){
                return $data['joke'];
            }elseif (!empty($data['setup'])){
                return $data['setup'] . ' ' . $data['delivery'];
            }
        } catch (Exception $e) {
            Log::error('Error fetching jokes from JokeApiProvider: ' . $e->getMessage());
        }
        return '';
    }

    /**
     * Fetch up to $numberOfJokes jokes from the provider.
     *
     * @param int $numberOfJokes
     * @return array Array of jokes in normalized format ['text' => '...', 'source' => '...'].
     */
    public function getJokes(int $numberOfJokes): array
    {
        /*
         * We can't get more than 1 joke at a time, so we need to loop :(
         */
        $jokes = array();
        foreach ($this->getRandomIds($numberOfJokes) as $jokeId) {
            $joke = $this->getJokeById($jokeId);
            if (empty($joke)){
                return $jokes;
            }
            $jokes[] = [
                'text' => $joke,
                'source' => 'Joke Api 2',
            ];
        }
        return $jokes;
    }

    /*
     * @return int Jokes Limit
     */
    public function getJokesLimit(): int
    {
        return env('JOKE_API_JOKES_LIMIT');
    }
}
