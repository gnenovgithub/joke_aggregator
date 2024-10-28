<?php

namespace App\Http\Controllers;

use App\Services\JokeAggregator;

class JokeController extends Controller
{
    private JokeAggregator $aggregator;

    public function __construct(JokeAggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /*
     * @param int $numberOfJokes Number of Jokes
     */
    public function index($numberOfJokes): object
    {
        $jokes = $this->aggregator->getJokes($numberOfJokes);
        if(!empty($jokes['error'])){
            return response($jokes['error'], 503);
        }
        return response()->json($jokes);
    }
}
