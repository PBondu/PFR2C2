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

    public function testContractFoundByContractId(): void
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

        $functionResult = $searchClass->searchContractByContractId($contractId);

        $this->assertNotEmpty($functionResult, 'Le tableau est vide.');
        $this->assertSame($contractFound, $functionResult, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }

    public function testContractNotFoundByContractId(): void
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

        $functionResult = $searchClass->searchContractByContractId($contractId);

        $this->assertEmpty($functionResult, 'Le tableau n\'est pas vide.');
        $this->assertSame($contractNotFound, $functionResult, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }
}