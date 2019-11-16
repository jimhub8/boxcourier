<?php

namespace App\Http\Controllers;
// use Automattic\WooCommerce\Client;
// use Automattic\WooCommerce\HttpClient\HttpClientException;
use App\models\AutoGenerate;
use App\Notifications\ShipmentNoty;
use App\Product;
use App\Rows;
use App\Shipment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

// use Woocommerce;

// use Pixelpeter\Woocommerce\Facades\Woocommerce;
class WoocommerceController extends Controller
{
    public function woocommerce(Request $request)
    {
        if ($request->id) {
            Log::debug($request->all());
            // $row = new Rows();
            // $row->user_id = 1;
            // $row->text = serialize($request->all());
            // $row->save();
            // return $row;

            $products = $request->line_items;
            $amount_ordered = 0;
            foreach ($products as $product_item) {
                $amount_ordered += $product_item['quantity'];
            }

            $shipment = new Shipment();
            $shipment->client_id = 2;
            // $shipment->branch_id = 1;
            $shipping = $request->shipping;
            $billing = $request->billing;
            $name = ($shipping['first_name'] != null) ? $shipping['first_name'] . $shipping['last_name'] : $billing['first_name'] . $billing['last_name'];
            $shipment->client_name = $name;
            $shipment->client_phone = $billing['phone'];
            $shipment->client_email = $billing['email'];
            $shipment->client_address = ($billing['address_1']  != null) ? $billing['address_1'] : $shipping['address_1'];
            // $shipment->client_city = $request->client_city;
            // $shipment->airway_bill_no = $request->airway_bill_no;
            $shipment->country_id = 1;
            $shipment->booking_date = now();
            // $shipment->derivery_date = $pick_up_date;
            // $shipment->pick_up_date = $pick_up_date;
            // $shipment->bar_code = $request->bar_code;
            $shipment->to_city = ($billing['city']  != null) ? $billing['city'] : $shipping['city'];
            // $shipment->from_city = $from_name;
            $shipment->user_id = 1;
            // $shipment->vendor = $from_name;

            $bar_code = new AutoGenerate;
            $shipment->bar_code = 'BL_' . $request->id;
            $shipment->airway_bill_no = $bar_code->airwaybill_no();
            $shipment->sender_name = 'Shopizy Africa';
            $shipment->sender_email = 'info@shopizy.africa';
            $shipment->sender_phone = '0729143740';

            // $shipment->lat = $from_lat;
            // $shipment->lng = $from_long;
            // $shipment->lng_to = $to_long;
            // $shipment->lat_to = $to_lat;

            $shipment->amount_ordered = $amount_ordered;
            $shipment->status = $request->status;
            $shipment->cod_amount = $request->total;
            // $shipment->sender_address = $sender_address;
            // $shipment->sender_city = $sender_city;

            // return $user_id;
            $shipment->shipment_id = random_int(1000000, 9999999);
            $shipment->save();
            foreach ($products as $product_item) {
                $product = new Product();
                $product->product_name = $product_item['name'];
                // $product->weight = $product_item['weight'];
                $product->price = $product_item['price'];
                $product->total = $product_item['total'];
                $product->quantity = $product_item['quantity'];
                $product->user_id = 1;
                $product->shipments_id = $shipment->id;
                $product->save();
            }
            $users = $this->getAdmin();
            $type = 'shipment';
            Notification::send($users, new ShipmentNoty($shipment, $type));
            return response()->json(['success' => $shipment, 'status' => '200'], '200');
            // return Woocommerce::get('orders');
        } else {
            return;
        }
    }


    public function woo_orders()
    {
        $rows = Rows::all();
        foreach ($rows as $row) {
            $id = (unserialize($row->text));
            dd($id);
        }

        // return Woocommerce::get('orders');
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
    }
}
