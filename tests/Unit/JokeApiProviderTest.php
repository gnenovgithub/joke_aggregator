<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use App\Services\JokeApiProvider;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

class JokeApiProviderTest extends TestCase
{
    private $clientMock;
    private $jokeApiProvider;

    protected function setUp(): void
    {
        // Mock environment variables
        putenv('RAPID_API_KEY=test_api_key');
        putenv('JOKE_API_LANGUAGE=en');
        putenv('JOKE_API_JOKES_LIMIT=5');

        // Mock the Client dependency
        $this->clientMock = $this->createMock(Client::class);

        // Inject the mock into the JokeApiProvider
        $this->jokeApiProvider = new JokeApiProvider($this->clientMock);
    }

    private function helperGetRandomIds($responseBody)
    {
        $this->clientMock->method('get')->willReturn(new Response(200, [], json_encode($responseBody)));
        $method = new ReflectionMethod(JokeApiProvider::class, 'getRandomIds');

        return $method->invoke($this->jokeApiProvider, 3);
    }

    /*
     * Test for: JokeApiProvider::getRandomIds()
     */
    public function testSuccessGetRandomJokeIds()
    {
        $ids = $this->helperGetRandomIds([
            'jokes' => [
                'idRange' => [
                    'en' => [0, 300]
                ]
            ]
        ]);
        $this->assertIsArray($ids);
        $this->assertCount(3, $ids);
    }

    /*
     * Test for: JokeApiProvider::getRandomIds()
     */
    public function testErrorHandlesEmptyResponseWhenSelectingTheInfo()
    {
        Log::shouldReceive('error')->once()->with('The response body is empty!');
        $ids = $this->helperGetRandomIds([]);
        $this->assertIsArray($ids);
        $this->assertCount(0, $ids);
    }

    private function helperNoJokesForThisLanguage($idRange)
    {
        Log::shouldReceive('error')->once()->withAnyArgs();
        return $this->helperGetRandomIds([
            'jokes' => [
                'idRange' => $idRange
            ]
        ]);
    }

    /*
     * Test for: JokeApiProvider::getRandomIds()
     */
    public function testErrorLanguageMissingFromTheIdRange()
    {
        $ids = $this->helperNoJokesForThisLanguage([ 'enx' => [0, 10] ]);
        $this->assertIsArray($ids);
        $this->assertEmpty($ids);
    }

    /*
     * Test for: JokeApiProvider::getRandomIds()
     */
    public function testErrorThereAreZeroJokesForThisLanguage()
    {
        $ids = $this->helperNoJokesForThisLanguage([ 'en' => [0, 0] ]);
        $this->assertIsArray($ids);
        $this->assertEmpty($ids);
    }

    private function helperGetJokeById($responseBody)
    {
        $this->clientMock->method('get')->willReturn(new Response(200, [], json_encode($responseBody)));
        $method = new ReflectionMethod(JokeApiProvider::class, 'getJokeById');
        return $method->invoke($this->jokeApiProvider, 1);
    }

    /*
     * Test for: JokeApiProvider::getJokeById()
     */
    public function testSuccessReturnedDataJoke()
    {
        $jokeText = $this->helperGetJokeById(['joke' => 'This is a funny joke.']);
        $this->assertEquals('This is a funny joke.', $jokeText);
    }

    /*
     * Test for: JokeApiProvider::getJokeById()
     */
    public function testSuccessReturnsCombinedJokeText()
    {
        $jokeText = $this->helperGetJokeById(['setup' => 'Joke part 1.', 'delivery' => 'Joke art 2']);
        $this->assertEquals('Joke part 1. Joke art 2', $jokeText);
    }

    private function helperGetJokes($jokeData)
    {
        $this->clientMock->method('get')->will($this->onConsecutiveCalls(
            new Response(200, [], json_encode([
                'jokes' => ['idRange' => ['en' => [1, 300]]]
            ])),
            new Response(200, [], json_encode($jokeData)),
            new Response(200, [], json_encode(['joke' => 'Joke 2']))
        ));

        return $this->jokeApiProvider->getJokes(2);
    }

    /*
     * Test for: JokeApiProvider::getJokes()
     */
    public function testSuccessReturnedTwoJokes()
    {
        $jokes = $this->helperGetJokes(['joke' => 'Joke 1']);
        $this->assertCount(2, $jokes);
        $this->assertEquals('Joke 1', $jokes[0]['text']);
        $this->assertEquals('Joke Api 2', $jokes[0]['source']);
        $this->assertEquals('Joke 2', $jokes[1]['text']);
        $this->assertEquals('Joke Api 2', $jokes[1]['source']);
    }

    /*
     * Test for: JokeApiProvider::getJokes()
     */
    public function testErrorHandlesEmptyJokeResponse()
    {
        $jokes = $this->helperGetJokes([]);
        $this->assertEmpty($jokes);
    }

    /*
     * Test for: JokeApiProvider::getJokesLimit()
     */
    public function testSuccessReturnsEnvironmentVariableValue()
    {
        $limit = $this->jokeApiProvider->getJokesLimit();
        $this->assertEquals(5, $limit);
    }
}
