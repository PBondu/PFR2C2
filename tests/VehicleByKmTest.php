<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;
use MongoDB\Collection;

class VehicleByKmTest extends TestCase
{
  private $contractRepository;
  private $billingRepository;
  private $mongoDBService;
  private $userRequestProvider;
  private $vehiclesCollection;

  protected function setUp(): void
  {
    $this->contractRepository = $this->createMock(ContractRepository::class);
    $this->billingRepository = $this->createMock(BillingRepository::class);
    $this->mongoDBService = $this->createMock(MongoDBService::class);
    $this->userRequestProvider = $this->createMock(UserRequestProvider::class);
    $this->vehiclesCollection = $this->createMock(Collection::class);
  }

  public function testVehicleByKmFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $vehicles = [
      (object)[
        'km' => "20",
      ],
      (object)[
        'km' => "5",
      ],
    ];

    $this->mongoDBService->method('getDatabase')
      ->with('Vehicle')
      ->willReturn((object)['vehicles' => new class($vehicles)
      {
        private $vehicles;
        public function __construct($vehicles)
        {
          $this->vehicles = $vehicles;
        }
        public function find()
        {
          return new class($this->vehicles)
          {
            private $vehicles;
            public function __construct($vehicles)
            {
              $this->vehicles = $vehicles;
            }
            public function toArray()
            {
              return $this->vehicles;
            }
          };
        }
      }]);

    $functionResult = $searchClass->searchVehicleByKm("10");

    $this->assertNotEmpty($functionResult, 'Le tableau est vide');
    $this->assertCount(1, $functionResult);
  }

  public function testVehicleByKmNotFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $vehicles = [
      (object)[
        'km' => "5",
      ],
    ];

    $this->mongoDBService->method('getDatabase')
      ->with('Vehicle')
      ->willReturn((object)['vehicles' => new class($vehicles)
      {
        private $vehicles;
        public function __construct($vehicles)
        {
          $this->vehicles = $vehicles;
        }
        public function find()
        {
          return new class($this->vehicles)
          {
            private $vehicles;
            public function __construct($vehicles)
            {
              $this->vehicles = $vehicles;
            }
            public function toArray()
            {
              return $this->vehicles;
            }
          };
        }
      }]);

    $functionResult = $searchClass->searchVehicleByKm("10");

    $this->assertEmpty($functionResult, 'Le tableau est vide');
  }
}
