<?php

namespace App\Http\Requests\Admin;
use App\Entities\Order;
class OrderRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $total = 0;
        if($this->request->get('order_id')){
            $order = Order::find($this->request->get('order_id'));
            $total = $order->total + $order->freight;
        }
        //dd((float)$this->request->get('discount'));
        return [
            'discount' => 'sometimes|required|numeric|min:0|max:'. $total,
        ];

    }
}
