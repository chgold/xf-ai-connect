<?php

namespace chgold\AIConnect\Module;

class TranslationModule extends ModuleBase
{
    protected $moduleName = 'translation';
    
    private const MYMEMORY_API = 'https://api.mymemory.translated.net/get';

    protected function registerTools()
    {
        $this->registerTool('translate', [
            'description' => 'Translate text between languages using MyMemory translation service',
            'input_schema' => [
                'type' => 'object',
                'required' => ['text', 'target_lang'],
                'properties' => [
                    'text' => [
                        'type' => 'string',
                        'description' => 'Text to translate',
                    ],
                    'source_lang' => [
                        'type' => 'string',
                        'description' => 'Source language code (e.g., "en", "he", "es"). Leave empty for auto-detection.',
                    ],
                    'target_lang' => [
                        'type' => 'string',
                        'description' => 'Target language code (e.g., "en", "he", "es", "fr", "de", "ru")',
                    ],
                ],
            ],
        ]);

        $this->registerTool('getSupportedLanguages', [
            'description' => 'Get list of commonly supported language codes for translation',
            'input_schema' => [
                'type' => 'object',
                'properties' => [],
            ],
        ]);
    }

    public function execute_translate($params)
    {
        $text = $params['text'];
        $sourceLang = $params['source_lang'] ?? '';
        $targetLang = $params['target_lang'];

        $langPair = $sourceLang ? "{$sourceLang}|{$targetLang}" : "{$targetLang}";

        $url = self::MYMEMORY_API . '?' . http_build_query([
            'q' => $text,
            'langpair' => $langPair,
        ]);

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_USERAGENT, 'XenForo-AIConnect/1.0');
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return $this->error('http_error', 'Failed to connect to translation service: ' . $error);
            }

            if ($httpCode !== 200) {
                return $this->error('api_error', 'Translation service returned HTTP ' . $httpCode);
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['responseData'])) {
                return $this->error('invalid_response', 'Invalid response from translation service');
            }

            $responseData = $data['responseData'];

            if (!isset($responseData['translatedText'])) {
                return $this->error('translation_failed', 'Translation failed: ' . ($data['responseDetails'] ?? 'Unknown error'));
            }

            return $this->success([
                'original_text' => $text,
                'translated_text' => $responseData['translatedText'],
                'source_lang' => $sourceLang ?: 'auto',
                'target_lang' => $targetLang,
                'match' => $responseData['match'] ?? 0,
            ]);

        } catch (\Exception $e) {
            return $this->error('exception', 'Translation error: ' . $e->getMessage());
        }
    }

    public function execute_getSupportedLanguages($params)
    {
        $languages = [
            'en' => 'English',
            'he' => 'Hebrew',
            'ar' => 'Arabic',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese (Simplified)',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'tr' => 'Turkish',
            'sv' => 'Swedish',
            'da' => 'Danish',
            'no' => 'Norwegian',
            'fi' => 'Finnish',
            'cs' => 'Czech',
            'ro' => 'Romanian',
            'hu' => 'Hungarian',
            'el' => 'Greek',
            'th' => 'Thai',
            'hi' => 'Hindi',
            'id' => 'Indonesian',
            'vi' => 'Vietnamese',
            'uk' => 'Ukrainian',
            'bg' => 'Bulgarian',
            'hr' => 'Croatian',
        ];

        return $this->success([
            'languages' => $languages,
            'usage' => 'Use the language code (e.g., "en", "he") in the translate tool',
        ]);
    }
}
