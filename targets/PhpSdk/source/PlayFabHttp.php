<?php

if (!class_exists("PlayFabSettings")) {
    class PlayFabSettings
    {
        public static $PlayFabApiUrl = "https://{titleId}.playfabapi.com";
        public static $versionString = "PhpSdk-1.0.170111";
        public static $enableCompression = false; // Can't get this to work
        public static $titleId = null;
        public static $disableSsl = false;

        public static function GetFullUrl($titleId, $apiPath)
        {
            if (!isset($titleId) && isset(PlayFabSettings::$titleId))
                $titleId = PlayFabSettings::$titleId;

            $output = PlayFabSettings::$PlayFabApiUrl . $apiPath;
            $output = str_replace("{titleId}", $titleId, $output);
            return $output;
        }
    }
}

if (!class_exists("PlayFabHttp")) {
    class PlayFabHttp
    {
        public static function MakeCurlApiCall($titleId, $apiPath, $request, $authKey, $authValue)
        {
            $fullUrl = PlayFabSettings::GetFullUrl($titleId, $apiPath);
            $payload = json_encode($request);

            // ApiCall Headers
            $httpHeaders = [
                "Content-Type: application/json",
                "X-ReportErrorAsSuccess: true",
                "X-PlayFabSDK: " . PlayFabSettings::$versionString
            ];
            if (isset($authKey) && isset($authValue))
                array_push($httpHeaders, $authKey . ": " . $authValue);

            // Compression
            if (PlayFabSettings::$enableCompression) {
                array_push($httpHeaders, "Content-Encoding: GZIP");
                array_push($httpHeaders, "Accept-Encoding: GZIP");
                $payload = gzcompress($payload);
            }

            // Perform the call
            $ch = curl_init($fullUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (PlayFabSettings::$disableSsl) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // TODO: DON'T PUBLISH WITH THIS
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // TODO: DON'T PUBLISH WITH THIS
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
            $rawResult = curl_exec($ch);
            
            if ($rawResult === false)
                echo "cUrl Error: " . curl_error($ch) . "\n\n";
            
            curl_close($ch);
            
            
            return $rawResult;
        }

        // public static function MakeHttpRequestApiCall($titleId, $apiPath, $request, $authKey, $authValue)
        // {
            // $fullUrl = PlayFabSettings::GetFullUrl($titleId, $apiPath);
            // $httpRequest = new HttpRequest($fullUrl, HttpRequest::METH_POST);
            // $requestJson = json_encode($request);

            // // ApiCall Headers
            // $httpHeaders = [
                // "Content-Type" => "application/json",
                // "X-ReportErrorAsSuccess" => "true",
                // "X-PlayFabSDK" => PlayFabSettings::$versionString
            // ];
            // if ($authKey && $authValue)
                // $httpHeaders[$authKey] = $authValue;

            // // Compression
            // if (PlayFabSettings::$enableCompression) {
                // $httpHeaders[$authKey] = $authValue;
                // $httpHeaders["Content-Encoding"] = "GZIP";
                // $httpHeaders["Accept-Encoding"] = "GZIP";
                // $requestJson = gzcompress($requestJson);
            // }

            // // Perform the call
            // $httpRequest->addHeaders($httpHeaders);

            // try {
                // $httpMessage = $httpRequest->send();
                // $responseHeaders = $httpMessage->getHeaders();
                // $responseJson = $httpMessage->getBody();

                // $responseEncoding = $responseHeaders["Content-Encoding"];
                // if (isset($responseEncoding) && $responseEncoding === "GZIP")
                    // $responseJson = gzdecode($responseJson);
                // return $responseJson;
            // } catch (HttpException $ex) {
                // return $ex;
            // }
        // }
    }
}

?>
