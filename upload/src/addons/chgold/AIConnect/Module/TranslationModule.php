<?php

namespace chgold\AIConnect\Module;

class TranslationModule extends ModuleBase
{
    protected $moduleName = 'translation';

    private const MYMEMORY_API = 'https://api.mymemory.translated.net/get';

    protected function registerTools()
    {
        $provider = \XF::options()->aiconnect_translation_provider ?? 'ai_self';
        if ($provider !== 'mymemory') {
            return;
        }

        $this->registerTool('translate', [
            'description' => 'Translate text between languages. Supports text of any length (automatically split into chunks if needed). Source language is auto-detected if not specified.',
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
                        'description' => 'Source language as ISO 639-1 two-letter lowercase code (e.g. "en", "he", "fr", "ru"). Leave empty for auto-detection.',
                    ],
                    'target_lang' => [
                        'type' => 'string',
                        'description' => 'Target language as ISO 639-1 two-letter lowercase code (e.g. "en" English, "he" Hebrew, "fr" French, "ru" Russian, "ar" Arabic, "es" Spanish). Use getSupportedLanguages for full list.',
                    ],
                ],
            ],
        ]);

        $this->registerTool('getSupportedLanguages', [
            'description' => 'Get list of commonly supported language codes for translation',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
            ],
        ]);
    }

    /**
     * Split text into chunks that fit within the API limit.
     */
    protected function chunkText($text, $maxLength = 450)
    {
        if (mb_strlen($text) <= $maxLength) {
            return [$text];
        }

        $chunks = [];
        while (mb_strlen($text) > $maxLength) {
            $slice = mb_substr($text, 0, $maxLength);
            $breakAt = null;

            // Find best break point: paragraph > sentence > word
            foreach (["\n\n", "\n", '. ', '! ', '? ', '; ', ', '] as $sep) {
                $pos = mb_strrpos($slice, $sep);
                if ($pos !== false && $pos > (int) ($maxLength / 3)) {
                    $breakAt = $pos + mb_strlen($sep);
                    break;
                }
            }

            // Fall back to word boundary
            if ($breakAt === null) {
                $pos = mb_strrpos($slice, ' ');
                $breakAt = ($pos !== false && $pos > (int) ($maxLength / 3)) ? $pos + 1 : $maxLength;
            }

            $chunks[] = rtrim(mb_substr($text, 0, $breakAt));
            $text = ltrim(mb_substr($text, $breakAt));
        }

        if (mb_strlen($text) > 0) {
            $chunks[] = $text;
        }

        return array_values(array_filter($chunks));
    }

    /**
     * Translate a single chunk via MyMemory API.
     */
    protected function translateChunk($text, $sourceLang, $targetLang)
    {
        if (empty($sourceLang) || strtolower($sourceLang) === 'auto') {
            $sourceLang = $this->detectLanguage($text);
        }

        $langPair = $sourceLang . '|' . $targetLang;

        $url = self::MYMEMORY_API . '?' . http_build_query([
            'q'        => $text,
            'langpair' => $langPair,
        ]);

        $client   = \XF::app()->http()->client();
        $response = $client->get($url, [
            'timeout' => 10,
            'headers' => ['User-Agent' => 'XenForo-AIConnect/1.0'],
        ]);

        if ($response->getStatusCode() !== 200) {
            return ['success' => false, 'error' => 'HTTP ' . $response->getStatusCode()];
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (!$data || !isset($data['responseData']['translatedText'])) {
            return ['success' => false, 'error' => $data['responseDetails'] ?? 'Invalid response'];
        }

        if (!empty($data['quotaFinished'])) {
            return [
                'success' => false,
                'error' => 'quota_exceeded',
                'message' => 'Daily translation quota exceeded (MyMemory free API limit: ~5,000 chars/day). Try again tomorrow.',
            ];
        }

        $translated = $data['responseData']['translatedText'];

        // If TM returned the same text, look for a better match in the matches array
        if ($this->isSameText($text, $translated) && !empty($data['matches'])) {
            foreach ($data['matches'] as $match) {
                if (!$this->isSameText($text, $match['translation'])) {
                    $translated = $match['translation'];
                    break;
                }
            }
        }

        return ['success' => true, 'text' => $translated];
    }

    /**
     * Detect source language from text using character analysis.
     */
    protected function detectLanguage($text)
    {
        // Hebrew
        if (preg_match('/[\x{0590}-\x{05FF}]/u', $text)) {
            return 'he';
        }
        // Arabic
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }
        // Russian/Cyrillic
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $text)) {
            return 'ru';
        }
        // Chinese
        if (preg_match('/[\x{4E00}-\x{9FFF}]/u', $text)) {
            return 'zh';
        }
        // Japanese (Hiragana/Katakana)
        if (preg_match('/[\x{3040}-\x{30FF}]/u', $text)) {
            return 'ja';
        }
        // Korean
        if (preg_match('/[\x{AC00}-\x{D7AF}]/u', $text)) {
            return 'ko';
        }
        // Default to English
        return 'en';
    }

    /**
     * Check if two texts are essentially the same (ignoring case and punctuation).
     */
    protected function isSameText($original, $translated)
    {
        $norm = function ($s) {
            return mb_strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $s)));
        };
        return $norm($original) === $norm($translated);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_translate($params)
    {
        $text       = $params['text'];
        $sourceLang = $params['source_lang'] ?? '';
        $targetLang = $params['target_lang'];

        try {
            $chunks = $this->chunkText($text);
            $translated = [];

            foreach ($chunks as $chunk) {
                $result = $this->translateChunk($chunk, $sourceLang, $targetLang);
                if (!$result['success']) {
                    return $this->error('translation_failed', 'Translation failed: ' . $result['error']);
                }
                $translated[] = $result['text'];
            }

            $translatedText = implode(' ', $translated);

            return $this->success([
                'original_text'   => $text,
                'translated_text' => $translatedText,
                'source_lang'     => $sourceLang ?: 'auto',
                'target_lang'     => $targetLang,
                'chunks'          => count($chunks),
            ]);
        } catch (\Exception $e) {
            return $this->error('exception', 'Translation error: ' . $e->getMessage());
        }
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
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
