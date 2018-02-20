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
     * @var int
     */
    private $maxFeedResults = 30;

    /**
     * @return string
     */
    public function getFeedUrl()
    {
        return $this->feedUrl;
    }

    /**
     * @param string $feedUrl
     * @return Sms_api_model
     */
    public function setFeedUrl($feedUrl)
    {
        $this->feedUrl = $feedUrl;
        return $this;
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
     * @return Sms_api_model
     */
    public function setSendUrl($sendUrl)
    {
        $this->sendUrl = $sendUrl;
        return $this;
    }

    /**
     * @param int $maxFeedResults
     * @return Sms_api_model
     */
    public function setMaxFeedResults($maxFeedResults)
    {
        $this->maxFeedResults = $maxFeedResults;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxFeedResults()
    {
        return $this->maxFeedResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicFeed($extra_param)
    {
        if (!$this->feedUrl) {
            throw new LogicException('feedUrl should be specified for Sms_api_model');
        }

        $contents = file_get_contents($this->getFullFeedUrl());
        $contents_decoded = $this->decodeAndValidate($contents);

        return $this->convertResponse($contents_decoded->result);
    }

    /**
     * Send message to the specified address (phone number).
     *
     * @param $address
     * @param $message
     * @return bool
     * @throws Exception
     */
    public function sendMessage($address, $message)
    {
        $contents = $this->makePost(array(
            'to' => "+" . $address,
            'message' => $message
        ), $this->getSendUrl());

        $this->decodeAndValidate($contents);

        return TRUE;
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

    /**
     * @param $contents
     * @return mixed
     * @throws Exception
     */
    private function decodeAndValidate($contents)
    {
        $contents_decoded = json_decode($contents);

        if (!isset($contents_decoded->status)) {
            throw new Exception("Received a malformed response");
        }

        if ($contents_decoded->status != 200) {
            throw new Exception("Received a valid response with incorrect status code");
        }
        return $contents_decoded;
    }

    /**
     * @param $result
     * @return mixed
     */
    private function convertResponse($result)
    {
        foreach ($result as &$item) {
            $item->date_sent = $this->toDatabaseDate($item->date_sent);
            $item->date = $this->toDatabaseDate($item->date);
            $item->is_incoming = $item->is_incoming ? 1 : 0;
        }

        return $result;
    }

    /**
     * @param $date
     * @return false|int
     */
    private function toDatabaseDate($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * @return string
     */
    private function getFullFeedUrl()
    {
        if (!$this->getMaxFeedResults()) {
            return $this->getFeedUrl();
        }
        return $this->getFeedUrl() . '?maxResults=' . $this->getMaxFeedResults();
    }
}