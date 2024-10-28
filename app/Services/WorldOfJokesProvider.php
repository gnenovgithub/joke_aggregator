<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WorldOfJokesProvider implements JokeProviderInterface
{
    private Client $client;
    private string $apiKey;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->apiKey = env('RAPID_API_KEY');
    }

    /*
     * @param $numberOfJokes
     */
    public function getJokes(int $numberOfJokes): array
    {
        /*
         * @toDo Error Message The API is unreachable, please contact the API provider
         */

        try {
            $response = $this->client->get('https://world-of-jokes1.p.rapidapi.com/v1/jokes', [
                'headers' => [
                    'x-rapidapi-key' => $this->apiKey,
                ],
                'query' => ['limit' => $numberOfJokes]
            ]);

            $data = json_decode($response->getBody(), true);
            $jokes = [];

            /*
            foreach ($data['jokes'] as $joke) {
                $jokes[] = [
                    'text' => $joke['joke'],
                    'source' => 'WorldOfJokes',
                ];
            }
            */

            return $jokes;
        } catch (Exception $e) {
            Log::error('Error fetching jokes from WorldOfJokesProvider: ' . $e->getMessage());
            return [];
        }
    }

    /*
     * @return int Jokes Limit
     */
    public function getJokesLimit(): int
    {
        return env('WORLD_OF_JOKES_LIMIT');
    }
}
