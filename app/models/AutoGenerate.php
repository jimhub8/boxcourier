<?php

namespace App\models;

use App\Shipment;

class AutoGenerate
{
    public function airwaybill_no()
    {
        $shipments = Shipment::withTrashed()->select('airway_bill_no')->orderBy('id', 'Desc')->first();
        if ($shipments) {
            $inv_arr = explode('L', $shipments->airway_bill_no);
            $in_ar = (int) $inv_arr[1];
            // $id = ''.str_pad($product->id + 1, 8, "0", STR_PAD_LEFT);
            $airway_bill_no = ($shipments) ? 'BL' . str_pad($shipments->id + 1, 8, "0", STR_PAD_LEFT) : 'BL' . str_pad(1, 8, "0", STR_PAD_LEFT);
            $validator = \Validator::make(['airway_bill_no' => $airway_bill_no], ['airway_bill_no' => 'unique:shipments,airway_bill_no']);
            if ($validator->fails()) {
                for ($i = 1; $i < 50; $i++) {
                    $airway_bill_no = ($shipments) ? 'BL' . str_pad($in_ar + $i, 8, "0", STR_PAD_LEFT) : 'BL' . str_pad(1, 8, "0", STR_PAD_LEFT);
                    $validator = \Validator::make(['airway_bill_no' => $airway_bill_no], ['airway_bill_no' => 'unique:shipments,airway_bill_no']);
                    if (!$validator->fails()) {
                        return $airway_bill_no;
                    }
                }
            } else {
                return $airway_bill_no;
            }
        } else {
            return 'BL' . str_pad(1, 8, "0", STR_PAD_LEFT);
        }
    }
}

