# Joke Aggregator Library

The Joke Aggregator Library is a PHP package designed for aggregating science, tech, and programming jokes from multiple APIs. The library provides a unified interface for fetching jokes from multiple sources, allowing developers to easily integrate random jokes into their applications with minimal configuration. It also supports logging and error handling to ensure a smooth developer experience.

## Features

- Aggregates jokes from multiple sources.
- Configurable joke providers (choose one or multiple).
- Supports PSR-3 logging for API requests and responses.
- Handles API authentication and common errors.
- Tolerates provider errors (e.g., rate limits), aiming to return the maximum number of jokes requested.

## Installation

You can install the library via Composer:

```bash
composer require gnenovgithub/joke-aggregator
```
#### Note: This package is not yet published on Packagist.

## Requirements
- PHP 7.4 or higher
- Composer
- PSR-3 compatible logger (e.g., Stack)
- Guzzle HTTP client (for making API requests)

## Usage
### 1. Set Up API Credentials

Obtain an API key from RapidAPI for each joke provider you intend to use. This library currently supports the following joke APIs:
- World of Jokes
- JokeAPI
- Jokester

Set these keys in your .env file:
```env
WORLD_OF_JOKES_API_KEY=your_world_of_jokes_api_key
# Language preference for jokes
JOKE_API_LANGUAGE=en 
# Max jokes to fetch per provider
WORLD_OF_JOKES_LIMIT=0 # You can exclude provider by setting the limit to 0, but this will log a message
JOKE_API_JOKES_LIMIT=10 
JOKESTER_JOKES_LIMIT=1
```
### 2. Initialize the Joke Aggregator
The following example shows how to initialize the library, configure joke providers, and retrieve random jokes.
```
$client = new Client();
$providers = [
    new WorldOfJokesProvider($client),
    new JokeApiProvider($client),
    new JokesterProvider($client)
];

$aggregator =  new JokeAggregator($providers, Log::channel('stack'));
$jokes = $aggregator->getJokes(5);

if(!empty($jokes['error'])){
    return response($jokes['error'], 503);
}
return response()->json($jokes);
```
## Error Handling
- Authentication Errors: If authentication fails, the library will log the issue and return an error message.
- API Rate Limiting and Provider Errors: The library is tolerant to rate limits and other API errors. It will attempt to return as many jokes as possible within the specified limit, even if one or more providers fail.
## Logging
The library uses a PSR-3 compatible logger to log all API requests and responses. By default, any PSR-3 compliant logger can be passed to the aggregator, which helps in debugging and monitoring API usage.
## Testing
The library includes unit tests for each joke provider and the aggregator. To run tests, use the following command. After you have ```cd``` to your root directory of the app:

```bash
php artisan test tests/Unit
```
## Contributing
Contributions are welcome! To contribute:

1. Fork the repository.
2. Create a feature branch (```git checkout -b your_feature_branch```).
3. Commit your changes (```git commit -m 'Add some feature'```).
4. Push to the branch (```git push origin your_feature_branch```).
5. Open a Pull Request.
