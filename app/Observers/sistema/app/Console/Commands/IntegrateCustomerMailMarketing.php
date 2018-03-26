<?php

namespace App\Console\Commands;

use App\Repositories\Customers\Criteria\NeedsMarketingUpdateCriteria;
use App\Repositories\Customers\Criteria\WithoutMarketingIntegrationCriteria;
use App\Repositories\Customers\CustomerRepository;
use App\Services\MarketingCampaign\SendCustomerToEmailMarketing;
use App\Services\MarketingCampaign\UpdateCustomerFromEmailMarketing;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class IntegrateCustomerMailMarketing extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketing:integrate-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send customers to mail marketing campaigns';

    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var SendCustomerToEmailMarketing
     */
    private $sendCustomerService;
    /**
     * @var UpdateCustomerFromEmailMarketing
     */
    private $updateCustomerService;

    /**
     * SendCustomerMarketingCampaign constructor.
     * @param CustomerRepository $customerRepository
     * @param SendCustomerToEmailMarketing $sendCustomerService
     * @param UpdateCustomerFromEmailMarketing $updateCustomerService
     */
    public function __construct(CustomerRepository $customerRepository,
                                SendCustomerToEmailMarketing $sendCustomerService,
                                UpdateCustomerFromEmailMarketing $updateCustomerService)
    {
        parent::__construct();
        $this->customerRepository = $customerRepository;
        $this->sendCustomerService = $sendCustomerService;
        $this->updateCustomerService = $updateCustomerService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->sendCustomers(
            $this->customerRepository->getByCriteria(new WithoutMarketingIntegrationCriteria)
        );

        $this->updateCustomers(
            $this->customerRepository->getByCriteria(new NeedsMarketingUpdateCriteria)
        );
    }

    /**
     * Send Customers to Marketing Campaigns
     * @param $customers
     */
    private function sendCustomers($customers)
    {
        foreach ($customers as $customer) {
            $this->info("Integrando " . implode(", ",[$customer->id, $customer->firstname, $customer->email]));
            if($this->sendCustomerService->fire( $customer )){
                $this->comment("INTEGRADO COM SUCESSO");
            } else {
                $this->error("FALHA NA INTEGRACAO");
            }
        }
    }

    /**
     * Update Customers from Marketing Campaigns Lists
     * @param $customers
     */
    private function updateCustomers($customers)
    {
        foreach ($customers as $customer) {
            $this->info("Atualizando " . implode(", ",[$customer->id, $customer->firstname, $customer->email]));
            if($this->updateCustomerService->fire( $customer )){
                $this->comment("ATUALIZADO COM SUCESSO");
            } else {
                $this->error("FALHA NA ATUALIZAÇÃO");
            }
        }
    }
}
