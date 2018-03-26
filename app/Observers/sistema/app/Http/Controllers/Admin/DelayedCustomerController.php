<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\Criterias\BeforeLastUpdateTimeCriteria;
use App\Repositories\Criterias\JustSitesInSessionCriteria;
use App\Repositories\Customers\Criteria\CustomerDataCriteria;
use App\Repositories\Customers\Criteria\WithoutDocumentNumberCriteria;
use App\Repositories\Customers\Criteria\WithoutOrdersCriteria;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Customers\Criteria\CanceledOrdersCriteria;
use Carbon\Carbon;
use App\Http\Requests\Admin\Request;

use App\Http\Requests;
use App\Support\SiteSettings;
use Prettus\Repository\Criteria\RequestCriteria;

class DelayedCustomerController extends Controller
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var Carbon
     */
    private $delay;

    /**
     * CustomerController constructor.
     * @param SiteSettings $siteSettings
     * @param CustomerRepository $customerRepository
     * @param SiteSettings $siteSettings
     */
    public function __construct(SiteSettings $siteSettings,
                                CustomerRepository $customerRepository)
    {
        parent::__construct($siteSettings);
        $this->customerRepository = $customerRepository;
        $this->delay = Carbon::now()->subMinutes(30);
    }

    /**
     * Display customers.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $request)
    {
        $customerData = new CustomerDataCriteria($request);
        $customers = $this->customerRepository
                          ->with("site")
                          ->pushCriteria(new RequestCriteria($request))
                          ->pushCriteria(new JustSitesInSessionCriteria())
                          ->pushCriteria($customerData);

        if (!$this->isSearching($request, $customerData->getFieldsSearchable())) {
            $customers->pushCriteria(new BeforeLastUpdateTimeCriteria($this->delay, "created_at"));
        }

        $customers = $customers->paginate();

		return view('admin.pages.customer.index', compact('customers'));
    }


    /**
     * Display canceled customers with orders
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory
     */
    public function canceled(Request $request)
    {
        $title = "Clientes Com Pagamento não Autorizado";

        $customers = $this->customerRepository
                            ->pushCriteria(new RequestCriteria($request))
                            ->pushCriteria(new CanceledOrdersCriteria())
                            ->pushCriteria(new CustomerDataCriteria($request))
                            ->pushCriteria(new JustSitesInSessionCriteria())
                            ->pushCriteria(new BeforeLastUpdateTimeCriteria(Carbon::now()->subMinutes(10), "created_at"))
                            ->paginate();

        return view('admin.pages.customer.index', compact('customers', 'title'));
    }

    /**
     * Display interested customers
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory
     */
    public function interested(Request $request)
    {
        $title = "Clientes Interessados";

        $customers = $this->customerRepository
                            ->pushCriteria(new RequestCriteria($request))
                            ->pushCriteria(new WithoutOrdersCriteria())
                            ->pushCriteria(new WithoutDocumentNumberCriteria())
                            ->pushCriteria(new CustomerDataCriteria($request))
                            ->pushCriteria(new JustSitesInSessionCriteria())
                            ->pushCriteria(new BeforeLastUpdateTimeCriteria($this->delay, "created_at"))
                            ->paginate();

        return view('admin.pages.customer.index', compact('customers', 'title'));
    }

    private function isSearching(Request $request, $arraySearchable)
    {
        // Pelo menos 3 caracteres para considerar busca
        $filtered = collect($request->all())->filter(function($value){
            return strlen($value) >= 3;
        });

        return count(array_intersect_key($filtered->toArray(), $arraySearchable)) > 0;
    }
}
