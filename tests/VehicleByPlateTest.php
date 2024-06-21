<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;
use MongoDB\Collection;

class VehicleByPlateTest extends TestCase
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

  public function testVehicleByPlateFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $vehicles = [
      (object)[
        'licence_plate' => "abc",
      ],
    ];

    $this->mongoDBService->method('getDatabase')
    ->with('Vehicle')
    ->willReturn((object)['vehicles' => new class($vehicles) {
        public $vehicles;
        public function __construct($vehicles) {
            $this->vehicles = $vehicles;
        }
        public function findOne() {
            return new class($this->vehicles) {
                public $vehicles;
                public function __construct($vehicles) {
                    $this->vehicles = $vehicles;
                }
            };
        }
    }]);

    $functionResult = $searchClass->searchVehicleByPlate("abc");

    $this->assertNotEmpty($functionResult, 'Le tableau est vide');
    $this->assertEquals("abc", $functionResult[0]->{'vehicles'}[0]->licence_plate, 'La configuration de du service est incorrecte');
  }

  public function testVehicleByPlateNotFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $this->mongoDBService->method('getDatabase')
    ->with('Vehicle')
    ->willReturn((object)['vehicles' => $this->vehiclesCollection]);

// Mock de findOne pour retourner null en fonction du critère
$this->vehiclesCollection->method('findOne')
    ->will($this->returnValueMap([
        [['licence_plate' => 'abc'], null, null],
    ]));

    $functionResult = $searchClass->searchVehicleByPlate("def");
    

    $this->assertEmpty($functionResult, 'Le tableau n\'est pas vide');  
   }
}
