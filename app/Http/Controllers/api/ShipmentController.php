<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShipmentResource;
use App\models\AutoGenerate;
use App\Notifications\ShipmentNoty;
use App\Pincode;
use App\Product;
use App\Rows;
use App\Shipment;
use App\User;
use Illuminate\Http\Request;
use Notification;
use App\ShipmentStatus;

class ShipmentController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        if ($user->hasRole('Client')) {
            // return 'Client';
            $shipment = Shipment::where('client_id', $user->id)->orderBy('id', 'desc')->latest()->paginate(100);
        } else {
            $shipment = Shipment::where('country_id', $user->country_id)->orderBy('id', 'desc')->latest()->paginate(100);
        }
        return ShipmentResource::collection($shipment);
    }

    public function show($id)
    {
        return ShipmentResource::collection(Shipment::where('id', $id)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

            $row = new Rows();
            $row->user_id = 1;
            $row->text = serialize($request->all());
            $row->save();
            // return $row;
        // return $request->all();
        $data = $request->data;
        // $shipment = new Shipment();
        // $shipment->description = serialize($data);
        // $shipment->save();
        // return response()->json(['success','status' => '200'], '200');
        // return 'success';
        // return $data['from']['from_name'];

        $products = $data['line_items'];
        // return $products;
        $amount_ordered = 0;
        $total_price = 0;
        foreach ($products as $product_item) {
            $amount_ordered += $product_item['quantity'];
            $total_price += $product_item['total'];
        }
        // return($total_price);
        // return($amount_ordered);

        // $from = $data['from'];
        $to = $data['to'];
        $recepient = $data['recepient'];
        $sender = $data['sender'];
        // $delivery_details = $data['delivery_details'];
        // dd($from, $to);

        // $from_name = $from['from_name'];
        // $from_lat = $from['from_lat'];
        // $from_long = $from['from_long'];
        // $from_description = $from['from_description'];

        $to_name = $to['to_name'];
        $to_lat = (array_key_exists('to_lat', $to)) ? $to['to_lat'] : null;
        $to_long = (array_key_exists('to_long', $to)) ? $to['to_long'] : null;
        // $to_long = $to['to_long'];
        $to_description = $to['to_description'];

        $recepient_phone = $recepient['recepient_phone'];
        $recepient_name = $recepient['recepient_name'];
        $recepient_email = $recepient['recepient_email'];
        $recepient_notes = $recepient['recepient_notes'];

        $sender_name = $sender['sender_name'];
        $sender_phone = $sender['sender_phone'];
        $sender_email = $sender['sender_email'];
        $sender_notes = $sender['sender_notes'];

        // $pick_up_date = $delivery_details['pick_up_date'];

        // return $request->all();
        // $user_data = auth('api')->user();
        $user_id = 1;
        $shipment = new Shipment;
        // $shipment->client_id = 1;
        // $shipment->branch_id = 1;
        $shipment->client_name = $recepient_name;
        $shipment->client_phone = $recepient_phone;
        $shipment->client_email = $recepient_email;
        $shipment->client_address = $to_description;
        // $shipment->client_city = $request->client_city;
        $shipment->airway_bill_no = $request->airway_bill_no;
        $shipment->country_id = 1;
        $shipment->booking_date = now();
        // $shipment->derivery_date = $pick_up_date;
        $shipment->printed = false;
        $shipment->amount_ordered = $amount_ordered;
        // $shipment->pick_up_date = $pick_up_date;
        // $shipment->bar_code = $request->bar_code;
        $shipment->to_city = $to_name;
        $shipment->client_city = $to_name;
        $shipment->cod_amount = $request->cod_amount;
        // $shipment->from_city = $from_name;
        // $shipment->sender_city = $from_name;
        $shipment->user_id = $user_id;
        // $shipment->vendor = $from_name;

        // $bar_code = new AutoGenerate;
        // $shipment->bar_code = $bar_code->airwaybill_no();
        // $shipment->airway_bill_no = $bar_code->airwaybill_no();


        $shipment->bar_code = 'BL_001';
        $shipment->airway_bill_no = 'BL_001';

        $shipment->sender_name = $sender_name;
        $shipment->sender_email = $sender_email;
        $shipment->sender_phone = $sender_phone;

        // $shipment->lat = $from_lat;
        // $shipment->lng = $from_long;
        $shipment->lng_to = $to_long;
        $shipment->lat_to = $to_lat;
        // $total_price
        $shipment->cod_amount = $total_price;
        // $shipment->sender_address = $sender_address;
        // $shipment->sender_city = $sender_city;

        // return $user_id;
        $shipment->shipment_id = random_int(1000000, 9999999);
        $shipment->save();

        foreach ($products as $product_item) {
            $product = new Product();
            $product->lat_from = $product_item['from_lat'];
            $product->long_from = $product_item['from_long'];
            $product->product_name = $product_item['name'];
            $product->vendor = $product_item['from_name'];
            $product->price = $product_item['price'];
            $product->total = $product_item['total'];
            $product->quantity = $product_item['quantity'];
            $product->user_id = 1;
            $product->shipments_id = $shipment->id;
            $product->save();
        }

        // $users = $this->getAdmin();
        // $type = 'shipment';
        // Notification::send($users, new ShipmentNoty($shipment, $type));
        return response()->json(['success', 'status' => '200'], '200');
        // return ShipmentResource::collection($shipment);
        // die();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Shipment  $shipment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // if ($products->isEmpty()) {
        //     return response()->json([
        //         'product_empty' => ['One or more products is required'],
        //     ], 422);
        // }
        $shipment = Shipment::where($id)->paginate(500);
        if ($request->selectCl == []) {
            // $shipment->client_id = null;
            // dd('nn');
        } else {
            $shipment->client_id = $request->selectCl['id'];
            // dd( $request->selectCl['id']);
        }
        if ($request->selectD == []) {
            // $shipment->driver = '';
            // dd('renn');
        } else {
            $shipment->driver = $request->selectD['id'];
            // dd( $request->selectD['id']);
        }

        if ($request->selectB == []) {
            // $shipment->branch_id = Auth::user()->branch_id;
            // dd(Auth::user()->branch_id);
        } else {
            $shipment->branch_id = $request->selectB['id'];
            // dd( $request->selectB['id']);
        }

        // $shipment->sub_total = $products->sum('total');
        $shipment->client_name = $request->form['client_name'];
        $shipment->client_phone = $request->form['client_phone'];
        $shipment->client_email = $request->form['client_email'];
        $shipment->client_address = $request->form['client_address'];
        $shipment->client_city = $request->form['client_city'];
        // $shipment->assign_staff = $request->form['assign_staff'];
        $shipment->airway_bill_no = $request->form['bar_code'];
        $shipment->shipment_type = $request->form['shipment_type'];
        $shipment->payment = $request->form['payment'];
        // $shipment->total_freight = $request->form['total_freight'];
        // $shipment->total = $request->form['total'];
        $shipment->insuarance_status = $request->form['insuarance_status'];
        $shipment->status = $request->form['status'];
        $shipment->booking_date = $request->form['booking_date'];
        $shipment->derivery_date = $request->form['derivery_date'];
        $shipment->derivery_time = $request->form['derivery_time'];
        $shipment->bar_code = $request->form['bar_code'];
        $shipment->to_city = $request->form['to_city'];
        $shipment->cod_amount = $request->form['cod_amount'];
        // $shipment->receiver_name = $request->form['receiver_name'];
        $shipment->from_city = $request->form['from_city'];

        // if ($request->model) {
        // $shipment->sender_name = $request->form['sender_name'];
        // $shipment->sender_email = $request->form['sender_email'];
        // $shipment->sender_phone = $request->form['sender_phone'];
        // $shipment->sender_address = $request->form['sender_address'];
        // $shipment->sender_city = $request->form['sender_city'];
        // } else {
        $shipment->sender_name = $request->form['sender_name'];
        $shipment->sender_email = $request->form['sender_email'];
        $shipment->sender_phone = $request->form['sender_phone'];
        $shipment->sender_address = $request->form['sender_address'];
        $shipment->sender_city = $request->form['sender_city'];
        // }

        // return $request->form['customer_id'];
        $shipment->user_id = $user->id;
        $shipment->shipment_id = random_int(1000000, 9999999);
        // $shipment->branch_id = Auth::user()->branch_id;
        $shipment->save();
        $users = $this->getAdmin();
        // if ($shipment->save()) {
        //     $shipment->products()->saveMany($products);
        // }
        // Notification::send($users, new ShipmentNoty($shipment));
        // $users->notify(new ShipmentNoty($shipment));
        return ShipmentResource::collection($shipment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Shipment  $shipment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shipment $shipment)
    {
        Shipment::find($shipment->id)->delete();
    }

    public function getAdmin()
    {

        $users = User::all();
        $admin = [];
        foreach ($users as $user) {
            if ($user->hasRole('Admin')) {
                $admin[] = $user;
            };
        }
        return $admin;
        // $users = User::all();
        // return $users->hasRole('Admin');
        // $userArr = [];
        // foreach ($usersRolem as $user) {
        //     // var_dump($user->roles); die;
        //     foreach ($user->roles as $role) {
        //         if ($role->name == 'Admin') {
        //             $userArr[] = $role->id;
        //         }
        //     }
        // }
        // $users = $userArr;
        // return $admin = User::whereIn('id', $userArr)->paginate(500);
        // return UserResource::collection($admin);
    }

    public function delete(Request $request)
    {
        // $api_user = new ApiUser();
        // $user_data = $api_user->login($request);
        $user_data = auth('api')->user();

        if (empty($user_data) || isset($user_data['error'])) {
            return response()->json(['error' => 'Unauthorised User', 'status' => '401'], 401);
        }

        if (empty($user_data['id']) || empty($request->get('awb_no'))) {
            return response()->json(['error' => 'Required Parameter Missing', 'status' => '400'], 400);
        }

        $data = Shipment::where(['user_id' => $user_data['id'], 'airway_bill_no' => $request->get('awb_no')])->select()->get()->toArray();
        if (empty($data)) {
            return response()->json(['error' => 'Awb Number Does not exsist', 'status' => '200'], 200);
        }

        Shipment::where(['user_id' => $user_data['id'], 'airway_bill_no' => $request->get('awb_no')])->delete();

        return response()->json(['success' => 'Tracking number sucessfully deleted', 'status' => '200'], 200);
    }

    public function track(Request $request)
    {
        $user_data = auth('api')->user();

        if (empty($user_data) || isset($user_data['error'])) {
            return response()->json(['error' => 'Unauthorised User', 'status' => '401'], 401);
        }

        if (empty($user_data['id']) || empty($request->get('awb_no'))) {
            return response()->json(['error' => 'Required Parameter Missing', 'status' => '400'], 400);
        }

        $data = Shipment::where(['user_id' => $user_data['id'], 'airway_bill_no' => $request->get('awb_no')])->select()->get()->first();
        if (empty($data)) {
            return response()->json(['error' => 'Awb Number Does not exsist', 'status' => '200'], 200);
        }

        $status = !empty($data['status']) ? $data['status'] : 'No Status Found';
        return response()->json(['success' => $status, 'status' => '200'], 200);
    }

    public function pincode(Request $request)
    {
        // return $request->all();
        $user_data = auth('api')->user();

        if (empty($user_data) || isset($user_data['error'])) {
            return response()->json(['error' => 'Unauthorised User', 'status' => '401'], 401);
        }

        if (empty($request->get('pincode'))) {
            return response()->json(['error' => 'Required Parameter Missing', 'status' => '400'], 400);
        }

        $pincode = Pincode::where(['pincode' => $request->get('pincode')])->select('cod', 'prepaid')->get()->first();

        if (empty($pincode)) {
            return response()->json(['error' => 'Pincode is not Available', 'status' => '200'], 200);
        }

        $pincode_data = array();
        $pincode_data['cod'] = !empty($pincode['cod']) ? $pincode['cod'] : 0;
        $pincode_data['prepaid'] = !empty($pincode['prepaid']) ? $pincode['prepaid'] : 0;

        return response()->json(['success' => $pincode_data, 'status' => '200'], 200);
    }

    public function createPickup(Request $request)
    {

        $user_data = auth('api')->user();

        $response = array();
        if (empty($user_data) || isset($user_data['error'])) {
            return response()->json(['error' => 'Unauthorised User', 'status' => '401'], 401);
        }

        if (empty($request->get('request'))) {
            return response()->json(['error' => 'Required Parameter Missing', 'status' => '400'], 400);
        }
        foreach ($request->get('request') as $key => $value) {

            $response[$key]['status'] = 0;

            if (empty($value['awb_no']) || empty($value['pickup_at'])) {
                continue;
            }

            $response[$key]['awb_no'] = $value['awb_no'];

            $data = Shipment::where(['user_id' => $user_data['id'], 'airway_bill_no' => $value['awb_no']])->select()->get()->first();

            if (empty($data)) {
                continue;
            } elseif (!empty($data['pickup_id'])) {
                $response[$key]['pickup_id'] = $data['pickup_id'];
                continue;
            }

            $pickup_time = strtotime($value['pickup_at']);

            $pickup_id = Shipment::max('pickup_id');
            $pickup_id = !empty($pickup_id) ? $pickup_id + 1 : 1;

            Shipment::where(['user_id' => $user_data['id'], 'airway_bill_no' => $value['awb_no']])->update(['pickup_id' => $pickup_id, 'pickup_at' => $pickup_time]);

            $response[$key]['pickup_id'] = $pickup_id;
            $response[$key]['status'] = 1;
        }

        return response()->json(['success' => json_encode($response), 'status' => '200'], 200);
    }

    public function deletePickup(Request $request)
    {
        return $request->all();
        return $request['awb_no'];
        $user_data = auth('api')->user();

        $response = array();
        if (empty($user_data) || isset($user_data['error'])) {
            return response()->json(['error' => 'Unauthorised User', 'status' => '401'], 401);
        }

        if (empty($request->get('awb_no'))) {
            return response()->json(['error' => 'Required Parameter Missing', 'status' => '400'], 400);
        }

        $data = Shipment::where(['user_id' => $user_data['id'], 'airway_bill_no' => $request->get('awb_no')])->select()->get()->first();
        if (empty($data)) {
            return response()->json(['error' => 'Awb Number Does not exsist', 'status' => '200'], 200);
        }

        Shipment::where(['user_id' => $user_data['id'], 'airway_bill_no' => $request->get('awb_no')])->update(['pickup_id' => '', 'pickup_at' => '']);

        return response()->json(['success' => 'Sucessfully Deleted', 'status' => '200'], 200);
    }

    public function glSearch(Request $request)
    {

        // return response()->json(['data' => 'dwdwdwdw', 'status' => '200'], 200);
        $search = $request->data['search'];
        $user = auth('api')->user();
        $shipments = Shipment::where('bar_code', 'LIKE', "%{$search}%")
            ->where('client_id', $user->id)
            ->orwhere('client_phone', 'LIKE', "%{$search}%")
            ->orwhere('client_email', 'LIKE', "%{$search}%")
            ->orwhere('client_name', 'LIKE', "%{$search}%")->paginate(500);
        return ShipmentResource::collection($shipments);
        // return response()->json(['data' => $shipments, 'status' => '200'], 200);
    }

    public function apiSearch($search)
    {

        // $search = $request->data['search'];
        $user = auth('api')->user();
        // return response()->json(['data' => $user, 'status' => '200'], 200);
        return $shipments = Shipment::where('bar_code', 'LIKE', "%{$search}%")
            // ->where('client_id', $user->id)
            ->orwhere('client_phone', 'LIKE', "%{$search}%")
            ->orwhere('client_email', 'LIKE', "%{$search}%")
            ->orwhere('client_name', 'LIKE', "%{$search}%")->paginate(500);
        return ShipmentResource::collection($shipments);
        // return response()->json(['data' => $shipments, 'status' => '200'], 200);
    }

    // public function status($id)
    // {

    //     // return $id;
    //     // $search = $request->search;
    //     $shipments = ShipmentStatus::where('shipment_id', $id)->paginate(500);
    //     // return ShipmentResource::collection($shipments);
    //     // $colors = array(
    //     //     'name' => 'cyan',
    //     //     'name' => 'green',
    //     //     'name' => 'pink',
    //     //     'name' => 'pink',
    //     //     'name' => 'amber',
    //     //     'name' => 'orange',
    //     // );
    //     $shipments->transform(function ($shipment) {
    //         $random_color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    //         $shipment->color = $random_color;
    //         return $shipment;
    //     });
    //     return response()->json($shipments);
    // }
}
