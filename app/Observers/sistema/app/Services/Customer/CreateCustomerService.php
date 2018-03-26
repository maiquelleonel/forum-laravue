<?php

namespace App\Services\Customer;


use App\Entities\Customer;
use App\Repositories\Customers\CustomerRepository;

class CreateCustomerService
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * CreateCustomerService constructor.
     * @param CustomerRepository $customerRepository
     */
    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param array $data
     * @return Customer
     */
    public function create($data = [])
    {
        if($customer = $this->find($data)){
            if(isset($data["site_id"])) unset($data["site_id"]);
            if(isset($data["email"])) unset($data["email"]);
            return $this->customerRepository->update( $data, $customer->id);
        }

        return $this->customerRepository->create( $data );
    }

    /**
     * @param $data
     * @return Customer|null
     */
    protected function find($data)
    {
        try {
            if(isset($data["email"], $data["site_id"])){
                return $this->customerRepository->findWhere([
                    "email"     => $data["email"],
                    "site_id"   => $data["site_id"]
                ])->first();
            }

            if(isset($data["customer_id"]) && $data["customer_id"]){
                return $this->customerRepository->find($data["customer_id"]);
            }

            if(isset($data["id"]) && $data["id"]){
                return $this->customerRepository->find($data["id"]);
            }
        } catch (\Exception $e) {
        }

        return null;
    }
}