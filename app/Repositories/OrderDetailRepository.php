<?php

namespace App\Repositories;

use App\Model\OrderDetail;
use App\Repositories\Contracts\OrderDetailRepository as OrderDetailRepositoryInterface;
use DB;

class OrderDetailRepository implements OrderDetailRepositoryInterface
{
    protected $model;

    public function __construct(OrderDetail $model)
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

    public function getById($order_detail_id)
    {
        $orders = OrderDetail::select('order_details.*', DB::raw('orders.status_id as status_id'), DB::raw('orders.user_id as user_id'), DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'), DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'))
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('order_details.id', $order_detail_id)
            ->get();
        return $orders;

    }

    public function findPaginateMyBox($args = [])
    {
        $args = array_merge([
            'perPage' => $args['limit'] != 0 ? $args['limit'] : 10,
        ], $args);

        $query = $this->model->query();
        $query->select('order_details.*',
            DB::raw('orders.user_id as user_id'),
            DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'),
            DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'));
        $query->leftJoin('orders', 'orders.id', '=', 'order_details.order_id');
        $query->where('user_id', $args['user_id']);
        $query->where('order_details.status_id', '!=', 4);
        $query->where('order_details.status_id', '!=', 18);
        $query->orderBy('order_details.order_id', 'DESC');
        $query->orderBy('order_details.id', 'DESC');

        $orders = $query->paginate($args['perPage']);

        return $orders;
    }

    public function findPaginateMyItem($args = [])
    {
        $args = array_merge([
            'perPage' => $args['limit'] != 0 ? $args['limit'] : 10,
        ], $args);

        $query = $this->model->query();
        $query->select('order_details.id', 'order_details.id_name', 'order_details.order_id', 'order_details.is_returned', 'order_details.types_of_duration_id', 'order_details.room_or_box_id', 'order_details.types_of_box_room_id', 'order_details.types_of_size_id', 'order_details.name', 'order_details.duration', 'order_details.amount', 'order_details.start_date', 'order_details.end_date', 'order_details.status_id',
            DB::raw('orders.user_id as user_id'),
            DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'),
            DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'));
        $query->leftJoin('orders', 'orders.id', '=', 'order_details.order_id');
        $query->leftJoin('order_detail_boxes', 'order_detail_boxes.order_detail_id', '=', 'order_details.id');
        $query->where('user_id', $args['user_id']);
        $query->where('is_returned', '!=', 1);
        $query->where('order_details.status_id', 4);
        if(isset($args['search'])){
            $query->where('order_details.name',  'like', '%' . $args['search'] . '%');
            $query->orWhere('order_detail_boxes.item_name',  'like', '%' . $args['search'] . '%');
        }
        $query->orderBy('order_details.name', 'ASC');

        $orders = $query->paginate($args['perPage']);

        return $orders;
    }

    public function findPaginateMyBoxHistory($args = [])
    {
        $args = array_merge([
            'perPage' => $args['limit'] != 0 ? $args['limit'] : 10,
        ], $args);

        $query = $this->model->query();
        $query->select('order_details.id', 'order_details.id_name', 'order_details.order_id', 'order_details.is_returned', 'order_details.types_of_duration_id', 'order_details.room_or_box_id', 'order_details.types_of_box_room_id', 'order_details.types_of_size_id', 'order_details.name', 'order_details.duration', 'order_details.amount', 'order_details.start_date', 'order_details.end_date', 'order_details.status_id',
            DB::raw('orders.user_id as user_id'),
            DB::raw('DATEDIFF(day, order_details.start_date, order_details.end_date) as total_time'),
            DB::raw('DATEDIFF(day, order_details.start_date, GETDATE()) as selisih'));
        $query->leftJoin('orders', 'orders.id', '=', 'order_details.order_id');
        $query->where('user_id', $args['user_id']);
        $query->where('is_returned', 1);
        $query->where('order_details.status_id', 18);
        $query->orderBy('order_details.name', 'ASC');

        $orders = $query->paginate($args['perPage']);

        return $orders;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(OrderDetail $orderDetail, $data)
    {
        try{
            return $orderDetail->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(OrderDetail $orderDetail)
    {
        return $orderDetail->delete();
    }
}
