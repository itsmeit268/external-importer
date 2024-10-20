<?php

namespace ExternalImporter\application\components\ai;

use ExternalImporter\application\admin\AiConfig;
use function ExternalImporter\prnx;

defined('\ABSPATH') || exit;

/**
 * GeminiClient class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2024 keywordrush.com
 */

class GeminiClient extends AiClient
{
    public function getChatUrl()
    {
        $type = AiConfig::getInstance()->option('model') === 'gemini-1.5-flash' ? 'flash' : 'pro';
        return 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-'.$type.':generateContent?key='.$this->api_key;
    }

    public function getHeaders()
    {
        return array(
            'Content-Type: application/json'
        );
    }

    public function getPayload($prompt, $system = '', $params = array())
    {
        $payload = array(
            "contents" => array(
                array(
                    "role" => "user",
                    "parts" => array(
                        array(
                            "text" => $prompt
                        )
                    )
                )
            ),
            "generationConfig" => array(
                "temperature" => 1,
                "topK" => 64,
                "topP" => 0.95,
                "maxOutputTokens" => 8192,
                "responseMimeType" => "text/plain"
            )
        );

        return $payload;
    }

    public function getContent($response)
    {
        // Decode the JSON response into an associative array
        $data = json_decode($response, true);

        if (!$data) {
            throw new \Exception('Invalid JSON formatting.');
        }

        if (!isset($data['candidates']) || !is_array($data['candidates']) || count($data['candidates']) === 0) {
            throw new \Exception('No content message in the Gemini response.');
        }

        $content = $data['candidates'][0]['content']['parts'][0]['text'];

        // Store usage data if available
        if (isset($data['usage'])) {
            $this->last_usage = $data['usage'];
        } else {
            $this->last_usage = array();
        }

        return $content;
    }
}
