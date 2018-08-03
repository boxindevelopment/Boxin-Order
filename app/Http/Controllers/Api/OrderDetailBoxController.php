<?php

namespace App\Http\Controllers\Api;

use App\Entities\OrderDetailBox;
use App\Entities\OrderDetail;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailBoxResource;
use Illuminate\Http\Request;
use DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
USE File;

class OrderDetailBoxController extends Controller
{
    
    public function startDetailItemBox(Request $request)
    {
        
        $validator = \Validator::make($request->all(), [
            'order_detail_id'   => 'required',
            'item_image'        => 'required|image|mimes:jpeg,png,jpg',
            'item_name'         => 'required',
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
                    $getimageName = time().'.'.$request->item_image->getClientOriginalExtension();
                    $image = $request->item_image->move(public_path('images/detail_item_box'), $getimageName);
        
                }
            }

            $order                  = new OrderDetailBox;
            $order->order_detail_id = $request->order_detail_id;
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
        $orders = OrderDetailBox::select('order_detail_boxes.*')
            ->leftJoin('order_details', 'order_details.id', '=', 'order_detail_boxes.order_detail_id')
            ->where('order_detail_boxes.order_detail_id', $order_detail_id)
            ->get();

        if(count($orders) != 0) {
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

    public function getItemById($item_box_id)
    {
        $orders = OrderDetailBox::select('order_detail_boxes.*')
            ->leftJoin('order_details', 'order_details.id', '=', 'order_detail_boxes.order_detail_id')
            ->where('order_detail_boxes.id', $item_box_id)
            ->get();

        if(count($orders) != 0) {
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
            'item_image'        => 'image|mimes:jpeg,png,jpg',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ]);
        }

        try {
            $id     = $request->item_box_id;
            $item   = OrderDetailBox::findOrFail($id); 
            
            $dataItem           = DB::table('order_detail_boxes')->where('id', $id)->get();
            $getImage           = $dataItem[0]->item_image;

            $data               = $request->all();
            if($item){
                if ($request->hasFile('item_image')) {
                    $image_path = "/images/detail_item_box/{$getImage}";
                    if ($request->file('item_image')->isValid()) {
                        if (file_exists(public_path().$image_path)) {
                            unlink(public_path().$image_path);
                        }
                        $getimageName = time().'.'.$request->item_image->getClientOriginalExtension();
                        $image = $request->item_image->move(public_path('images/detail_item_box'), $getimageName);
            
                    }
                }
                $data["item_image"]     = $getimageName;
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

    public function deleteItem($item_box_id)
    {

        try {
            $id     = $item_box_id;
            $item   = OrderDetailBox::findOrFail($id); 
            
            $dataItem           = DB::table('order_detail_boxes')->where('id', $id)->get();
            $getImage           = $dataItem[0]->item_image;

            if($item){
                $image_path = "/images/detail_item_box/{$getImage}";
                if (file_exists(public_path().$image_path)) {
                    unlink(public_path().$image_path);
                }
                $item->delete();
            }
            
        } catch (\Exception $e) {
            
            return response()->json([
                'status' =>false,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Delete detail item box success.',
            'data' => new OrderDetailBoxResource($item)
        ]);
        
    }

}