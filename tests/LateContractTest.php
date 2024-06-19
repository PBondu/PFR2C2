<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class LateContractTest extends TestCase
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

  public function testLateContractFound(): void
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
        'locend_datetime' => 1,
        'returning_datetime' => 2
      ],
      (object)[
        'id' => 2,
        'locend_datetime' => 2,
        'returning_datetime' => 1
      ],
    ];

    $this->contractRepository->method('findAll')
      ->willReturn($contracts);

    $functionResult = $searchClass->searchLateContracts(true);

    $this->assertNotEmpty($functionResult, 'Le tableau est vide');
    $this->assertCount(1, $functionResult, 'La configuration des contrats est incorrecte');
  }

  public function testLateContractNotFound(): void
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
      'locend_datetime' => 2,
      'returning_datetime' => 1
    ],
    (object)[
      'id' => 2,
      'locend_datetime' => 2,
      'returning_datetime' => 1
    ],
  ];

  $this->contractRepository->method('findAll')
    ->willReturn($contracts);

  $functionResult = $searchClass->searchLateContracts(true);

  $this->assertEmpty($functionResult, 'Le tableau est vide');
   }
}
