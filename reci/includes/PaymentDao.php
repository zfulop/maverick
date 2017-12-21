<?php

class PaymentDao
{

    /**
     *
     * Gets the sum payment for a booking description id, converts to $ouput_currency on the given day
     * @param $filter array:
     *      - booking_description_ids: array[booking_description_id]['day'] = '2017-10-04';
     *      - storno: int, for ex: 0
     *      - types: array: for ex: ['Szobabevétel','IFA / City Tax']
     *
     * @param $ouput_currency: EUR/HUF
     * @return array[booking_description_id] = payment_amount;
     */
    public static function getPayments($filter,$ouput_currency,$link){

        //extracts the booking description ids from the array
        $bd_ids = array();
        if (!empty($filter['booking_description_ids'])){
            foreach ($filter['booking_description_ids'] as $curr_id=>$curr_val){
                $bd_ids[] = $curr_id;
            }
        }

        $sql = "select booking_description_id,currency,sum(amount) as sum_amount 
                from payments 
                where 1=1 ";

        if (!empty($bd_ids)){
            $sql .= " and booking_description_id in (".implode(',',$bd_ids).")";
        }

        if (isset($filter['storno'])){
            $filter['storno'] = (int) $filter['storno'];
            $sql .= " and storno = ".$filter['storno'];
        }

        if (!empty($filter['types'])){
            $types = array();
            foreach ($filter['types'] as $curr_type){
                $types[] = "'".addslashes($curr_type)."'";
            }
            $sql .= " and `type` in (".implode(',',$types).")";
        }

        if (!empty($filter['pay_modes'])){
            $modes = array();
            foreach ($filter['pay_modes'] as $mode){
                $modes[] = "'".addslashes($mode)."'";
            }
            $sql .= " and `pay_mode` in (".implode(',',$modes).")";
        }


        $sql .=" group by booking_description_id, currency ";

//        echo "Payment SQL:<pre>". $sql."</pre>";
        $result = mysql_query($sql, $link);

        $sum_payments = array();
        while($row = mysql_fetch_assoc($result)) {
            $curr_booking_desc_id = $row['booking_description_id'];

            $curr_amount = $row['sum_amount'];

            if ($row['currency']!=$ouput_currency) {
                $curr_amount = round(convertAmount($curr_amount,$row['currency'],$ouput_currency,$filter['booking_description_ids'][$curr_booking_desc_id]['day']));
            }

            if (empty($sum_payments[$curr_booking_desc_id])){
                $sum_payments[$curr_booking_desc_id] = $curr_amount;
            } else {
                $sum_payments[$curr_booking_desc_id] += $curr_amount;
            }
        }

        return $sum_payments;
    }

}