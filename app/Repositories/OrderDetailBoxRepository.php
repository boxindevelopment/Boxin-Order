<?php

namespace App\Repositories;

use App\Model\OrderDetailBox;
use App\Repositories\Contracts\OrderDetailBoxRepository as OrderDetailBoxRepositoryInterface;
use DB;

class OrderDetailBoxRepository implements OrderDetailBoxRepositoryInterface
{
    protected $model;

    public function __construct(OrderDetailBox $model)
    {
        $this->model = $model;
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function all()
    {
        return $this->model->get();
    }

    public function getItemByOrderDetailId($order_detail_id)
    {
        $orders = OrderDetailBox::select('order_detail_boxes.*')
            ->leftJoin('order_details', 'order_details.id', '=', 'order_detail_boxes.order_detail_id')
            ->where('order_detail_boxes.order_detail_id', $order_detail_id)
            ->where('order_detail_boxes.status_id', '<>', 21)
            ->get();
        return $orders;
    }

    public function getItemById($id)
    {
        $orders = OrderDetailBox::select('order_detail_boxes.*')
            ->leftJoin('order_details', 'order_details.id', '=', 'order_detail_boxes.order_detail_id')
            ->where('order_detail_boxes.id', $id)
            ->get();
        return $orders;
    }

    public function getById($id)
    {
        $orders = OrderDetailBox::where('order_detail_boxes.id', $id)->get();
        return $orders;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(OrderDetailBox $orderDetailBox, $data)
    {
        try{
            return $orderDetailBox->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(OrderDetailBox $orderDetailBox)
    {
        return $orderDetailBox->delete();
    }
}
