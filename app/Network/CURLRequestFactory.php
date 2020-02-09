<?php


namespace App\Network;


use App\Network\Contract\Request;
use App\Network\CURL\Contract\Wrapper;
use App\Network\Utils\CURLCommon;

class CURLRequestFactory implements \App\Network\Contract\RequestFactory
{
    use CommonOption;

    use CommonHeader;

    private $wrapper;

    public function __construct()
    {
        $this
            ->addCommonOption(CURLOPT_RETURNTRANSFER, true)
        ;
    }

    public function setWrapper(Wrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    public function make($path, $data = null, $headers = null, $setOptions = null)
    {
        $ch = curl_init($path);
        // Set common options
        curl_setopt_array($ch, $this->commonOptions);
        // Set postfields
        $additionalHeaders = CURLCommon::setPostfieldsIfNeed($ch, $data);
        $cURLAPIRequest = new CURLRequest($ch);
        // Add headers
        $cURLAPIRequest->setHeaderLists($this->commonHeaders, $additionalHeaders);
        if (is_callable($setOptions)) {
            $setOptions($ch);
        }
        return $cURLAPIRequest;
    }
}
