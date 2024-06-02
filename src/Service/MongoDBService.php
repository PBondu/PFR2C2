<?php 

namespace App\Service;

use MongoDB\Client;

class MongoDBService
{
    private $client;
    private $databases = [];

    public function __construct(string $uri, array $databaseNames)
    {
        $this->client = new Client($uri);
        foreach ($databaseNames as $name) {
            $this->databases[$name] = $this->client->selectDatabase($name);
        }
    }

    public function getDatabase(string $name)
    {
        if (!isset($this->databases[$name])) {
            throw new \InvalidArgumentException("Database $name not configured.");
        }

        return $this->databases[$name];
    }
    
    public function getNextSequence($name)
    {
        $counterCollection = $this->getDatabase('Customer')->counters;
        $counter = $counterCollection->findOneAndUpdate(
            ['_id' => $name],
            ['$inc' => ['seq' => 1]],
            ['returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER, 'upsert' => true]
        );
        return $counter->seq;
    }
}