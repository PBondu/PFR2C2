<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class AverageLatenessPerVehicleTest extends TestCase
{
  private $contractRepository;
  private $billingRepository;
  private $mongoDBService;
  private $userRequestProvider;

  protected function setUp(): void
  {
    $this->contractRepository = $this->createMock(ContractRepository::class);
    $this->billingRepository = $this->createMock(BillingRepository::class);
    $this->mongoDBService = $this->createMock(MongoDBService::class);
    $this->userRequestProvider = $this->createMock(UserRequestProvider::class);
  }

  public function testAverageLatenessPerVehicleFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $contracts = [
      (object)[
        'vehicle_uid' => 1,
        'locend_datetime' => new \DateTime('2022-01-01'),
        'returning_datetime' => new \DateTime('2022-01-02')
      ],
      (object)[
        'vehicle_uid' => 1,
        'locend_datetime' => new \DateTime('2022-01-01'),
        'returning_datetime' => new \DateTime('2022-01-04')
      ],
      (object)[
        'vehicle_uid' => 2,
        'locend_datetime' => new \DateTime('2022-01-01'),
        'returning_datetime' => new \DateTime('2022-01-01')
      ],
    ];

    $this->contractRepository->method('findAll')
      ->willReturn($contracts);

    $functionResult = $searchClass->calculateAverageLatenessPerVehicle(true);
    
    $this->assertNotEmpty($functionResult, 'Le tableau est vide');
    $this->assertCount(1, $functionResult, 'La configuration des contrats est incorrecte');
    $this->assertEquals("2 jours et 0 heures", $functionResult[1]);
  }

  public function testAverageLatenessPerVehicleNotFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $contracts = [
      (object)[
        'id' => 1,
        'locend_datetime' => new \DateTime('2022-01-01'),
        'returning_datetime' => new \DateTime('2022-01-01')
      ],
      (object)[
        'id' => 2,
        'locend_datetime' => new \DateTime('2022-01-01'),
        'returning_datetime' => new \DateTime('2022-01-01')
      ],
    ];

  $this->contractRepository->method('findAll')
    ->willReturn($contracts);

  $functionResult = $searchClass->calculateAverageLatenessPerVehicle(false);

  $this->assertEmpty($functionResult, 'Le tableau est vide');
   }
}
