<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * sms_api model
 *
 * @author piotr@polak.ro
 * @date 2017-09-22
 */
class Sms_api_model extends Array_model
{
    private $feedUrl;

    /**
     * @return mixed
     */
    public function getFeedUrl()
    {
        return $this->feedUrl;
    }

    /**
     * @param mixed $feedUrl
     */
    public function setFeedUrl($feedUrl)
    {
        $this->feedUrl = $feedUrl;
    }


    public function getBasicFeed($extra_param)
    {
        if (!$this->feedUrl) {
            throw new LogicException('feedUrl should be specified for Sms_api_model');
        }
        $contents = file_get_contents($this->feedUrl);
        return json_decode($contents);
    }

    public function sendMessage($address, $message)
    {
        return TRUE;
    }
}