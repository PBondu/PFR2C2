<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class LatenessBetweenDatesTest extends TestCase
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

  public function testLatenessBetweenDatesFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $beginDateLate = "2000-01-01";
    $endDateLate = "2030-01-01";

    $contracts = [
      (object)[
        'id' => 1,
        'locend_datetime' => new \DateTime('2022-01-01'),
        'returning_datetime' => new \DateTime('2024-01-01')
      ],
      (object)[
        'id' => 2,
        'locend_datetime' => new \DateTime('2022-01-01'),
        'returning_datetime' => new \DateTime('2022-01-01')
      ],
    ];

    $this->contractRepository->method('findAll')
      ->willReturn($contracts);

    $functionResult = $searchClass->calculateLatenessBetweenDates($beginDateLate, $endDateLate);

    $this->assertNotEmpty($functionResult, 'Le tableau est vide');
    $this->assertEquals(1, $functionResult, 'La configuration des contrats est incorrecte');
  }

  public function testLatenessBetweenDatesNotFound(): void
  {
    $searchClass = new SearchController(
      $this->mongoDBService,
      $this->contractRepository,
      $this->billingRepository,
      $this->userRequestProvider
    );

    $beginDateLate = "2000-01-01";
    $endDateLate = "2030-01-01";

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

  $functionResult = $searchClass->calculateLatenessBetweenDates($beginDateLate, $endDateLate);

  $this->assertEmpty($functionResult, 'Le tableau est vide');
   }
}
