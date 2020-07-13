<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\ExtendOrderDetailResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use SoftDeletes;

    protected $table = 'order_details';

    protected $fillable = [
        'order_id', 'types_of_duration_id', 'room_or_box_id', 'types_of_box_room_id', 'types_of_size_id', 'name', 'duration', 'amount', 'start_date', 'end_date', 'status_id', 'is_returned', 'id_name', 'place', 'pickup'
    ];

    public function order()
    {
        return $this->belongsTo('App\Model\Order', 'order_id', 'id');
    }

    public function box()
    {
        return $this->belongsTo('App\Model\Box', 'room_or_box_id', 'id');
    }

    public function room()
    {
        return $this->belongsTo('App\Model\SpaceSmall', 'room_or_box_id', 'id');
    }

    public function space_small()
    {
        return $this->belongsTo('App\Model\SpaceSmall', 'room_or_box_id', 'id');
    }

    public function type_box_room()
    {
        return $this->belongsTo('App\Model\TypeBoxRoom', 'types_of_box_room_id', 'id');
    }

    public function type_size()
    {
        return $this->belongsTo('App\Model\TypeSize', 'types_of_size_id', 'id');
    }

    public function type_duration()
    {
        return $this->belongsTo('App\Model\TypeDuration', 'types_of_duration_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo('App\Model\Status', 'status_id', 'id');
    }

    public function order_detail_box()
    {
        return $this->hasMany('App\Model\OrderDetailBox', 'order_detail_id', 'id');
    }

    public function return_box()
    {
        return $this->hasMany('App\Model\ReturnBoxes', 'order_detail_id', 'id');
    }
    
    public function return_box_payment()
    {
        return $this->hasMany('App\Model\ReturnBoxPayment', 'order_detail_id', 'id');
    }
    
    public function change_box_payment()
    {
        return $this->hasMany('App\Model\ChangeBoxPayment', 'order_detail_id', 'id');
    }
    
    public function change_box()
    {
        return $this->hasMany('App\Model\ChangeBox', 'order_detail_id', 'id');
    }
    
    public function extend()
    {
        return $this->hasMany('App\Model\ExtendOrderDetail', 'order_detail_id', 'id');
    }
    
    public function extend_payment()
    {
        return $this->hasMany('App\Model\ExtendOrderPayment', 'order_detail_id', 'id');
    }
    
    public function add_item()
    {
        return $this->hasMany('App\Model\AddItemBox', 'order_detail_id', 'id');
    }
    
    public function add_item_payment()
    {
        return $this->hasMany('App\Model\AddItemBoxPayment', 'order_detail_id', 'id');
    }

    public function order_back_warehouse()
    {
        return $this->hasMany('App\Model\OrderBackWarehouse', 'order_detail_id', 'id');
    }

    public function order_take()
    {
        return $this->hasMany('App\Model\OrderTake', 'order_detail_id', 'id');
    }
    
    public function toSearchableArray()
    {
        // $url = (env('DB_DATABASE') == 'coredatabase') ? 'https://boxin-dev-webbackend.azurewebsites.net/' : 'https://boxin-prod-webbackend.azurewebsites.net/';
        $url = env('APP_ADMIN');

        if (!is_null($this->order_id)) {
            $order = [
                'id'                     => $this->order->id,
                'total'                  => $this->order->total,
                'qty'                    => $this->order->qty,
                'payment_expired'        => $this->order->payment_expired,
                'payment_status_expired' => $this->order->payment_status_expired
            ];
        }

        if (!is_null($this->types_of_size_id)) {
            $type_size = [
                'id'        => $this->type_size->id,
                'name'      => $this->type_size->name,
                'size'      => $this->type_size->size,
                'image'     => is_null($this->type_size->image) ? null : $url.'images/types_of_size'.'/'.$this->type_size->image,
            ];
        }

        if (!is_null($this->types_of_box_room_id)) {
            $type_box_room = [
                'id'        => $this->type_box_room->id,
                'name'      => $this->type_box_room->name,
                'code'      => $this->type_box_room->id_name,
            ];
        }

        if (!is_null($this->types_of_duration_id)) {
            $difference = $this->selisih;
            if($difference <= 0){
                $difference = 0;
            }
            if($difference >= $this->total_time){
                $difference = $this->total_time;
            }
            $duration = [
                'id'                => $this->type_duration->id,
                'count_time'        => $difference,
                'total_time'        => $this->total_time,
                'duration_storing'  => $this->duration,
                'name'              => $this->type_duration->name,
                'alias'             => $this->type_duration->alias,
            ];
        }

        $pick_up = null;
        if(!is_null($this->order->pickup_order)){
            if($this->order->pickup_order->type_pickup->id == 1){
                $pick_up = [
                    'pickup_id'         => $this->order->pickup_order->id,
                    'address'           => $this->order->pickup_order->address,
                    'date'              => $this->order->pickup_order->date,
                    'note'              => $this->order->pickup_order->note,
                    'pickup_fee'        => intval($this->order->pickup_order->pickup_fee),
                    'driver_name'       => $this->order->pickup_order->driver_name,
                    'driver_phone'      => $this->order->pickup_order->driver_phone,
                    'time_pickup'       => $this->order->pickup_order->time_pickup,
                    'type_pickup_id'    => $this->order->pickup_order->type_pickup->id,
                    'type_pickup_name'  => $this->order->pickup_order->type_pickup->name,
                ];
            }
        }

        $pickup_delivery = null;
        if($this->pickup != null){
            if($this->pickup == 'order'){
                $pickup_delivery = [
                    'note'          => $this->order->pickup_order->note,
                    'driver_name'   => $this->order->pickup_order->driver_name,
                    'driver_phone'  => $this->order->pickup_order->driver_phone
                ];
            } else if($this->pickup == 'take'){
                $deliveryTake = $this->order_take->where('status_id', 2)->first();
                $pickup_delivery = [
                    'note'          => ($deliveryTake) ? $deliveryTake->note : null,
                    'driver_name'   => ($deliveryTake) ? $deliveryTake->driver_name : null,
                    'driver_phone'  => ($deliveryTake) ? $deliveryTake->driver_phone : null
                ];
            } else if($this->pickup == 'return'){
                $deliveryReturn = $this->order_back_warehouse->where('status_id', 2)->first();
                $pickup_delivery = [
                    'note'          => ($deliveryReturn) ? $deliveryReturn->note : null,
                    'driver_name'   => ($deliveryReturn) ? $deliveryReturn->driver_name : null,
                    'driver_phone'  => ($deliveryReturn) ? $deliveryReturn->driver_phone : null
                ];
            } else if($this->pickup == 'terminate'){
                $deliveryTerminate = $this->order_back_warehouse->where('status_id', 2)->first();
                $pickup_delivery = [
                    'note'              => ($deliveryTerminate) ? $deliveryTerminate->note : null,
                    'driver_name'       => ($deliveryTerminate) ? $deliveryTerminate->river_name : null,
                    'driver_phone'      => ($deliveryTerminate) ? $deliveryTerminate->driver_phone : null
                ];
            }
        }

        $payment = null;
        if(!is_null($this->order->payment)){
            $payment = [
                'id'                           => $this->order->payment->id,
                'order_id'                     => $this->order->payment->order_id,
                'status_id'                    => $this->order->payment->status->id,
                'status'                       => $this->order->payment->status->name,
                'midtrans_url'                 => $this->order->payment->midtrans_url,
                'midtrans_status'              => $this->order->payment->midtrans_status,
                'midtrans_start_transaction'   => $this->order->payment->midtrans_start_transaction,
                'midtrans_expired_transaction' => $this->order->payment->midtrans_expired_transaction,
            ];

        }

        $return_box = null;
        if($this->return_box){
            foreach ($this->return_box as $k => $return_box) {
                if($return_box->type_pickup->id == 1){
                    $return_box = [
                        'return_box_id'     => $return_box->id,
                        'address'           => $return_box->address,
                        'date'              => $return_box->date,
                        'note'              => $return_box->note,
                        'deliver_fee'       => intval($return_box->deliver_fee),
                        'driver_name'       => $return_box->driver_name,
                        'driver_phone'      => $return_box->driver_phone,
                        'time_pickup'       => $return_box->time_pickup,
                        'type_pickup_id'    => $return_box->type_pickup->id,
                        'type_pickup_name'  => $return_box->type_pickup->name,
                    ];
                }
            }
        }

        $return_box_payment = null;
        if($this->return_box_payment){
            foreach ($this->return_box_payment as $k => $return_box_payment) {
                $return_box_payment = [
                    'id'                           => $return_box_payment->id,
                    'status_id'                    => $return_box_payment->status->id,
                    'status'                       => $return_box_payment->status->name,
                    'midtrans_url'                 => $return_box_payment->midtrans_url,
                    'midtrans_status'              => $return_box_payment->midtrans_status,
                    'midtrans_start_transaction'   => $return_box_payment->midtrans_start_transaction,
                    'midtrans_expired_transaction' => $return_box_payment->midtrans_expired_transaction,
                ];
            }
        }

        $change_box = null;
        if($this->change_box){
            foreach ($this->change_box as $k => $change_boxs) {
                // if($change_boxs->type_pickup->id == 1){
                    $change_box = [
                        'change_box_id'    => $change_boxs->id,
                        'address'          => $change_boxs->address,
                        'date'             => $change_boxs->date,
                        'note'             => $change_boxs->note,
                        'deliver_fee'      => intval($change_boxs->deliver_fee),
                        'driver_name'      => $change_boxs->driver_name,
                        'driver_phone'     => $change_boxs->driver_phone,
                        'status_id'        => $change_boxs->status->id,
                        'status'           => $change_boxs->status->name,
                        'time_pickup'      => $change_boxs->time_pickup,
                        'type_pickup_id'   => $change_boxs->type_pickup->id,
                        'type_pickup_name' => $change_boxs->type_pickup->name,
                        'items'            => $change_boxs->change_details
                    ];
                // }
            }
        }

        $change_box_payment = null;
        if($this->change_box_payment){
            foreach ($this->change_box_payment as $k => $change_box_payment) {
                $change_box_payment = [
                    'id'                           => $change_box_payment->id,
                    'status_id'                    => $change_box_payment->status->id,
                    'status'                       => $change_box_payment->status->name,
                    'midtrans_url'                 => $change_box_payment->midtrans_url,
                    'midtrans_status'              => $change_box_payment->midtrans_status,
                    'midtrans_start_transaction'   => $change_box_payment->midtrans_start_transaction,
                    'midtrans_expired_transaction' => $change_box_payment->midtrans_expired_transaction,
                ];
            }
        }

        $location = [
            'city_id'   => $this->order->area->city->id,
            'city'      => $this->order->area->city->name,
            'area_id'   => $this->order->area->id,
            'area'      => $this->order->area->name,
        ];

        $room_or_box = null;
        if($this->types_of_box_room_id == 1){
            if ($this->box) {
                $room_or_box = [
                    'id'   => $this->box->code_box,
                    'name' => $this->box->name
                ];
            }
        } else if($this->types_of_box_room_id == 2){
            if ($this->space_small) {
                $room_or_box = [
                    'id'   => $this->space_small->code_space_small,
                    'name' => $this->space_small->name
                ];
            }
        }

        $show = true;
        $tgl_sehari_sebelum = Carbon::parse($this->end_date);
        $tgl_sehari_sebelum->addDays(-1);
        if (Carbon::now()->gte($tgl_sehari_sebelum)){
          $show = false;
        }

        $extend = null;
        if($this->extend){
          foreach ($this->extend as $k => $v) {
            $extend = [
                'extend_id'              => $v->id,
                'extend_duration'        => $v->extend_duration,
                'remaining_duration'     => $v->remaining_duration,
                'amount'                 => $v->amount,
                'end_date_before'        => $v->end_date_before,
                'new_end_date'           => $v->new_end_date,
                'payment_expired'        => $v->payment_expired,
                'payment_status_expired' => $v->payment_status_expired == 1 ? true : false,
                'status'                 => $v->status_id,
                'status'                 => $v->status->name,
                'new_duration'           => $v->new_duration,
                'total_amount'           => $v->total_amount
            ];
          }
        }

        $extend_payment = null;
        if($this->extend_payment){
          foreach ($this->extend_payment as $k => $v) {
              $extend_payment = [
                  'id'                           => $v->id,
                  'status_id'                    => $v->status->id,
                  'status'                       => $v->status->name,
                  'midtrans_url'                 => $v->midtrans_url,
                  'midtrans_status'              => $v->midtrans_status,
                  'midtrans_start_transaction'   => $v->midtrans_start_transaction,
                  'midtrans_expired_transaction' => $v->midtrans_expired_transaction,
              ];
          }
        }

        $add_item = null;
        if ($this->add_item) {
          foreach ($this->add_item as $k => $v) {
              $items = array();
              foreach ($v->items as $kk => $vv) {
                $items[] = [
                  'id'         => $vv->id,
                  'category'   => $vv->category,
                  'item_name'  => $vv->item_name,
                  'item_image' => $vv->image,
                  'note'       => $vv->note,
                ];
              }
            // if ($v->types_of_pickup_id == 1){
              $add_item = [
                'id'               => $v->id,
                'address'          => $v->address,
                'date'             => $v->date,
                'deliver_fee'      => intval($v->deliver_fee),
                'driver_name'      => $v->driver_name,
                'driver_phone'     => $v->driver_phone,
                'status_id'        => $v->status->id,
                'status'           => $v->status->name,
                'time_pickup'      => $v->time_pickup,
                'type_pickup_id'   => $v->types_of_pickup_id,
                'type_pickup_name' => $v->type_pickup->name,
                'items'            => $items
              ];
            // }
          }
        }

        $add_item_payment = null;
        if ($this->add_item_payment) {
          foreach ($this->add_item_payment as $k => $v) {
            $add_item_payment = [
              'id'                           => $v->id,
              'status_id'                    => $v->status->id,
              'status'                       => $v->status->name,
              'midtrans_url'                 => $v->midtrans_url,
              'midtrans_status'              => $v->midtrans_status,
              'midtrans_start_transaction'   => $v->midtrans_start_transaction,
              'midtrans_expired_transaction' => $v->midtrans_expired_transaction,
            ];
          }
        }

        $data = [
            'id'                   => $this->id,
            'code'                 => $this->id_name,
            'name'                 => $this->name,
            'amount'               => $this->amount,
            'start_date'           => $this->start_date,
            'end_date'             => $this->end_date,
            'place'                => $this->place,
            'show_button_extend'   => $show,
            'room_or_box'          => $room_or_box,
            'status_id'            => $this->status->id,
            'status'               => $this->status->name,
            'types_of_box_room_id' => $type_box_room,
            'types_of_size'        => $type_size,
            'order'                => $order,
            'location'             => $location,
            'duration'             => $duration,
            'pickup'               => $pick_up,
            'pickup_delivery'      => $pickup_delivery,
            'payment'              => $payment,
            'return_box'           => $return_box,
            'return_box_payment'   => $return_box_payment,
            'change_box'           => $change_box,
            'change_box_payment'   => $change_box_payment,
            'extend'               => $extend,
            'extend_payment'       => $extend_payment,
            'add_item'             => $add_item,
            'add_item_payment'     => $add_item_payment
        ];

        return $data;

    }
}
