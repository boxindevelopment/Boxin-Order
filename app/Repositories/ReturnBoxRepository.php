<?php

namespace App\Repositories;

  use App\Model\ReturnBoxes;
use App\Repositories\Contracts\ReturnBoxRepository as ReturnBoxRepositoryInterface;
use DB;

class ReturnBoxRepository implements ReturnBoxRepositoryInterface
{
    protected $model;

    public function __construct(ReturnBoxes $model)
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

    public function findPaginate($args = [])
    {
        // Set default args
        $args = array_merge([
            'perPage' => $args['limit'] != 0 ? $args['limit'] : 10,
        ], $args);
        
        $query = $this->model->query();
        $query->select('return_boxes.id','return_boxes.order_detail_id','return_boxes.types_of_pickup_id','return_boxes.address','return_boxes.longitude','return_boxes.latitude','return_boxes.date','return_boxes.note','return_boxes.status_id', 'return_boxes.time_pickup', 'return_boxes.created_at', 'order_details.types_of_duration_id', 'order_details.room_or_box_id', 'order_details.types_of_box_room_id', 'order_details.types_of_size_id',  'order_details.name', 'order_details.amount',  'order_details.duration',  'order_details.start_date', 'order_details.end_date', DB::raw('orders.user_id as user_id'), DB::raw('datediff("Day", order_details.end_date, order_details.start_date) as total_time'), DB::raw('datediff("Day", GETDATE(),order_details.start_date) as selisih'));
        $query->leftJoin('order_details', 'order_details.id', '=', 'return_boxes.order_detail_id');
        $query->leftJoin('orders', 'orders.id', '=', 'order_details.order_id');
        $query->where('user_id', $args['user_id']);
        $query->where(function ($q) {
            $q->where('return_boxes.status_id', '=', 2) // on delivery
                ->orWhere('return_boxes.status_id', '=', 11) // pending                
                ->orWhere('return_boxes.status_id', '=', 12); // finished
        }); 

        $query->orderBy('return_boxes.id', 'DESC');

        $data = $query->paginate($args['perPage']);

        return $data;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(ReturnBoxes $returnboxes, $data)
    {
        try{
            return $returnboxes->update($data);
        }
        catch(\Exception $e){
           return $e->getMessage();
        }
    }

    public function delete(ReturnBoxes $returnboxes)
    {
        return $returnboxes->delete();
    }
}
