<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\AddItem;
use App\Model\AddItemBox;
use App\Model\AddItemBoxPayment;
use App\Http\Resources\AddItemBoxResource;
use Auth;
use Validator;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderDetailBox;
use DB;
use Carbon\Carbon;
use Exception;

class AddItemBoxController extends Controller
{

  public function add_items(Request $request)
  {
  	$user = Auth::user();
    $validator = Validator::make($request->all(), [
      'order_detail_id'    => 'required',
      'types_of_pickup_id' => 'required',
      'category_id'        => 'required',
      'item_name'          => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
    }

    $item_categories = $request->category_id;
    $item_names      = $request->item_name;
    $item_notes      = $request->note;

    $order_detail_id    = $request->order_detail_id;
    $types_of_pickup_id = $request->types_of_pickup_id;

    DB::beginTransaction();
    try {

      $additembox                     = new AddItemBox;
      $additembox->order_detail_id    = $order_detail_id;
      $additembox->types_of_pickup_id = $types_of_pickup_id;
      $additembox->address            = $request->address;
      $additembox->date               = $request->date;
      $additembox->time_pickup        = $request->time_pickup;
      $additembox->status_id          = $types_of_pickup_id == '1' ? 14 : 25;
      $additembox->deliver_fee        = 0;
      $additembox->save();

      $add_id = $additembox->id;
      for ($i=0; $i < count($item_categories); $i++) {
        $getimageName = '';
        if ($request->hasFile('item_image')) {
          // if ($request->file('item_image')->isValid()) {
            $getimageName = time().'.'.$request->item_image[$i]->getClientOriginalExtension();
            $image = $request->item_image[$i]->move(public_path('images/detail_item_box/additem'), $getimageName);
          // }
        }

        AddItem::create([
          'category_id'     => $item_categories[$i],
          'item_name'       => $item_names[$i],
          'item_image'      => $getimageName,
          'note'            => $item_notes[$i],
          'add_item_box_id' => $add_id
        ]);
      }

      DB::commit();
    } catch (Exception $e) {
      DB::rollback();
      return response()->json([ 'status' => false, 'message' => $e->getMessage()], 401);
    }

    return response()->json(['status' => true, 'message' => 'Create request add item success.', 'data' => new AddItemBoxResource($additembox)]);
  }

  public function cancelAdditem($id)
  {
    $status = 24; // cancelled
    try {

      $change = AddItemBox::find($id);
      if (empty($change)) {
         return response()->json([ 'status' => false, 'message' => 'Data Not found!'], 422);
      }

      if ($change->status != 14 || $change->status != 25) {
         return response()->json([ 'status' => false, 'message' => 'Request can\'t cancelled.'], 422);
      }

     $change->status = 24; // cancelled
     $change->save();
    } catch (\Exception $x) {
       return response()->json([ 'status' => false, 'message' => $x->getMessage()], 401);
    }

    return response()->json(['status' => true, 'message' => 'Cancel request change box success.', 'data' => null]);
  }




}
