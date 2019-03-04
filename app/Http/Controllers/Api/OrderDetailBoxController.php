<?php

namespace App\Http\Controllers\Api;

use App\Model\OrderDetailBox;
use App\Model\OrderDetail;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailBoxResource;
use Illuminate\Http\Request;
use DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use File;
use App\Repositories\Contracts\OrderDetailBoxRepository;

class OrderDetailBoxController extends Controller
{
    protected $repository;

    public function __construct(OrderDetailBoxRepository $repository)
    {
        $this->repository = $repository;
    }

    public function startDetailItemBox(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
            'item_image'        => 'required|image|mimes:jpeg,png,jpg',
            'item_name'         => 'required',
            'category_id'       => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $data               = $request->all();
            if ($request->hasFile('item_image')) {
                if ($request->file('item_image')->isValid()) {
                    $getimageName = round(microtime(true) * 1000) .'.'.$request->item_image->getClientOriginalExtension();
                    $image = $request->item_image->move(public_path('images/detail_item_box'), $getimageName);
        
                }
            }

            $order                  = new OrderDetailBox;
            $order->order_detail_id = $request->order_detail_id;            
            $order->category_id     = $request->category_id;
            $order->item_name       = $request->item_name;
            $order->item_image      = $getimageName;
            $order->note            = $request->note;
            $order->save();
            
        } catch (\Exception $e) {
            
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Create detail item box success.',
            'data' => new OrderDetailBoxResource($order)
        ]);
        
    }

    public function getItemByOrderDetailId($order_detail_id)
    {
        $orders = $this->repository->getItemByOrderDetailId($order_detail_id);
        
        if(count($orders) > 0) {
            $data = OrderDetailBoxResource::collection($orders);

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);
    }

    public function getItemById($id)
    {
        $orders = $this->repository->getItemById($id);

        if(count($orders) > 0) {
            $data = OrderDetailBoxResource::collection($orders);

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Data not found'
        ]);
    }

    public function updateItem(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'item_box_id'       => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $id         = $request->item_box_id;
            $item_image = $request->item_image;
            $item       = OrderDetailBox::findOrFail($id); 
            
            $dataItem   = $this->repository->getById($id);
            $getImage   = $dataItem[0]->item_image;

            $data       = $request->all();
            if($item){
                if($item_image){
                    if ($request->hasFile('item_image')) {
                        $image_path = "/images/detail_item_box/{$getImage}";
                        if ($request->file('item_image')->isValid()) {
                            if($getImage != null || $getImage != 'NULL'){
                                if (file_exists(public_path().$image_path)) {
                                   unlink(public_path().$image_path);
                                   Storage::delete(public_path().$image_path);
                                }
                            }
                            $getimageName = time().'.'.$request->item_image->getClientOriginalExtension();
                            $image = $request->item_image->move(public_path('images/detail_item_box'), $getimageName);
                
                        }
                    }
                    $data["item_image"]     = $getimageName != '' ? $getimageName : $getImage; 
                }
                $data["item_name"]      = $request->item_name;
                $data["note"]           = $request->note;
                $item->fill($data)->save();
            }
            
        } catch (\Exception $e) {
            
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Update detail item box success.',
            'data' => new OrderDetailBoxResource($item)
        ]);
        
    }

    public function destroy($id)
    {

        $item   = $this->repository->find($id); 
        if (!$item) {
            return response()->json(['message' => 'Item box not found'], 404);
        }
        try {

            $dataItem           = $this->repository->getById($id);
            $getImage           = $dataItem[0]->item_image;
            $image_path = "/images/detail_item_box/{$getImage}";
            if (file_exists(public_path().$image_path)) {
                unlink(public_path().$image_path);
                Storage::delete(public_path().$image_path);
            }
            $hapus = OrderDetailBox::where('id', $id)->delete();
            if($hapus){
                return response()->json([
                    'status' => true,
                    'message' => 'Delete detail item box success.',
                    'data' => new OrderDetailBoxResource($item)
                ]);
            }
            
        } catch (\Exception $e) {
            
            return response()->json([
                'status' =>false,
                'message' => '$e->getMessage()'
            ]);
        }
        
    }

    public function deleteMultiple(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'delete_count' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([ 'status' => false, 'message' => $validator->errors()]);
        }

        $data = $request->all();
        if(isset($data['delete_count'])) {
            for ($a = 1; $a <= $data['delete_count']; $a++) {

                $validator = \Validator::make($request->all(), [
                    'detail_box_id'.$a => 'required',
                ]);

                if($validator->fails()) {
                    return response()->json(['status' => false, 'message' => $validator->errors()]);
                }

                $item   = $this->repository->find($data['detail_box_id'.$a]); 
                if (!$item) {
                    return response()->json(['status' => false, 'message' => 'Item box not found'], 401);
                }
            }
        } else {
            return response()->json(['status' =>false, 'message' => 'Not found delete count.'], 401);
        }

        try{
            for ($a = 1; $a <= $data['delete_count']; $a++) {
                $dataItem           = $this->repository->getById($data['detail_box_id'.$a]);
                $getImage           = $dataItem[0]->item_image;
                $image_path = "/images/detail_item_box/{$getImage}";
                if (file_exists(public_path().$image_path)) {
                    unlink(public_path().$image_path);
                    Storage::delete(public_path().$image_path);
                }
                $hapus = OrderDetailBox::where('id', $data['detail_box_id'.$a])->delete();
            }

        } catch (\Exception $e) {
            return response()->json(['status' =>false, 'message' => $e->getMessage()], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Delete detail item box success.',
        ]);
    }

}