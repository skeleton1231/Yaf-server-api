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
        ORDER_BY_DEFAULT        => ' ORDER BY t1.updated DESC , t1.car_type ASC, t1.mileage ASC, t1.board_time ASC, t1.price ASC ',
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

/*
     'buycar_info'=>array(
        'price'=>0,
        'base_insurance'=>'',
        'down_payment'=>array(
         '30%','40%','50%','60%',
        ),//首付比例
        'age_limit'=>array(
         '1'=>"2%",
         '2'=>"3%",
         '3'=>"4%",
         '4'=>"4%",
         '5'=>"5%",
        ),//贷款年限
        'tax'=>array(
            'purchase_tax'=>'10%',//购置税 购车款/(1+17%)×购置税率(10%)
            'licence_fee'=>500,//上牌费用
            'usage_tax'=>array(
             '1.0L(含以下)'=>150,
             '1.0-1.6L'=>210,
             '1.6-2.0L'=>240,
             '2.0-2.5L'=>450,
             '2.5-3.0L'=>960,
             '3.0-4.0L'=>1740,
             '4.0L(以上)'=>2640,
             ''
             ),//车船使用税
            'coercive_insurance'=>array(
             '家用6座以下'=>950,
             '家用6座以上'=>1100,
            ),//交强险
        ),
         'insurance'=>array(
            'third'=>array(
            '5万'=>659,
            '10万'=>928,
            '15万'=>1048,
            '20万'=>1131,
            '30万'=>1266,
            '50万'=>1507,
            '100万'=>1963,
            ),//第三者责任险
            'car_lose'=>array(
             'base'=>200,
             'fee_rate'=>"1.0880%",
            ),//车辆损失险
            'abatement'=>"",//不计免赔特约险 (车辆损失险+第三者责任险)×20% 
            'theft'=>array(
             'base'=>"300",// 基础保费
             'fee_rate'=>"1.0880%",// 费率
             ),//全车盗抢险
            'Broken_glass'=>array(
            'import_glass'=>"0.25%",//进口
            'domestic_glass'=>"0.15%",//国产
            ),//玻璃单独破碎险
            'Spontaneous'=>"0.15%",//自燃损失
            'Wading'=>"", //涉水险
            'Scratch'=>array(
            '2千'=>850,
            '5千'=>1100,
            '1万'=>1500,
            '2万'=>2250,
            ),//划痕险
            'Driver'=>array(
            '1万'=>42,
            '2万'=>84,
            '3万'=>126,
            '4万'=>168,
            '5万'=>210,
            ),//司机责任险
            'Passenger'=>array(
            '1万'=>27,
            '2万'=>54,
            '3万'=>81,
            '4万'=>108,
            '5万'=>135,
            ),//乘客责任险
        )

    ),
*/

);

