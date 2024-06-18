<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class ContractByContractIdTest extends TestCase
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

    public function testContractFoundById(): void
    {
        $searchClass = new SearchController(
            $this->mongoDBService,
            $this->contractRepository,
            $this->billingRepository,
            $this->userRequestProvider
        );

        $contractId = 1;
        $contractFound = [(object)['id' => $contractId]];

        $this->contractRepository->method('findBy')
            ->with(['id' => $contractId])
            ->willReturn($contractFound);

        $result = $searchClass->searchContractByContractId($contractId);

        $this->assertNotEmpty($result, 'Le tableau est vide.');
        $this->assertSame($contractFound, $result, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }

    public function testContractNotFoundById(): void
    {
        $searchClass = new SearchController(
            $this->mongoDBService,
            $this->contractRepository,
            $this->billingRepository,
            $this->userRequestProvider
        );

        $contractId = 9999999999;
        $contractNotFound = [];

        $this->contractRepository->method('findBy')
            ->with(['id' => $contractId])
            ->willReturn($contractNotFound);

        $result = $searchClass->searchContractByContractId($contractId);

        $this->assertEmpty($result, 'Le tableau n\'est pas vide.');
        $this->assertSame($contractNotFound, $result, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }
}