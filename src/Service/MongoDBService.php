<?php 

namespace App\Service;

use MongoDB\Client;

class MongoDBService
{
    private $client;
    private $database;

    public function __construct(string $uri, string $databaseName)
    {
        $this->client = new Client($uri);
        $this->database = $this->client->selectDatabase($databaseName);
    }

    public function getDatabase()
    {
        return $this->database;
    }
}
