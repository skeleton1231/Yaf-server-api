<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/10/19
 * Time: 上午8:56
 */


//状态码
define('STATUS_FAIL', 0);
define('STATUS_SUCCESS', 1);

//正常返回码
define('SUCCESS', 0);

//系统相关错误码
define('NOT_ENOUGH_ARGS', 40001);
define('NOT_ALLOWED_FORMAT', 40002);
define('NOT_AUTHORIZED', 40003);
define('NOT_FOUND', 40004);
define('MODULE_NOT_ALLOWED', 40005);
define('METHOD_NOT_ALLOWED', 40006);
define('APP_NOT_AUTHORIZED', 40007);



//app 注册
define('APP_REGISTER_EXIST' , 50001);
define('APP_REGISTER_FAIL' , 50002);

//user register
define('USER_CODE_ERROR' , 51000);
define('USER_MOBILE_REGISTERED' , 51001);
define('USER_REGISTER_FAIL' , 51002);
define('USER_NICKNAME_FORMAT_ERROR', 51003);
define('USER_LOGIN_FAIL' , 51004);
define('USER_AUTH_FAIL', 51005);
define('USER_PROFILE_KEY_ERROR', 51006);
define('USER_PROFILE_UPDATE_FAIL', 51007);


//car
define('CAR_ALREADY_ADDED',60001);
define('CAR_ADDED_ERROR',60002);
define('CAR_DRIVE_INFO_ERROR',60003);



//qiniu
define('QINIU_UPLOAD_ERROR' , 70001);


//item
define('ITEM_TYPE_CAR' , 1);
define('ITEM_TYPE_USER_AVATAR' , 2);
define('ITEM_TYPE_USER_POST' , 3);

//car 1:为本平台个人二手车辆，2为第三方平台二手车辆 3:用户车辆非卖 4:平台销售二手车
define('PLATFORM_USER_SELLING_CAR', 1);
define('OTHER_SELLING_CAR', 2);
define('PLATFORM_USER_OWNER_CAR', 3);
define('PLATFORM_SELLING_CAR', 4);


//car status 1:审核中 2:审核通过 3:审核失败 4:已卖出 5:已下架 11:已认证 12:未认证
define('CAR_VERIFYING', 1);
define('CAR_VERIFIED' , 2);
define('CAR_VERIFY_FAIL',3);
define('CAR_SELLED' , 4);
define('CAR_UNDERCARAGE', 5);


define('CAR_AUTH' , 11);
define('CAR_NOT_AUTH' , 12);




define('QI_NIU_AK','b2uNBag0oxn1Kh1-3ZaX2I8PUl_o2r19RWerT3yI');
define('QI_NIU_SK','5RgnuN64dSEJoitJwUvMkMGypaifc9PqSOvjYd2f');

define('IMAGE_DOMAIN' , 'http://7xopqk.com1.z0.glb.clouddn.com/');




