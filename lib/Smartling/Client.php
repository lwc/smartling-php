<?php

namespace Smartling;

use Guzzle\Http\Client as Requester;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\EntityBody;

use Smartling\Errors\ErrorFactory;

class Client
{
    const BASEURL_PROD = 'https://api.smartling.com/v1';
    const BASEURL_SANDBOX = 'https://sandbox-api.smartling.com/v1';

    const TYPE_ANDROID = 'android';
    const TYPE_IOS = 'ios';
    const TYPE_GETTEXT = 'gettext';
    const TYPE_HTML = 'html';
    const TYPE_JAVA = 'javaProperties';
    const TYPE_XLIFF = 'xliff';
    const TYPE_XML = 'xml';
    const TYPE_JSON = 'json';
    const TYPE_YAML = 'yaml';

    private 
        $requester,
        $apiKey,
        $projectId,
        $useSandbox
        ;

    public function __construct($apiKey, $projectId, $useSandbox=false)
    {
        $this->requester = new Requester($useSandbox ? self::BASEURL_SANDBOX : self::BASEURL_PROD);
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
        $this->useSandbox = $useSandbox;
    }

    public function upload($source, $target, $type, $approved=true, $options=null, $callbackUrl=null)
    {
        $commands = array();
        if (is_array($options)) {
            foreach ($options as $k => $v) {
                $commands['smartling'.$k] = $v;
            }
        }
        $request = $this->requester->post('file/upload')
            ->addPostFields(array_merge($commands, array(
                'apiKey' => $this->apiKey,
                'projectId' => $this->projectId,
                'fileType' => $type,
                'fileUri' => $target,
                'approved' => $approved ? 'true' : 'false',
            )))
            ->addPostFiles(array('file' => $source));

        return $this->sendRequest($request);
    }

    public function get($source, $target, $locale=null, $retrievalType=null)
    {
        $args = array(
            'apiKey' => $this->apiKey,
            'projectId' => $this->projectId,
            'fileUri' => $source
        );
        if (isset($locale)) {
            $args['locale'] = $locale;
        }
        if (isset($retrievalType)) {
            $args['retrievalType'] = $retrievalType;
        }

        $request = $this->requester->get('file/get');
        $responseBody = EntityBody::factory(fopen($target, 'w+'));
        $request->setResponseBody($responseBody);
        $request->getQuery()->merge($args);
        return $this->sendRequest($request, true);
    }

    public function files($searchTerms=null)
    {
        if (!is_array($searchTerms)) {
            $searchTerms = array();
        }
        $request = $this->requester->get('file/list');
        $request->getQuery()->merge(array_merge($searchTerms, array(
            'apiKey' => $this->apiKey,
            'projectId' => $this->projectId,
        )));
        return $this->sendRequest($request);
    }

    public function status($fileUri, $locale)
    {
        $args = array(
            'apiKey' => $this->apiKey,
            'projectId' => $this->projectId,
            'fileUri' => $fileUri,
            'locale' => $locale
        );

        $request = $this->requester->get('file/status');
        $request->getQuery()->merge($args);
        return $this->sendRequest($request);
    }

    public function rename($fileUri, $newFileUri)
    {
        $request = $this->requester->post('file/rename')
            ->addPostFields(array(
                'apiKey' => $this->apiKey,
                'projectId' => $this->projectId,
                'fileUri' => $fileUri,
                'newFileUri' => $newFileUri,
            ));
        return $this->sendRequest($request);
    }

    public function delete($fileUri)
    {
         $args = array(
            'apiKey' => $this->apiKey,
            'projectId' => $this->projectId,
            'fileUri' => $fileUri,
        );

        $request = $this->requester->delete('file/delete');
        $request->getQuery()->merge($args);
        return $this->sendRequest($request);
    }

    private function sendRequest($request, $attachment=false)
    {
        try {
            $response = $request->send();
            if (!$attachment) {
                $outer = $response->json();
                return $outer['response']['data'];
            }
        }
        catch (BadResponseException $e) {
            throw ErrorFactory::createForGuzzleException($e);
        }
    }
}
