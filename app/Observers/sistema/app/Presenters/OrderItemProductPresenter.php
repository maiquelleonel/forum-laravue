<?php

namespace App\Presenters;

use App\Domain\OrderStatus;
use Laracasts\Presenter\Presenter;
use Form;

class OrderItemProductPresenter extends Presenter
{
    public function total()
    {
        return monetary_format( $this->entity->qty * $this->entity->price );
    }

    public function price()
    {
        return monetary_format( $this->entity->price );
    }

    public function buttonDelete()
    {
        if($this->entity->order->status != OrderStatus::INTEGRATED) {
            return Form::open(["route" => ['admin:orderitemproduct.destroy', $this->entity->id], 'method' => 'DELETE'])
            . "<button type='submit' class='btn btn-danger btn-xs' title='Remover Item do Pedido'>
                           <i class='fa fa-trash' ></i>
                       </button>"
            . Form::close();
        } else {
            return "<button type='button' class='btn btn-danger btn-xs disabled' title='Impossível Excluir Item'>
                        <i class='fa fa-trash''></i>
                    </button>";
        }
    }
}