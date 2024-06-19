<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class CurrentContractTest extends TestCase
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

  public function testCurrentContractFound(): void
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
        'returning_datetime' => null
      ],
      (object)[
        'id' => 2,
        'returning_datetime' => 1
      ]
    ];

    $this->contractRepository->method('findAll')
      ->willReturn($contracts);

    $functionResult = $searchClass->searchCurrentContracts(true);

    $this->assertNotEmpty($functionResult, 'Le tableau est vide');
    $this->assertEquals(1, $functionResult[0]->id, 'La configuration des contrats est incorrecte');
  }

  public function testCurrentContractNotFound(): void
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
        'returning_datetime' => 2
      ],
      (object)[
        'id' => 2,
        'returning_datetime' => 1
      ]
    ];

    $this->contractRepository->method('findAll')
      ->willReturn($contracts);

    $functionResult = $searchClass->searchCurrentContracts(true);

    $this->assertEmpty($functionResult, 'Le tableau est vide');
  }
}
