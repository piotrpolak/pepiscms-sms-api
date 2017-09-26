<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * sms_api model
 *
 * @author piotr@polak.ro
 * @date 2017-09-22
 */
class Sms_api_model extends Array_model
{

    public function getBasicFeed($extra_param)
    {
        return array((object)array('id' => 1,
            'address' => '500500500',
            'body' => 'Hello World!',
            'is_incoming' => true,
            'date' => date('Y-m-d H:i:s'),
            'date_sent' => date('Y-m-d H:i:s')));
    }
}