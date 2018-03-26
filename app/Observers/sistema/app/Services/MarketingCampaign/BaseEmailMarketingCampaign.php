<?php

namespace App\Services\MarketingCampaign;


use App\Domain\LeadSegment;
use App\Entities\Customer;
use App\Repositories\Customers\CustomerRepository;
use App\Services\MarketingCampaign\Contracts\Lead as LeadContractApi;
use App\Services\MarketingCampaign\Contracts\LeadList as LeadListContractApi;

abstract class BaseEmailMarketingCampaign
{
    /**
     * @var LeadListContractApi
     */
    protected $leadListApi;
    /**
     * @var LeadContractApi
     */
    protected $leadApi;
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * SendCustomerToEmailMarketing constructor.
     * @param CustomerRepository $customerRepository
     */
    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Customer $customer
     * @return LeadContractApi|mixed|null
     * @throws \Exception
     */
    protected function getLeadApi(Customer $customer)
    {
        if ($customer->site && $customer->site->emailCampaignSetting) {
            return $this->leadApi = EmailMarketingFactory::newLeadApi( $customer->site->emailCampaignSetting );
        }

        return null;
    }

    /**
     * @param Customer $customer
     * @return LeadListContractApi|mixed|null
     * @throws \Exception
     */
    protected function getLeadListApi(Customer $customer)
    {
        if ($customer->site && $customer->site->emailCampaignSetting) {
            return $this->leadListApi = EmailMarketingFactory::newLeadListApi( $customer->site->emailCampaignSetting );
        }

        return null;
    }

    /**
     * @param Customer $customer
     * @return string
     */
    protected function detectList(Customer $customer)
    {
        // Cliente sem pedido
        if ( !$this->customerRepository->customerHasOrder($customer) ) {
            return LeadSegment::INTERESSADO;
        }

        // Cliente com pagamento aprovado com upsell
        else if ($this->customerRepository->customerHasUpsellOrder($customer)) {
            return LeadSegment::CLIENTE_COM_UPSELL;
        }

        // Cliente com pagamento aprovado (sem upsell ou boleto pago)
        else if ($this->customerRepository->customerHasApprovedOrder($customer)) {
            return LeadSegment::CLIENTE_SEM_UPSELL;
        }

        // Cliente tem boleto emitido
        else if ($this->customerRepository->customerHasPendingOrder($customer)) {
            return LeadSegment::PAGAMENTO_PENDENTE;
        }

        return LeadSegment::PAGAMENTO_NAO_APROVADO;
    }

    public function fire(Customer $customer)
    {
        $this->leadApi      = $this->getLeadApi( $customer );
        $this->leadListApi  = $this->getLeadListApi( $customer );
    }
}