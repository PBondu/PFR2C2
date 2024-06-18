<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\ContractRepository;
use App\Repository\BillingRepository;
use App\Service\MongoDBService;
use App\Service\UserRequestProvider;
use App\Controller\SearchController;

class BillingByContractId extends TestCase
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

        $billingId = 3;
        $billFound = [(object)['id' => $billingId]];
        //$billFound = [(object)['id' => 1 ]];

        $this->billingRepository->method('findBy')
            ->with(['Contract_id' => $billingId])
            ->willReturn($billFound);

        $result = $searchClass->searchBillingByContractId($billingId);

        $this->assertNotEmpty($result, 'Le tableau est vide.');
        $this->assertSame($billFound, $result, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }

    public function testContractNotFoundById(): void
    {
        $searchClass = new SearchController(
            $this->mongoDBService,
            $this->contractRepository,
            $this->billingRepository,
            $this->userRequestProvider
        );

        $billingId = 9999999999;
        $billingNotFound = [];

        $this->billingRepository->method('findBy')
            ->with(['Contract_id' => $billingId])
            ->willReturn($billingNotFound);

        $result = $searchClass->searchBillingByContractId($billingId);

        $this->assertEmpty($result, 'Le tableau n\'est pas vide.');
        $this->assertSame($billingNotFound, $result, 'Le contrat renvoyé par la fonction n\'est pas correct');
    }
}