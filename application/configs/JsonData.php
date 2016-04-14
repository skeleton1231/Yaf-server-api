<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/11/29
 * Time: 下午6:22
 */

return array(

    'city_info'  =>  array(
        'city_id'=>93,
        'city_name'=>'深圳',
        'city_lat'=>360,
        'city_lng'=>360
    ),

    'order_info' =>  array(
        ORDER_BY_DEFAULT        => ' ORDER BY t1.car_type ASC, t1.mileage ASC, t1.board_time ASC, t1.price ASC ',
        ORDER_BY_PRICE_ASC      => ' ORDER BY t1.price ASC',
        ORDER_BY_PRICE_DESC     => ' ORDER BY t1.price DESC',
        ORDER_BY_BOARD_TIME_ASC => ' ORDER BY t1.board_time ASC',
        ORDER_BY_MILEAGE_ASC    => ' ORDER BY t1.mileage ASC',

    ),

    'user_info'=>array(
        'user_id'=>0,
        'username'=>'',
        'mobile'=>'',
        'created'=>0,
        'profile'=>array(
            'nickname'=>'',
            'gender'=>0,
            'signature'=>'',
            'age'=>0,
            'constellation'=>''
        )

    ),


);

