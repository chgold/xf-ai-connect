<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Phrases bundle (Group B): search/get/edit phrases.
 * 3 tools. XF stores version history automatically — reverting via ACP
 * is available.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminPhrasesTrait
{
    protected function registerPhrasesTools()
    {
        $this->registerTool('searchPhrases', [
            'description' => 'Search phrases by title or content substring. Returns up to 100 matches. '
                . 'Case-insensitive.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['search'],
                'properties' => [
                    'search' => ['type' => 'string'],
                    'language_id' => ['type' => 'integer', 'description' => 'Master (0) by default'],
                    'addon_id' => ['type' => 'string', 'description' => 'Filter by add-on'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getPhrase', [
            'description' => 'Get a phrase by title (and optionally language_id).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['title'],
                'properties' => [
                    'title' => ['type' => 'string'],
                    'language_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('editPhrase', [
            'description' => 'Edit a phrase (change its text). XF automatically records history — '
                . 'reverting is available via ACP.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['title', 'phrase_text'],
                'properties' => [
                    'title' => ['type' => 'string'],
                    'phrase_text' => ['type' => 'string'],
                    'language_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────

    public function execute_searchPhrases($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        // Phrases fall under the 'style' admin permission in XF core
        if ($err = $this->assertPermission('style')) return $err;

        $search = trim((string) $params['search']);
        if ($search === '') return $this->error('validation_failed', 'search cannot be empty');

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
        $sql = 'SELECT phrase_id, title, LEFT(phrase_text, 200) AS preview,
                       language_id, addon_id, version_string
                FROM xf_phrase
                WHERE (title LIKE ? OR phrase_text LIKE ?)';
        $args = [$like, $like];

        if (isset($params['language_id'])) {
            $sql .= ' AND language_id = ?';
            $args[] = (int) $params['language_id'];
        }
        if (!empty($params['addon_id'])) {
            $sql .= ' AND addon_id = ?';
            $args[] = (string) $params['addon_id'];
        }
        $sql .= ' ORDER BY title LIMIT 100';

        $rows = \XF::db()->fetchAll($sql, $args);
        return $this->success(['count' => count($rows), 'phrases' => $rows]);
    }

    public function execute_getPhrase($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $title = (string) $params['title'];
        $languageId = (int) ($params['language_id'] ?? 0);

        $phrase = \XF::em()->findOne('XF:Phrase', [
            'title' => $title,
            'language_id' => $languageId,
        ]);
        if (!$phrase) return $this->error('not_found', "Phrase '$title' not found in language $languageId");

        return $this->success([
            'phrase_id'      => (int)    $phrase->phrase_id,
            'title'          => (string) $phrase->title,
            'phrase_text'    => (string) $phrase->phrase_text,
            'language_id'    => (int)    $phrase->language_id,
            'addon_id'       => (string) $phrase->addon_id,
            'version_string' => (string) $phrase->version_string,
        ]);
    }

    public function execute_editPhrase($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $title = (string) $params['title'];
        $languageId = (int) ($params['language_id'] ?? 0);

        $phrase = \XF::em()->findOne('XF:Phrase', [
            'title' => $title,
            'language_id' => $languageId,
        ]);
        if (!$phrase) return $this->error('not_found', "Phrase '$title' not found");

        $phrase->phrase_text = (string) $params['phrase_text'];
        if (!$phrase->save()) {
            return $this->error('validation_failed', implode(' ', $phrase->getErrors()));
        }

        return $this->success([
            'title'    => $title,
            'updated'  => true,
            'note'     => 'XF stores phrase history automatically — revert via ACP if needed.',
        ]);
    }
}
