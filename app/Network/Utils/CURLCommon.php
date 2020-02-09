<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-3
 * Time: 下午2:38
 */

namespace App\Network\Utils;


abstract class CURLCommon
{
    public static function setPostfieldsIfNeed($ch, $data = null, $asJSON = false)
    {
        $additionalHeaders = [];

        if (is_null($data))
            return $additionalHeaders;
        if (is_string($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            return [];
        } else {
            if ($asJSON) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                return [
                    "Content-Type" => "application/json",
                ];
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                return [];
            }
        }
    }

    /**
     * @param resource $ch
     * @param array ...$headerLists
     */
    public static function setHTTPHeaders($ch, ... $headerLists)
    {
        $headers = [];
        foreach ($headerLists as $headerList) {
            foreach ($headerList as $key => $value) {
                $headers[] = sprintf("%s: %s", $key, $value);
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
}
