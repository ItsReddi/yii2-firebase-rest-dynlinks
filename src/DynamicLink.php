<?php

namespace itsreddi\firebase;

use OtherCode\Rest\Core\Configuration;
use OtherCode\Rest\Rest;
use yii\base\Component;
use yii\base\InvalidCallException;

class DynamicLink extends Component
{

    public $apiKey;

    public $linkDomain;

    public $androidPackageName;

    public $iosBundleId;

    public $iosAppStoreId;

    public $enableForcedRedirect;

    public $suffixOption;

    public $targetUrl;

    public $apiConfig = [
        'url'=>'https://firebasedynamiclinks.googleapis.com/v1/',
    ];

    private $_apiInstance;

    private $_requestBody;


    public function init()
    {
        $this->apiConfig['url'] .= $this->getPath('shortLinks');
        $this->_apiInstance = new Rest(new Configuration($this->apiConfig));
        $this->_apiInstance->setDecoder('json');
    }

    public function shorten($targetUrl)
    {
        return $this->sendRequest($targetUrl);
    }

    public function getRequestBody()
    {
        if (!$this->_requestBody) {
            $this->_requestBody = $this->generateRequestBody();
        }
        return $this->_requestBody;
    }

    private function generateRequestBody()
    {
        $body = [
            'dynamicLinkInfo' => [
                'dynamicLinkDomain' => $this->linkDomain,
                'link' => $this->targetUrl,
                'androidInfo' => [
                    'androidPackageName' => $this->androidPackageName
                ],
                'iosInfo' => [
                    'iosBundleId' => $this->iosBundleId
                ]
            ],
            'suffix' => [
                'option' => $this->suffixOption
            ]
        ];

        //If forcedRedirect is set, add it to request body
        if ($this->enableForcedRedirect !== null) {
            $body['dynamicLinkInfo']['navigationInfo'] = [
             'enableForcedRedirect' => $this->enableForcedRedirect
            ];
        }

        //Set Appstoreid if set
        if ($this->iosAppStoreId !== null) {
            $body['dynamicLinkInfo']['iosInfo']['iosAppStoreId'] =  $this->iosAppStoreId;
        }

        return json_encode($body);
    }

    private function sendRequest($targetUrl)
    {
        $this->targetUrl = $targetUrl;
        $this->addHeaders();
        try {
            $response = $this->_apiInstance->post('', $this->requestBody);

            if ($response->code != 200) {
                throw new InvalidCallException($response->message);
            }
        } catch (\Exception $e) {
            return false;
        }

        return $response->body->shortLink;
    }

    private function addHeaders()
    {
        $this->_apiInstance->addHeaders(['Content-Type' => 'application/json','Content-Length' => strlen($this->requestBody)]);
    }

    private function getPath($path)
    {
        return $path . "?key=$this->apiKey";
    }
}
