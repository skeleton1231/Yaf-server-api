<?php
/**
 * Created by PhpStorm.
 * User: huanghaitao
 * Date: 15/12/15
 * Time: 下午12:52
 */

/*
 * 推送类,包括推送具体实现的方法,包括ios以及android
 */
class PushNotification {

    // For mac.
    private $_macApnsHost = 'gateway.sandbox.push.apple.com'; // Url of live server: gateway.push.apple.com,sandbox: gateway.sandbox.push.apple.com
    private $_macApnsPort = 2195;
    private $_macApnsCert = '';
    private $_passphrase = '123456';


    public  $_debug =1;
    private $apns = '';

    // @param: $app, the application name, to ask related file for iphone push service.
    // example: app = gigatalk, macApnsCert = apns-gigatalk.pem.
    public function __construct($debug = 1) {
        $this->_debug = $debug;
    }


    public function stream_connect() {
        $streamContext = stream_context_create ();

        stream_context_set_option ( $streamContext, 'ssl', 'local_cert', $this->_macApnsCert );
        stream_context_set_option ( $streamContext, 'ssl', 'passphrase', $this->_passphrase );

        $this->apns = stream_socket_client ( 'ssl://' . $this->_macApnsHost . ':' . $this->_macApnsPort, $error, $errorString, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $streamContext );
        Common::globalLogRecord('_macApnsHost', $this->_macApnsHost);
        Common::globalLogRecord('_macApnsCert', $this->_macApnsCert);
        if (! $this->apns) {
            Common::globalLogRecord ( 'apns-error', $errorString );
            echo 'error';
            exit ();
        }
    }

    public function stream_close() {

        @socket_close ( $this->apns );
        fclose ( $this->apns );
        Common::globalLogRecord('stream_close', 'stream_close');
    }


    public function push_ios($device, $message, $badge = 1, $level = 1, $ext = array() ,$iosPushType = 1) {

        switch ($iosPushType) {//app store

            case 2:
                $this->_macApnsCert = __DIR__ . '/../pem/BiBi_Pro_APNS.pem';
                $this->_macApnsHost = 'gateway.push.apple.com';

                break;

            case 1: //debug
                $this->_macApnsCert = __DIR__ . '/../pem/BiBi_Dev_APNS.pem';
                $this->_macApnsHost = 'gateway.sandbox.push.apple.com';
                break;

            default:
                break;
        }

        $this->stream_connect();

        $body = array ();

        $body ['aps'] = array (
            'alert' => $message,
            'sound' => 'default',
            "badge" => $badge
        );

        if (! empty ( $ext )) {
            $body = array_merge ( $body, $ext );
        }

        $payload = Common::arrToJson ( $body );

        $msg = @chr ( 0 ) . @pack ( "n", 32 ) . @pack ( 'H*', str_replace ( ' ', '', $device ) ) . @pack ( "n", strlen ( $payload ) ) . $payload;

        $rs = @fwrite ( $this->apns, $msg );

        Common::globalLogRecord ( 'apns-host', $this->_macApnsHost );
        Common::globalLogRecord ( 'apns-token', $device );
        Common::globalLogRecord ( 'apns-length', $rs );
        Common::globalLogRecord ( 'apns-level', $level );
        Common::globalLogRecord ( 'apns-content', $payload );
        common::globalLogRecord ( 'apns-badage', $badge);

        $this->stream_close();

        return $rs;
    }

}