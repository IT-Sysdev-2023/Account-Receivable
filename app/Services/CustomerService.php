<?php

namespace App\Services;

use App\Models\MasterfileModels\Customer;

class CustomerService
{
    /**
     * Get customer by customer code
     */
    public static function getCustomerByCode(string $code): ?Customer
    {
        $code = trim($code);
        return Customer::whereRaw('LTRIM(cus_code) = ?', [$code])->first();
    }

    /**
     * Get all customers
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Customer::all();
    }
}
