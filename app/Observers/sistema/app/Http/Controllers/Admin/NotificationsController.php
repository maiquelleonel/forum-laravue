<?php

namespace App\Http\Controllers\Admin;

use App\Services\Notifications\AsaasNotification;
use App\Services\Notifications\BoletoFacilNotification;
use App\Services\Notifications\MundiPaggNotification;
use App\Services\Notifications\PagSeguroNotification;
use Exception;
use App\Http\Requests\Admin\Request;
use Monolog\Logger;

class NotificationsController extends Controller
{
    /**
     * @param Request $request
     * @param AsaasNotification $notification
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function asaas(Request $request, AsaasNotification $notification)
    {
        try {
            $notification->notify($request);
        } catch (Exception $e) {
            $this->logException('Notification error (Asaas)', $e);
        }

        $this->logRequest('Notification received (Asaas)', $request);

        return response('SUCCESS');
    }

    /**
     * @param Request $request
     * @param PagSeguroNotification $pagseguro
     */
    public function pagseguro(Request $request, PagSeguroNotification $pagseguro)
    {
        try {
            $pagseguro->notify($request);
        } catch (Exception $e) {
            $this->logException('Notification error (PagSeguro)', $e);
        }

        $this->logRequest('Notification received (PagSeguro)', $request);
    }

    public function mundipagg(Request $request, MundiPaggNotification $mundipagg)
    {
        try {
            $mundipagg->notify($request);
        } catch (Exception $e) {
            $this->logException('Notification error (MundiPagg)', $e);
        }

        $this->logRequest('Notification received (MundiPagg)', $request);

        return response('OK');
    }


    public function boletofacil(Request $request, BoletoFacilNotification $boletoFacil)
    {
        try {
            $boletoFacil->notify($request);
        } catch (Exception $e) {
            $this->logException('Notification error (Boleto Facil)', $e);
        }

        $this->logRequest('Notification received (Boleto Facil)', $request);
    }

    private function logRequest($title, Request $request)
    {
        try {
            \Log::getMonolog()->log(Logger::NOTICE, $title, [
                "headers"       => $request->headers,
                "content_type"  => $request->getContentType(),
                "contents"      => $request->getContent(),
                "body"          => $request->isJson() ? $request->json() : $request->all()
            ]);
        } catch (\Exception $e) {

        }
    }

    private function logException($title, Exception $e)
    {
        try {
            \Log::getMonolog()->log(Logger::ALERT, $title, [$e]);
        } catch (\Exception $e) {

        }
    }
}
