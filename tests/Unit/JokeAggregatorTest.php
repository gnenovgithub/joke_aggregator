<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use App\Services\JokeAggregator;
use App\Services\JokeProviderInterface;
use Exception;

class JokeAggregatorTest extends TestCase
{
    private $loggerMock;
    private $providerMock1;
    private $providerMock2;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        // Create provider mocks
        $this->providerMock1 = $this->createMock(JokeProviderInterface::class);
        $this->providerMock2 = $this->createMock(JokeProviderInterface::class);
    }

    /**
     * @param int $jokesLimit
     * @param array $jokes
     * @return array
     */
    private function helperGetJokes($jokesLimit, $jokes, $requestedNumberOfJokes = 2): array
    {
        $this->providerMock1->method('getJokesLimit')->willReturn($jokesLimit);
        $this->providerMock1->method('getJokes')->willReturn($jokes);
        $aggregator = new JokeAggregator([$this->providerMock1], $this->loggerMock);
        return $aggregator->getJokes($requestedNumberOfJokes);
    }

    public function testSuccessTwoReturnedJokes()
    {
        $jokes = $this->helperGetJokes(5,['Joke 1', 'Joke 2']);
        $this->assertCount(2, $jokes);
        $this->assertEquals(['Joke 1', 'Joke 2'], $jokes);
    }

    public function testErrorUnableToRetrieveJokesDoToEnvSettings()
    {
        $jokes = $this->helperGetJokes(0,['Joke 1', 'Joke 2']);
        $this->assertEquals(['error' => 'Unable to retrieve jokes from the available providers.'], $jokes);
    }

    public function testErrorUnableToRetrieveJokesDoToNoJokes()
    {
        $jokes = $this->helperGetJokes(10,[]);
        $this->assertEquals(['error' => 'Unable to retrieve jokes from the available providers.'], $jokes);
    }

    public function testErrorUnableToRetrieveTheRequestedNumberOfJokes()
    {
        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('Requested 10 jokes but could only return 2');
        $jokes = $this->helperGetJokes(5,['Joke 1', 'Joke 2'], 10);
        $this->assertCount(2, $jokes);
    }

    private function helperGetJokes2(): array
    {
        $this->providerMock1->method('getJokesLimit')->willReturn(5);
        $this->providerMock1->method('getJokes')->will($this->throwException(new Exception('Test Exception')));

        $this->loggerMock->expects($this->exactly(2))->method('error');
        $aggregator = new JokeAggregator([$this->providerMock1], $this->loggerMock);
        return $aggregator->getJokes(3);
    }

    public function testErrorHandlesProviderException()
    {
        $jokes = $this->helperGetJokes2();
        $this->assertArrayHasKey('error', $jokes);
        $this->assertEquals('Unable to retrieve jokes from the available providers.', $jokes['error']);
    }

    private function helperGetJokes3(): array
    {
        $this->providerMock1->method('getJokesLimit')->willReturn(10);
        $this->providerMock1->method('getJokes')->willReturn(['Joke 1', 'Joke 2']);

        $this->providerMock2->method('getJokesLimit')->willReturn(5);
        $this->providerMock2->method('getJokes')->willReturn(['Joke 3']);

        $aggregator = new JokeAggregator([$this->providerMock1, $this->providerMock2], $this->loggerMock);
        return $aggregator->getJokes(3);
    }

    public function testSuccessReturnedJokesFromMultipleProviders()
    {
        $jokes = $this->helperGetJokes3();
        $this->assertCount(3, $jokes);
        $this->assertEquals(['Joke 1', 'Joke 2', 'Joke 3'], $jokes);
    }
}
