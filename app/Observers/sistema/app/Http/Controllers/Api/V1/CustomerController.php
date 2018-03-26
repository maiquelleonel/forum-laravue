<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/19/18
 * Time: 12:55
 */

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\CustomerRequest;
use App\Http\Responses\ApiResponse;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Orders\Criteria\SiteCriteria;
use App\Services\Customer\CreateCustomerService;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    /**
     * @var CreateCustomerService
     */
    private $customerService;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * CustomerController constructor.
     * @param CreateCustomerService $customerService
     * @param CustomerRepository $customerRepository
     */
    public function __construct(CreateCustomerService $customerService,
                                CustomerRepository $customerRepository)
    {
        $this->customerService = $customerService;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @SWG\Get(
     *     path="/v1/customer",
     *     tags={"Customer"},
     *     summary="List customers",
     *     @SWG\Response(
     *          response=200,
     *          description="List of customers",
     *          @SWG\Schema(ref="#/definitions/ArrayOfCustomers")
     *      )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $request)
    {
        $customers = $this->customerRepository
                          ->pushCriteria(new SiteCriteria([$request->session()->get("site_id")]))
                          ->paginate(100);

        return $this->response(true, "success", $customers->toArray());
    }

    /**
     * @SWG\Get(
     *     path="/v1/customer/{id}",
     *     tags={"Customer"},
     *     summary="Fetch customer",
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          type="integer",
     *          description="Customer ID",
     * 		),
     *     @SWG\Response(
     *          response=200,
     *          description="Fetch Customer",
     *          @SWG\Schema(ref="#/definitions/Customer")
     *      )
     * )
     * @param Request $request
     * @param $customerId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function show(Request $request, $customerId)
    {
        $customer =  $this->customerRepository
                          ->pushCriteria(new SiteCriteria([$request->session()->get("site_id")]))
                          ->find($customerId);

        return new ApiResponse(true, "OK", $customer);
    }

    /**
     * @SWG\Post(
     *     path="/v1/customer",
     *     tags={"Customer"},
     *     summary="Create new customer",
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *              allOf={
     *                  {"$ref":"#/definitions/Customer"},
     *                  {"properties":{
     *                      "tracking":{
     *                          "schema":{"$ref":"#/definitions/Tracking"}
     *                      }}
     *                  }
     *              }
     *          )
     * 		),
     *     @SWG\Response(
     *          response=200,
     *          description="A newly-created customer",
     *          @SWG\Schema(ref="#/definitions/Customer")
     *      )
     * )
     * @param CustomerRequest $request
     * @return ApiResponse
     */
    public function store(CustomerRequest $request)
    {
        $customer = $this->customerService->create(array_merge($request->all(), [
            "site_id" => $request->session()->get("site_id")
        ]));

        if($customer->wasRecentlyCreated && $request->get("tracking")){
            $customer->visits()->create($request->get("tracking"));
        }

        return new ApiResponse(true, "OK", $customer);
    }

    /**
     * @SWG\Put(
     *     path="/v1/customer/{id}",
     *     tags={"Customer"},
     *     summary="Update customer",
     *     @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          type="integer",
     *          description="Customer ID",
     * 	   ),
     *     @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(ref="#/definitions/Customer")
     * 		),
     *     @SWG\Response(
     *          response=200,
     *          description="A updated customer",
     *          @SWG\Schema(ref="#/definitions/Customer")
     *     )
     * )
     * @param CustomerRequest $request
     * @param $customerId
     * @return ApiResponse
     */
    public function update(CustomerRequest $request, $customerId)
    {
        $customer = $this->customerService->create(array_merge($request->all(), [
            "id"      => $customerId,
            "site_id" => $request->session()->get("site_id")
        ]));

        return new ApiResponse(true, "OK", $customer);
    }
}