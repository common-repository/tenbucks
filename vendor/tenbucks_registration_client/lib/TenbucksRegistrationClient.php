<?php

/*
 * The MIT License
 *
 * Copyright 2016 tenbucks.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Send API keys TenBucks API server
 *
 * @author Gary P. <gary@webincolor.fr>
 */
final class TenbucksRegistrationClient
{
    const URL = 'https://apps.tenbucks.io/';

    /**
    * @var array Mandatory fields list
    */
    private $mandatoryFields = array(
        'email', // joe.doe@example.org
        'company', // My company name
        'platform', // WooCommerce|PrestaShop|Magento
        'locale', // fr|en
        'country', // FR, UK, US, ...
        'url' // https://www.example.org <- with protocol, no trailing-slash
    );

    /**
    * @var array Mandatory fields list
    */
    private $supportedLocales = array(
        'en', // English - US
        'fr', // Français - France
    );

    /**
     * @var string Key used to sign data
     */
    private $encryption_key;

    /**
    * Send API keys
    *
    * @param array $opts User data
    *
    * @return array server response
    */
    public function send(array $opts)
    {
        foreach ($this->mandatoryFields as $key) {
            if (empty($opts[$key])) {
                throw new Exception("Parameter $key is missing");
            }
        }
        $locale = strtolower($opts['locale']);

        if ( !in_array($locale, $this->supportedLocales) ) {
            $locale = 'en';
        }
        $path = sprintf('%s/registration/site/new', $locale);

        return $this->setKey($opts['url'])->call($path, $opts);
    }

    /**
     * Send uninstall notication to tenbucks.
     *
     * @param string $url
     *
     * @return array
     */
    public function uninstall($url)
    {
        return $this->call('uninstall', array(
            'url' => $url,
        ));
    }

    /**
     * Retrieve encryption key
     *
     * @param string $url shop url
     *
     * @return \TenbucksKeysClient
     *
     * @throws Exception
     */
    private function setKey($url)
    {
        $query = $this->call('key_manager/new', array(
            'url' => $url
        ));

        if (!array_key_exists('key', $query)) {
            $msg = 'Can\'t retrieve encryption key.';

            if (array_key_exists('error', $query)) {
                $msg .= ' ('.print_r($query['error'], true).')';
            }
            throw new Exception($msg);
        }

        $this->encryption_key = $query['key'];

        return $this;
    }

    private function call($path, array $data = array())
    {
        $url = self::URL.preg_replace('/^\//', '', $path);

        $request_headers = array(
            'Accept: application/json',
			'User-Agent: TenbucksKeys API Client'
		);

        if (!empty($this->encryption_key)) {
            $request_headers[] = 'X-Tenbucks-Signature: '.$this->getSignature($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Process
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ( empty( $response ) || !in_array($http_code, array(200, 201)) ) {
			$response = array(
				'http_code' => $http_code,
				'error' => curl_error($ch)
			);
		}
        curl_close($ch);

        return is_array($response) ? $response : json_decode($response, true);
    }

    private function getSignature(array $data)
    {
        ksort($data);
        
        return hash_hmac('sha256', http_build_query($data), $this->encryption_key);
    }
}