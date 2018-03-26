<?php

namespace App\Http\Controllers\Admin;

use App\Entities\Customer;
use App\Http\Requests;
use App\Http\Requests\Admin\Request;
use App\Http\Requests\Admin\CustomerRequest;
use App\Support\SiteSettings;
use App\Repositories\Criterias\JustSitesInSessionCriteria;
use App\Repositories\Customers\Criteria\CustomerDataCriteria;
use App\Repositories\Customers\Criteria\WithoutDocumentNumberCriteria;
use App\Repositories\Customers\Criteria\WithoutOrdersCriteria;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Customers\Criteria\CanceledOrdersCriteria;
use Carbon\Carbon;
use Prettus\Repository\Criteria\RequestCriteria;

class CustomerController extends Controller
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

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
        $customers = $this->customerRepository
                        ->with("site")
                        ->pushCriteria(new RequestCriteria($request))
                        ->pushCriteria(new JustSitesInSessionCriteria())
                        ->pushCriteria(new CustomerDataCriteria($request))
                        ->paginate();

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
                            ->paginate();

        return view('admin.pages.customer.index', compact('customers', 'title'));
    }

    /**
     * Display customer details
     *
     * @param $customerHash
     * @return \Illuminate\Contracts\View\Factory
     */
    public function show($customerHash)
    {
        $customer = Customer::with([
                             'orders.bundles',
                             'orders.upsellOrders.seller',
                             'orders.seller',
                             'visits.pageViews',
                             'audits.user'
                     ])
                     ->where('hash', '=', $customerHash)
                     ->first();
        if (!$customer) {
            $type = 'Cliente';
            return view('admin.errors.404', compact('type'));
        }
        $this->checkForLog($customer);

        return view('admin.pages.customer.show', compact('customer'));
    }

    /**
     * Update a customer by ID
     * @param CustomerRequest$request
     * @param $customerHash
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(CustomerRequest $request, $customerHash)
    {
        $customer = Customer::where('hash', $customerHash)->first();
        $customer->update($request->all());

        if ($request->input('redirect')) {
            return redirect(\Html::routeUrl($request->input('redirect'), $customerHash));
        }

        return back();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin.pages.customer.create');
    }

    /**
     * @param CustomerRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CustomerRequest $request)
    {
        $customer = $this->customerRepository->create($request->all());

        if ($request->input('redirect')) {
            return redirect(\Html::routeUrl($request->input('redirect'), $customer));
        }

        return back()->withInput();
    }

    /**
     * @param $customerHash
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($customerHash)
    {
        $customer = $this->customerRepository->findByField('hash', $customerHash)->first();
        return view('admin.pages.customer.edit', compact('customer'));
    }

    private function checkForLog($customer)
    {
        if (auth()->user() && !request()->server("HTTP_REFERRER") && $customer &&
            $customer->created_at->addMinutes(20) > Carbon::now()) {
            try {
                \Log::critical( implode(" | ", [
                    auth()->user()->name,
                    request()->fullUrl(),
                    $customer->created_at
                ]));
            } catch (\Exception $e) {}
        }
    }
}
