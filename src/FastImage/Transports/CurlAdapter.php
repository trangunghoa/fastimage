<?php namespace FastImage\Transports;

/**
 * Class CurlAdapter
 *
 * Not as fast since we just get the first 32768 bytes regardless
 * of image, but a little bit more stable
 *
 * @package FastImage\Transports
 * @author  Will Washburn <will@willwashburn.com>
 */
class CurlAdapter implements TransportInterface {

    /**
     * The curl handle
     *
     * @var array  curl resources
     */
    protected $handles = array();
    /**
     * @var int
     */
    protected $strpos = 0;

    /**
     * The string from the file
     *
     * @var string
     */
    protected $str = '';

    /**
     * The bits from the image
     *
     * @var string
     */
    protected $data;

    /**
     * @var int
     */
    protected $timeout = 10;

    /**
     * How much of the image to curl
     * @var int
     */
    protected $range = 32768;

    /**
     * @param int $range
     */
    public function __construct($range = null)
    {
        $this->range = is_null($range) ? $this->range : $range;
    }

    /**
     * Opens the connection to the file
     *
     * @param $url
     *
     * @throws Exception
     *
     * @return $this;
     */
    public function open($url)
    {

        $this->handles[$url] = $this->getCurlHandle($url);
        $data                = curl_exec($this->handles[$url]);

        if (curl_errno($this->handles[$url])) {
            throw new Exception(curl_error($this->handles[$url]), curl_errno($this->handles[$url]));
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Closes the connection to the file
     *
     * @return $this
     */
    public function close()
    {
        foreach ($this->handles as $handle) {
            curl_close($handle);
        }

        return $this;
    }

    /**
     * Reads more characters of the file
     *
     * @param $characters
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function read($characters)
    {
        if (!is_numeric($characters)) {
            throw new \InvalidArgumentException('"Read" expects a number');
        }

        $response = null;

        if ($this->strpos > strlen($this->data) ){

            throw new Exception(
                'Not enough of the file was curled.' .
                'Try increasing the range in the curl request'
            );

        }

        $result = substr($this->data, $this->strpos, $characters);
        $this->strpos += $characters;

        return $result;
    }

    /**
     * Resets the pointer where we are reading in the file
     *
     * @return mixed
     */
    public function resetReadPointer()
    {
        $this->strpos = 0;
    }

    /**
     * @param $seconds
     *
     * @return $this
     */
    public function setTimeout($seconds)
    {
        $this->timeout = floatval($seconds);

        return $this;
    }

}