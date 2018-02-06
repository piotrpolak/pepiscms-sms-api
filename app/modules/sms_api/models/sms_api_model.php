<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sms_api_model
 */
class Sms_api_model extends Array_model
{
    /**
     * @var string
     */
    private $feedUrl;

    /**
     * @var string
     */
    private $sendUrl;

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
     * @return string
     */
    public function getSendUrl()
    {
        return $this->sendUrl;
    }

    /**
     * @param string $sendUrl
     */
    public function setSendUrl($sendUrl)
    {
        $this->sendUrl = $sendUrl;
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
     * Send message to the specified address (phone number).
     *
     * @param $address
     * @param $message
     * @return bool
     */
    public function sendMessage($address, $message)
    {
        return $this->makePost(array(
            'to' => $address,
            'message' => $message
        ), $this->sendUrl);
    }

    /**
     * Helper method responsible for making post requests
     * @param $data
     * @return bool|string
     */
    private function makePost($data, $url)
    {
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data)
            )
        );

        $context = stream_context_create($opts);

        return file_get_contents($url, false, $context);
    }
}