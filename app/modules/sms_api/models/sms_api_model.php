<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Sms_api_model
 */
class Sms_api_model extends Array_model
{
    /**
     * @var string
     */
    private $feedUrl;

    /**
     * @return string
     */
    public function getFeedUrl()
    {
        return $this->feedUrl;
    }

    /**
     * @param string $feedUrl
     */
    public function setFeedUrl($feedUrl)
    {
        $this->feedUrl = $feedUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicFeed($extra_param)
    {
        if (!$this->feedUrl) {
            throw new LogicException('feedUrl should be specified for Sms_api_model');
        }
        $contents = file_get_contents($this->feedUrl);
        return json_decode($contents);
    }

    /**
     * Send message to the specified address (phone number)
     *
     * @param $address
     * @param $message
     * @return bool
     */
    public function sendMessage($address, $message)
    {
        return TRUE;
    }
}