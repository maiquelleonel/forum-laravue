<?php

namespace App\Presenters;

use App\Domain\OrderStatus;
use App\Support\MobileDetect;
use Laracasts\Presenter\Presenter;
use App\Entities\Status;

class OrderPresenter extends Presenter
{
    public function lastUpdate()
    {
        return $this->entity->updated_at->format('d/m/Y H\hi');
    }

    public function createdAt()
    {
        return $this->entity->created_at->format('d/m/Y H\hi');
    }

    public function paidAt()
    {
        if ($this->entity->paid_at) {
            return $this->entity->paid_at->format('d/m/Y H\hi');
        }
        return null;
    }

    public function customerStatusLabel()
    {
        if (in_array($this->status, [Status::APPROVED, Status::INTEGRATED, Status::APPROVED])) {

            return $this->createLabel( Status::APPROVED );

        } else if (in_array($this->status, [Status::RETRY, Status::CANCELED])) {

            return $this->createLabel( Status::CANCELED );

        }

        return $this->createLabel( Status::PENDING );
    }

    public function statusLabel()
    {
        return $this->createLabel($this->status);
    }

    private function createLabel($status)
    {
        return "<label class='label label-" . config("status.$status.label") . "'>" .
                    \Html::faIcon( config("status.$status.icon") ) . " | " .
                    config( "status.$status.text" ) .
               "</label>";
    }

    public function paymentType()
    {
        switch ($this->payment_type_collection) {
            case "CreditCard":
                return "Cartão de Crédito";
        }

        return $this->payment_type_collection;
    }

    public function paymentTypeIcon()
    {
        switch(mb_strtoupper($this->payment_type_collection))
        {
            case "CREDITCARD":
                return "<i class='fa fa-credit-card' title='Cartão de Crédito'></i>";

            case "BOLETO";
                return "<i class='fa fa-barcode' title='Boleto'></i>";

            case "PAGSEGURO";
                return "<i class='fa fa-dollar' title='PagSeguro'></i>";
        }

        return "<i class='fa fa-money' title='".$this->payment_type_collection."'></i>";
    }

    public function total()
    {
        return 'R$ ' . number_format( $this->entity->total, 2, ',', '.');
    }

    public function totalWithFreight()
    {
        return 'R$ ' . number_format( $this->entity->total + $this->entity->freight_value, 2, ',', '.' );
    }

    public function totalWithFreightAndDiscount()
    {
        return 'R$ ' . number_format( $this->entity->total - $this->entity->discount + $this->entity->freight_value, 2, ',', '.' );
    }

    public function totalWithUpsellOrder()
    {
        $total = $this->entity->total + $this->entity->freight_value - $this->entity->discount;
        if ($upsellOrder = $this->entity->upsellOrder) {
            $total += $upsellOrder->total + $upsellOrder->freight_value - $upsellOrder->discount;
        }
        return monetary_format($total);
    }

    public function freight()
    {
        return 'R$ ' . number_format( $this->entity->freight_value ?: 0, 2, ',', '.' );
    }

    public function discount()
    {
        return 'R$ ' . number_format( $this->entity->discount, 2, ',', '.');
    }

    public function installments()
    {
        return installments($this->entity->total + $this->entity->freight_value, $this->entity->installments);
    }

    public function trackLink()
    {
        if ($this->entity->tracking) {
            return \Html::link("http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI="
                        . $this->entity->tracking, $this->entity->tracking, ["target"=>"_blank"]);
        }
    }

    public function origin()
    {
        switch($this->entity->origin) {
            case null:
                return "Site";
            case "system":
                return "Call Center";
            default:
                return $this->entity->origin;
        }
    }

    public function device()
    {
        if ($agent = $this->entity->user_agent) {
            $detector = new MobileDetect(null, $this->entity->user_agent);

            if ($detector->isMobile()) {
                $device = "mobile";
            } else if ($detector->isTablet()) {
                $device = "tablet";
            } else {
                $device = "desktop";
            }

            return "<i class='fa fa-{$device}' title='Pedido realizado no {$device}'></i>";
        }
    }

    public function notifications()
    {
        $notifications = "";

        // Pedido atual tem IPI e Não tem Apps
        if ($this->entity->hasIpi() && !$this->entity->hasApp()){

            do {
                if($this->entity->upsellOrder && $this->entity->upsellOrder->hasApp()) {
                    break;
                }
                $notifications.= '<div class="no-print">
                    <div class="callout callout-danger">
                        <h4><span class="fa fa-exclamation-triangle"></span> ATENÇÃO!</h4>
                        <p>O pedido contém Produtos que devem ser vendidos juntamente com um Aplicativo!</p>
                    </div>
                </div>';
            } while (false);
        }

        return $notifications;
    }

    public function warnings()
    {
        $pattern = "<div class='alert-%s text-center' style='margin: 0; padding: 10px;'><h4 style='margin: 0'>%s</h4></div>";

        $warnings = "";

        if($upsellOrderId = $this->entity->upsell_order_id){
            $upSoldTotal = $this->entity->upsellOrder->present()->totalWithFreightAndDiscount;
            $text = "Este pedido é um upsell para o pedido <b>#{$upsellOrderId}</b> no valor de <b>{$upSoldTotal}</b> ";
            $text.= link_to_route("admin:orders.show", "ABRIR PEDIDO", $this->entity->upsellOrder, ["class"=>"btn btn-xs btn-default"]);
            $warnings .= sprintf($pattern, "info", $text);
        }

        if($this->entity->upsellOrders->count()){
            $text = "<b>Este pedido contém pedidos de upsell:</b>";
            foreach($this->entity->upsellOrders as $upsellOrder){
                $text.= "<br>Pedido <b>#{$upsellOrder->id}</b> no valor de <b>{$upsellOrder->present()->totalWithFreightAndDiscount}</b> ";
                if($user = $upsellOrder->seller){
                    $text.= " pelo vendedor <b>{$user->name}</b> ";
                }
                $text.= " {$upsellOrder->present()->statusLabel} ";
                $text.= link_to_route("admin:orders.show", "ABRIR PEDIDO", $upsellOrder, ["class"=>"btn btn-xs btn-default"]);
            }
            $warnings .= sprintf($pattern, "info", $text);
        }

        return $warnings;
    }

    public function erpActions()
    {
        if((auth()->user()->hasPermission("admin:orders.integrate") || auth()->user()->hasPermission("admin:orders.integrate-now"))
            && $this->entity->status != OrderStatus::INTEGRATED
            && $this->entity->isPaid()
            && $this->entity->upsellOrder
            && $this->entity->upsellOrder->isPaid()){
            return "<small class='text-danger'>Este pedido deve ser integrado através do pedido #{$this->entity->upsellOrder->id}</small>";
        }

        $actions = "";
        $erpSetting = $this->entity->customer->site->erpSetting;
        $onclick = "onclick=\"$('.loading-page').show()\"";

        if(auth()->user()->hasPermission("admin:orders.integrate") && $erpSetting) {
            $status = [OrderStatus::APPROVED, OrderStatus::PENDING_INTEGRATION, OrderStatus::PENDING_INTEGRATION_IN_ANALYZE];
            if(in_array($this->entity->status, $status) && $erpSetting->run_validations) {
                $actions .= '<div class="pull-right text-center" style="margin-left: 10px">';
                $actions .= \Form::open(["route"=>["admin:orders.integrate", $this->entity->id]]);
                $actions .= '<button class="btn btn-primary btn-xs" '.$onclick.'>';
                $actions .= '<strong>Analisar e integrar pedido!</strong>';
                $actions .= '</button>';
                if ($erpSetting->generate_invoice) {
                    $actions.= '<br><small class="text-muted">NF-e automática: <strong>SIM</strong></small>';
                } else {
                    $actions.= '<br><small class="text-muted">NF-e automática: <strong>NÃO</strong></small>';
                }
                $actions .= \Form::close();
                $actions .= '</div>';
            }
        }

        if(auth()->user()->hasPermission("admin:orders.integrate-now") && $erpSetting) {
            if(in_array($this->entity->status, $status)) {
                $actions .= '<div class="pull-right text-center" style="margin-left: 10px">';
                $actions .= \Form::open(["route"=>["admin:orders.integrate-now", $this->entity->id]]);
                $actions .= '<button class="btn btn-warning btn-xs" '.$onclick.'>';
                $actions .= '<strong title="Integrar sem validar">Integrar pedido agora!</strong>';
                $actions .= '</button>';
                if ($erpSetting->generate_invoice) {
                    $actions.= '<br><small class="text-muted">NF-e automática: <strong>SIM</strong></small>';
                } else {
                    $actions.= '<br><small class="text-muted">NF-e automática: <strong>NÃO</strong></small>';
                }
                $actions .= \Form::close();
                $actions .= '</div>';
            }
        }

        return $actions;
    }

}
