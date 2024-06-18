<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class UserRequestProvider
{
    public function getBillingId(Request $request): ?int
    {
        return $request->request->get('billing_id') ? (int) $request->request->get('billing_id') : null;
    }

    public function getContractId(Request $request): ?int
    {
        return $request->request->get('contract_id') ? (int) $request->request->get('contract_id') : null;
    }

    public function getCustomerId(Request $request): ?int
    {
        return $request->request->get('customer_id') ? (int) $request->request->get('customer_id') : null;
    }

    public function getVehicleId(Request $request): ?int
    {
        return $request->request->get('vehicle_id') ? (int) $request->request->get('vehicle_id') : null;
    }

    public function getFirstName(Request $request): ?string
    {
        return $request->request->get('FirstName_input');
    }

    public function getLastName(Request $request): ?string
    {
        return $request->request->get('LastName_input');
    }

    public function getLicencePlate(Request $request): ?string
    {
        return $request->request->get('immat_input');
    }

    public function getKmInput(Request $request): ?int
    {
        return $request->request->get('km_input') ? (int) $request->request->get('km_input') : null;
    }

    public function getBeginDateLate(Request $request): ?string
    {
        return $request->request->get('beginDateLate');
    }

    public function getEndDateLate(Request $request): ?string
    {
        return $request->request->get('endDateLate');
    }

    public function getShowParameter(Request $request, string $parameter): bool
    {
        return $request->request->get($parameter) == 'show';
    }
}