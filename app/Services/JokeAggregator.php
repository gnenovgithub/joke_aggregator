<?php

namespace App\Services;

use Exception;
use Psr\Log\LoggerInterface;

class JokeAggregator
{
    private array $providers;
    private LoggerInterface $logger;

    public function __construct(array $providers, LoggerInterface $logger)
    {
        $this->providers = $providers;
        $this->logger = $logger;
    }

    /**
     * Fetch up to $n random jokes from the configured providers.
     *
     * @param int $numberOfJokes The number of jokes to return.
     * @return array The normalized jokes array.
     */
    public function getJokes(int $numberOfJokes): array
    {
        $jokes = [];
        foreach ($this->providers as $provider) {
            $this->logger->info('Fetching jokes from provider: ' . get_class($provider));

            try {
                $providerLimit = $provider->getJokesLimit();
                if($providerLimit > 0){
                    $providerJokes = $provider->getJokes(min($numberOfJokes, $providerLimit));
                    $jokes = array_merge($jokes, $providerJokes);
                    if(count($jokes) >= $numberOfJokes){
                        break;
                    }
                }else{
                    $this->logger->error('The Joke Limit for ' . get_class($provider) . ' is not over 0, on the env file.');
                }

            } catch (Exception $e) {
                $this->logger->error('Failed to fetch jokes from provider: ' . get_class($provider),
                    ['exception' => $e]);
            }
        }
        if(count($jokes)){
            if (count($jokes) < $numberOfJokes) {
                $this->logger->warning('Requested ' . $numberOfJokes . ' jokes but could only return ' . count($jokes));
            }
            return $jokes;
        }
        $errorMessage = 'Unable to retrieve jokes from the available providers.';
        $this->logger->error($errorMessage . ' Please ensure the environment configuration is set up correctly.');
        return ['error' => $errorMessage];
    }
}
