<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class JokesterProvider implements JokeProviderInterface
{
    private Client $client;
    private string $apiKey;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->apiKey = env('RAPID_API_KEY');
    }

    /**
     * Get Joke from Jokester
     *
     */
    private function getSingleJoke(): string
    {
        try {
            $response = $this->client->get('https://jokester.p.rapidapi.com/jokes', [
                'headers' => [
                    'x-rapidapi-host' => 'jokester.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            if(!empty($data[0]['joke'])){
                return $data[0]['joke'];
            }else{
                Log::error('No jokes were available at the moment.');
            }

        } catch (Exception $e) {
            Log::error('Error fetching jokes from JokesterProvider: ' . $e->getMessage());
        }
        return '';
    }

    /*
     * @param $numberOfJokes
     */
    public function getJokes(int $numberOfJokes): array
    {
        $jokes = array();
        //It seems that we will need to loop to get more than 1 joke
        foreach (range(1, min($numberOfJokes, $this->getJokesLimit())) as $ignored) {
            $joke = $this->getSingleJoke();
            if(empty($joke)){
                return $jokes;
            }
            $jokes[] = [ 'text' => $joke, 'source' => 'Jokester' ];
        }
        return $jokes;
    }

    /*
     * @return int Jokes Limit
     */
    public function getJokesLimit(): int
    {
        return env('JOKESTER_JOKES_LIMIT');
    }

}
