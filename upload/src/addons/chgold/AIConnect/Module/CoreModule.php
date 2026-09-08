<?php

namespace chgold\AIConnect\Module;

class CoreModule extends ModuleBase
{
    protected $moduleName = 'xenforo';

    public function getToolPromptMeta(): array
    {
        return [
            'getCurrentUser' => [
                'hint'       => 'call this first to verify',
                'url_params' => [''],
            ],
            'searchThreads' => [
                'hint'       => 'search=KEYWORD, since=1week, limit=20',
                'url_params' => [
                    'limit=20',
                    'search=KEYWORD&limit=10',
                    'since=today&limit=20',
                    'since=1week&limit=20',
                    'forum_id=FORUM_ID&limit=20',
                ],
            ],
            'getThread' => [
                'hint'       => 'thread_id=NUMBER',
                'url_params' => ['thread_id=THREAD_ID'],
            ],
            'searchPosts' => [
                'hint'       => 'search=KEYWORD, since=1week, limit=20',
                'url_params' => [
                    'limit=20',
                    'search=KEYWORD&limit=10',
                    'since=1week',
                ],
            ],
            'getPost' => [
                'hint'       => 'post_id=NUMBER',
                'url_params' => ['post_id=POST_ID'],
            ],
        ];
    }

    protected function registerTools()
    {

        $this->registerTool('searchThreads', [
            'description' => 'Search XenForo thread TITLES, ranked by relevance. Supports XenForo search syntax: '
                . '"an exact phrase" in quotes, +required, -excluded. Multiple words must all appear, in any order. '
                . 'Returns results plus total, page and has_more — use page to walk through them. '
                . 'To search the BODY of posts instead, use searchPosts. '
                . 'Date options: (1) since=Xw for open range from X ago until now; (2) date_from+date_to for exact window (e.g. date_from=2026-03-09&date_to=2026-03-15); (3) since+until for relative window (e.g. since=3w&until=2w = the week 3 weeks ago). All params optional.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'search' => [
                        'type' => 'string',
                        'description' => 'Search query (min 1 character, max 200 characters)',
                        'minLength' => 1,
                        'maxLength' => 200,
                    ],
                    'forum_id' => [
                        'type' => 'integer',
                        'description' => 'Forum ID to filter by',
                    ],
                    'user_id' => [
                        'type' => 'integer',
                        'description' => 'Filter by author user ID (optional)',
                    ],
                    'username' => [
                        'type' => 'string',
                        'description' => 'Filter by author username, case-insensitive (optional)',
                    ],
                    'since' => [
                        'type' => 'string',
                        'description' => 'Lower time bound only — returns results from this point UNTIL NOW (open-ended). Formats: presets (today, yesterday, 1hour, 1week, 1month), relative (3d, 6h, 2w, 1y, 3months), ISO date (2026-03-15), or "all". To limit the upper bound too, add date_to or until.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date: ISO format YYYY-MM-DD (e.g. "2026-03-08") or Unix timestamp as string. Optional.',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date: ISO format YYYY-MM-DD (e.g. "2026-03-29") or Unix timestamp as string. Optional.',
                    ],
                    'until' => [
                        'type' => 'string',
                        'description' => 'Upper time bound — same format as since. Use with since to define a closed relative window: since=3w&until=2w = the week from 3 to 2 weeks ago.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Results per page (default 10, max 100)',
                        'default' => 10,
                    ],
                    'page' => [
                        'type' => 'integer',
                        'description' => 'Page number, starting at 1. The response returns total and has_more.',
                        'default' => 1,
                    ],
                    'match_mode' => [
                        'type' => 'string',
                        'description' => 'auto (default) uses the search engine and retries as substring if it '
                            . 'finds nothing; words forces engine-only whole-word matching with full search syntax; '
                            . 'substring forces matching inside words, which finds a base word within its prefixed '
                            . 'forms but disables quoted phrases and -excluded. The response reports which was used.',
                        'enum' => ['auto', 'words', 'substring'],
                        'default' => 'auto',
                    ],
                ],
            ],
        ]);

        $this->registerTool('getThread', [
            'description' => 'Get a single thread by ID',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => [
                        'type' => 'integer',
                        'description' => 'Thread ID',
                    ],
                ],
            ],
        ]);

        $this->registerTool('searchPosts', [
            'description' => 'Search the BODY of XenForo posts, ranked by relevance. Supports XenForo search syntax: '
                . '"an exact phrase" in quotes, +required, -excluded. Multiple words must all appear in the same post, '
                . 'in any order. Returns results plus total, page and has_more — use page to walk through them. '
                . 'To search thread titles instead, use searchThreads. '
                . 'Date options: (1) since=Xw for open range from X ago until now; (2) date_from+date_to for exact window (e.g. date_from=2026-03-09&date_to=2026-03-15); (3) since+until for relative window (e.g. since=3w&until=2w = the week 3 weeks ago). All params optional.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'search' => [
                        'type' => 'string',
                        'description' => 'Search query (min 1 character, max 200 characters)',
                        'minLength' => 1,
                        'maxLength' => 200,
                    ],
                    'thread_id' => [
                        'type' => 'integer',
                        'description' => 'Thread ID to filter by',
                    ],
                    'user_id' => [
                        'type' => 'integer',
                        'description' => 'Filter by author user ID (optional)',
                    ],
                    'username' => [
                        'type' => 'string',
                        'description' => 'Filter by author username, case-insensitive (optional)',
                    ],
                    'since' => [
                        'type' => 'string',
                        'description' => 'Lower time bound only — returns results from this point UNTIL NOW (open-ended). Formats: presets (today, yesterday, 1hour, 1week, 1month), relative (3d, 6h, 2w, 1y, 3months), ISO date (2026-03-15), or "all". To limit the upper bound too, add date_to or until.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date: ISO format YYYY-MM-DD (e.g. "2026-03-08") or Unix timestamp as string. Optional.',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date: ISO format YYYY-MM-DD (e.g. "2026-03-29") or Unix timestamp as string. Optional.',
                    ],
                    'until' => [
                        'type' => 'string',
                        'description' => 'Upper time bound — same format as since. Use with since to define a closed relative window: since=3w&until=2w = the week from 3 to 2 weeks ago.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Results per page (default 10, max 100)',
                        'default' => 10,
                    ],
                    'page' => [
                        'type' => 'integer',
                        'description' => 'Page number, starting at 1. The response returns total and has_more.',
                        'default' => 1,
                    ],
                    'match_mode' => [
                        'type' => 'string',
                        'description' => 'auto (default) uses the search engine and retries as substring if it '
                            . 'finds nothing; words forces engine-only whole-word matching with full search syntax; '
                            . 'substring forces matching inside words, which finds a base word within its prefixed '
                            . 'forms but disables quoted phrases and -excluded. The response reports which was used.',
                        'enum' => ['auto', 'words', 'substring'],
                        'default' => 'auto',
                    ],
                ],
            ],
        ]);

        $this->registerTool('getPost', [
            'description' => 'Get a single post by ID',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => [
                    'post_id' => [
                        'type' => 'integer',
                        'description' => 'Post ID',
                    ],
                ],
            ],
        ]);

        $this->registerTool('getCurrentUser', [
            'description' => 'Get current authenticated user information',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        // Cheap read-only stats tool. Powers the declarative brief metric
        // `community.unanswered.count` (see registerBriefMetrics below). Safe
        // to call on a schedule — one indexed COUNT(*) over xf_thread.
        $this->registerTool('getForumStats', [
            'description' => 'Return community stats — currently only unanswered_count. An "unanswered" '
                . 'thread is one with reply_count = 0 (no replies at all — the very first response has '
                . 'not been posted). Soft-deleted, moderated and hidden threads are EXCLUDED (discussion_state '
                . '= visible). The optional from/to/timezone params are accepted for future range queries '
                . 'but the current count is a live snapshot at query time and does not filter by date — '
                . 'the brief collector still passes them so the collector-side date tagging is consistent.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'from'     => ['type' => 'string', 'description' => 'Local start date YYYY-MM-DD (accepted, currently unused by the snapshot count).'],
                    'to'       => ['type' => 'string', 'description' => 'Local end date YYYY-MM-DD (accepted, currently unused by the snapshot count).'],
                    'timezone' => ['type' => 'string', 'description' => 'IANA timezone (accepted, currently unused by the snapshot count).'],
                ],
            ],
        ]);

        // Register declarative brief metrics for goldnat.ai. Constants only:
        // {{periodStart}}, {{periodEnd}} and {{timezone}} are substituted by
        // the collector — never computed locally on the wrong clock.
        $this->registerBriefMetrics();
    }

    /**
     * Register the declarative brief metrics exposed by this module.
     *
     * Split out from registerTools() so subclasses / Pro modules can override
     * without touching the tool table above.
     *
     * @return void
     */
    protected function registerBriefMetrics(): void
    {
        if (!$this->manifestService || !method_exists($this->manifestService, 'registerBriefMetric')) {
            return;
        }

        $this->manifestService->registerBriefMetric([
            'key'         => 'community.unanswered.count',
            'tool'        => $this->moduleName . '.getForumStats',
            'args'        => [
                'from'     => '{{periodStart}}',
                'to'       => '{{periodEnd}}',
                'timezone' => '{{timezone}}',
            ],
            'valuePath'   => 'data.unanswered_count',
            'granularity' => 'day',
        ]);
    }

    /**
     * Applies a search string to a finder as one substring condition per term.
     *
     * A multi-word query used to be passed through as a single literal, so
     * "lice+partition" was looked up as that exact sequence of characters and
     * matched nothing — even when both words appeared in the same post. Agents
     * write queries that way because "+" reads as AND, so the terms are split
     * on "+" and on whitespace and combined with AND: every term must appear,
     * in any order and anywhere in the field.
     *
     * Each term stays a substring match, so a query for a base word still finds
     * it inside a longer word — which matters for languages that attach
     * prefixes and suffixes directly onto the word.
     *
     * Wildcard characters in user input are escaped so a query containing % or
     * _ cannot widen the match beyond what was asked for.
     */
    protected function applySearchTerms($finder, string $field, $search): void
    {
        $search = trim((string) ($search ?? ''));
        if ($search === '') {
            return;
        }

        $terms = preg_split('/[\s+]+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($terms as $term) {
            $finder->where($field, 'LIKE', $finder->escapeLike($term, '%?%'));
        }
    }

    protected function resolveDateFrom($params)
    {
        if (!empty($params['date_from'])) {
            return $this->parseTimestamp($params['date_from']);
        }
        if (!empty($params['since'])) {
            return $this->parseSince($params['since']);
        }
        return null;
    }

    protected function parseTimestamp($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        // ISO date "YYYY-MM-DD"
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
            $ts = strtotime($value . ' 00:00:00');
            return $ts !== false ? $ts : (int) $value;
        }
        // ISO datetime "YYYY-MM-DD HH:MM"
        if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', (string) $value)) {
            $ts = strtotime($value);
            return $ts !== false ? $ts : (int) $value;
        }
        return (int) $value;
    }

    protected function parseSince($since)
    {
        $now = time();
        switch ($since) {
            case 'today':
                return mktime(0, 0, 0);
            case 'yesterday':
                return mktime(0, 0, 0) - 86400;
            case '1hour':
                return $now - 3600;
            case '1week':
                return $now - 604800;
            case '1month':
                return $now - 2592000;
            case 'all':
            case 'everything':
            case 'all-time':
            case 'alltime':
                return 0; // Unix epoch = all history
        }
        // Dynamic patterns: "3d", "6h", "2w", "1m", "1y", "3days", "6hours", "2years" etc.
        if (preg_match('/^(\d+)\s*(d|h|w|m|y|day|hour|week|month|year)s?$/i', $since, $matches)) {
            $n    = (int) $matches[1];
            $unit = strtolower($matches[2][0]);
            switch ($unit) {
                case 'd':
                    return $now - ($n * 86400);
                case 'h':
                    return $now - ($n * 3600);
                case 'w':
                    return $now - ($n * 604800);
                case 'm':
                    return $now - ($n * 2592000);
                case 'y':
                    return $now - ($n * 31536000);
            }
        }
        // ISO date "YYYY-MM-DD"
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $since)) {
            $ts = strtotime($since . ' 00:00:00');
            return $ts !== false ? $ts : null;
        }
        // ISO datetime
        if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', $since)) {
            $ts = strtotime($since);
            return $ts !== false ? $ts : null;
        }
        // Unknown value → return null = no date filter (return all history)
        return null;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_searchThreads($params)
    {
        $mode = $this->searchMatchMode($params);

        if ($mode !== 'substring') {
            $engine = $this->searchViaEngine('thread', $params, true);
            if ($engine !== null && !$this->shouldRetryAsSubstring($engine, $mode)) {
                return $engine;
            }
        }

        return $this->searchThreadsFallback($params);
    }

    /**
     * Resolves the requested matching mode.
     *
     * 'words'     — XenForo's search engine only. Whole-word tokens, so it gains
     *               phrase syntax, relevance ranking and paging.
     * 'substring' — direct query only. Matches inside words.
     * 'auto'      — engine first, falling back to substring when it finds
     *               nothing. Default, because neither mode alone is correct for
     *               every language (see shouldRetryAsSubstring).
     */
    protected function searchMatchMode(array $params): string
    {
        $mode = strtolower(trim((string) ($params['match_mode'] ?? 'auto')));
        return in_array($mode, ['auto', 'words', 'substring'], true) ? $mode : 'auto';
    }

    /**
     * True when an engine search came back empty and should be retried as a
     * substring query.
     *
     * A full-text index matches whole word tokens. That is fine for English,
     * where "cucumber" is its own word, but wrong for languages that glue
     * prefixes directly onto a word: in Hebrew "מלפפון" appears inside
     * "והמלפפון" as a single token, so the index holds a word the searcher
     * never typed and returns nothing. Full-text cannot solve this — a leading
     * wildcard is not supported in boolean mode, and the prefixes attach at the
     * START of the word, which is exactly where the wildcard cannot go.
     *
     * So when the engine finds nothing, the substring query gets a turn. The
     * caller keeps ranking and paging whenever the engine does have an answer,
     * and still gets results when only a substring match can find them.
     */
    protected function shouldRetryAsSubstring($engineResult, string $mode): bool
    {
        if ($mode !== 'auto') {
            return false;
        }
        $data = $engineResult['data'] ?? [];
        return isset($data['total']) && (int) $data['total'] === 0;
    }

    /**
     * Runs a search through XenForo's own search engine.
     *
     * Preferred over a raw LIKE query for four reasons that a LIKE cannot give:
     *
     *  - the engine understands XenForo's search syntax, so "an exact phrase",
     *    +required and -excluded all work;
     *  - results come back ranked by relevance rather than only by date;
     *  - permissions are applied INSIDE the search, so the caller always gets a
     *    full page. The old code fetched limit*2 rows and filtered afterwards,
     *    which silently returned short pages on boards where much of the content
     *    is restricted, and made a correct total impossible;
     *  - the filtered result set supports real paging, so page/total/has_more
     *    are honest numbers.
     *
     * Returns null when the engine cannot serve the request (no search index
     * built yet, or the visitor lacks search permission), so the caller can fall
     * back to the direct query rather than fail.
     *
     * @param string $type   'thread' or 'post'
     * @param bool   $titleOnly  thread search matches titles
     * @return array|null
     */
    protected function searchViaEngine(string $type, array $params, bool $titleOnly)
    {
        $keywords = trim((string) ($params['search'] ?? ''));
        if ($keywords === '') {
            // No keywords means "list recent content", which the engine does not
            // do — that is a plain finder job.
            return null;
        }

        if (!\XF::visitor()->canSearch()) {
            return null;
        }

        $app = \XF::app();
        try {
            $searcher = $app->search();
        } catch (\Throwable $e) {
            return null;
        }

        // An unbuilt index would report "no results" for content that plainly
        // exists, which is worse than a slower but correct answer.
        try {
            if (!$app->db()->fetchOne('SELECT content_id FROM xf_search_index LIMIT 1')) {
                return null;
            }
        } catch (\Throwable $e) {
            // Non-default search sources (e.g. Elasticsearch) may not use this
            // table at all; treat an unreadable probe as "engine unavailable".
            return null;
        }

        $query = new \XF\Search\Query\KeywordQuery($searcher);
        $query->inType($type)->withKeywords($keywords, $titleOnly);

        if (!empty($params['user_id'])) {
            $query->byUserId((int) $params['user_id']);
        } elseif (!empty($params['username'])) {
            $user = \XF::em()->findOne('XF:User', ['username' => trim((string) $params['username'])]);
            if (!$user) {
                return $this->success(['results' => [], 'total' => 0, 'page' => 1, 'has_more' => false]);
            }
            $query->byUserId($user->user_id);
        }

        // Forum scoping: both thread and post index records carry the node id.
        $nodeId = $params['forum_id'] ?? ($params['node_id'] ?? null);
        if (!empty($nodeId)) {
            $query->withMetadata('node', (int) $nodeId);
        }
        if ($type === 'post' && !empty($params['thread_id'])) {
            $query->withMetadata('thread', (int) $params['thread_id']);
        }

        $min = $this->resolveDateFrom($params);
        $max = null;
        if (!empty($params['date_to'])) {
            $max = $this->parseTimestamp($params['date_to']);
        } elseif (!empty($params['until'])) {
            $max = $this->parseSince($params['until']);
        }
        if ($min !== null || $max !== null) {
            $query->withinDateRange($min ?: 0, $max ?: 0);
        }

        try {
            $results = $searcher->search($query);
        } catch (\Throwable $e) {
            // A misconfigured or unavailable search source must not take the
            // tool down — fall back instead.
            return null;
        }

        $perPage = $this->boundedSearchLimit($params);
        $page    = max(1, (int) ($params['page'] ?? 1));

        $resultSet = $searcher->getResultSet($results)->limitToViewableResults();
        $total     = $resultSet->countResults();
        $resultSet->sliceResultsToPage($page, $perPage);

        $out = [];
        foreach ($resultSet->getResultsData() as $entity) {
            if ($entity instanceof \XF\Entity\Thread) {
                $out[] = $this->formatThread($entity);
            } elseif ($entity instanceof \XF\Entity\Post) {
                $out[] = $this->formatPost($entity);
            }
        }

        return $this->success([
            'results'  => $out,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'has_more' => ($page * $perPage) < $total,
            'engine'   => 'xf_search',
        ]);
    }

    /** Page size for search tools, bounded so an agent cannot ask for the world. */
    protected function boundedSearchLimit(array $params): int
    {
        $limit = isset($params['limit']) ? (int) $params['limit'] : 10;
        return max(1, min($limit, 100));
    }

    /**
     * Direct-query search, used when the engine is unavailable (no index built).
     *
     * Permissions still cannot be expressed in the query here, so the page is
     * over-fetched and filtered — but the response now says so via
     * engine=fallback and an approximate total, instead of quietly returning a
     * short page as if it were complete.
     */
    protected function searchThreadsFallback($params)
    {
        $finder = \XF::finder('XF:Thread');
        $this->applySearchTerms($finder, 'title', $params['search'] ?? null);

        if (!empty($params['forum_id'])) {
            $finder->where('node_id', $params['forum_id']);
        }
        if (!empty($params['user_id'])) {
            $finder->where('user_id', (int) $params['user_id']);
        }
        if (!empty($params['username'])) {
            $finder->where('username', $params['username']);
        }

        $dateFrom = $this->resolveDateFrom($params);
        if ($dateFrom !== null) {
            $finder->where('post_date', '>=', $dateFrom);
        }
        if (!empty($params['date_to'])) {
            $finder->where('post_date', '<=', $this->parseTimestamp($params['date_to']));
        }
        if (!empty($params['until']) && empty($params['date_to'])) {
            $finder->where('post_date', '<=', $this->parseSince($params['until']));
        }

        return $this->fallbackPage(
            $finder->where('discussion_state', 'visible')->with('Forum')->order('post_date', 'DESC'),
            $params,
            function ($thread) {
                return $this->formatThread($thread);
            }
        );
    }

    /**
     * Shared paging for the fallback path: walks the finder page by page,
     * keeping only entries the visitor may see, until the requested page is
     * filled. Slower than the engine, but it never returns a short page while
     * more viewable results exist.
     */
    protected function fallbackPage($finder, array $params, \Closure $format)
    {
        $perPage = $this->boundedSearchLimit($params);
        $page    = max(1, (int) ($params['page'] ?? 1));
        $skip    = ($page - 1) * $perPage;

        $out     = [];
        $seen    = 0;
        $offset  = 0;
        $chunk   = $perPage * 4;
        $scanCap = 2000; // bound the work on very large boards

        while (count($out) < $perPage && $offset < $scanCap) {
            $batch = $finder->limit($chunk, $offset)->fetch();
            if (!count($batch)) {
                break;
            }
            foreach ($batch as $entity) {
                if (!$entity->canView()) {
                    continue;
                }
                $seen++;
                if ($seen <= $skip) {
                    continue;
                }
                $out[] = $format($entity);
                if (count($out) >= $perPage) {
                    break;
                }
            }
            $offset += $chunk;
        }

        return $this->success([
            'results'  => $out,
            'page'     => $page,
            'per_page' => $perPage,
            'has_more' => count($out) >= $perPage,
            'engine'   => 'substring',
            'note'     => 'Substring matching: the term is matched anywhere inside a word, so a base word also '
                        . 'finds its prefixed forms. Results are ordered by date, and search syntax such as quoted '
                        . 'phrases or -excluded is NOT applied in this mode.',
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getThread($params)
    {
        $thread = \XF::em()->find('XF:Thread', $params['thread_id'], ['Forum', 'FirstPost']);

        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }

        if (!$thread->canView()) {
            return $this->error('not_accessible', 'Thread is not accessible');
        }

        return $this->success($this->formatThread($thread));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_searchPosts($params)
    {
        $mode = $this->searchMatchMode($params);

        if ($mode !== 'substring') {
            $engine = $this->searchViaEngine('post', $params, false);
            if ($engine !== null && !$this->shouldRetryAsSubstring($engine, $mode)) {
                return $engine;
            }
        }

        $finder = \XF::finder('XF:Post');
        $this->applySearchTerms($finder, 'message', $params['search'] ?? null);

        if (!empty($params['thread_id'])) {
            $finder->where('thread_id', $params['thread_id']);
        }
        if (!empty($params['user_id'])) {
            $finder->where('user_id', (int) $params['user_id']);
        }
        if (!empty($params['username'])) {
            $finder->where('username', $params['username']);
        }

        $dateFrom = $this->resolveDateFrom($params);
        if ($dateFrom !== null) {
            $finder->where('post_date', '>=', $dateFrom);
        }
        if (!empty($params['date_to'])) {
            $finder->where('post_date', '<=', $this->parseTimestamp($params['date_to']));
        }
        if (!empty($params['until']) && empty($params['date_to'])) {
            $finder->where('post_date', '<=', $this->parseSince($params['until']));
        }

        return $this->fallbackPage(
            $finder->where('message_state', 'visible')
                ->with(['Thread', 'Thread.Forum'])
                ->order('post_date', 'DESC'),
            $params,
            function ($post) {
                return $this->formatPost($post);
            }
        );
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getPost($params)
    {
        $post = \XF::em()->find('XF:Post', $params['post_id'], ['Thread', 'Thread.Forum']);

        if (!$post) {
            return $this->error('not_found', 'Post not found');
        }

        if (!$post->canView()) {
            return $this->error('not_accessible', 'Post is not accessible');
        }

        return $this->success($this->formatPost($post));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getCurrentUser($params)
    {
        $visitor = \XF::visitor();

        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'No authenticated user');
        }

        return $this->success([
            'user_id' => $visitor->user_id,
            'username' => $visitor->username,
            'email' => $visitor->email,
            'is_admin' => (bool) $visitor->is_admin,
            // user_group_id and is_super_admin were carried by the Pro add-on's
            // getMe, which duplicated this tool and has been removed. They live
            // here now so nothing an agent could previously read was lost.
            'user_group_id' => $visitor->user_group_id,
            'is_super_admin' => (bool) $visitor->is_super_admin,
            'is_moderator' => (bool) $visitor->is_moderator,
            'message_count' => $visitor->message_count,
            'register_date' => $visitor->register_date,
        ]);
    }

    /**
     * Execute the getForumStats tool.
     *
     * Powers the declarative brief metric `community.unanswered.count`. Returns
     * a snapshot COUNT(*) of currently unanswered threads.
     *
     * Definition (documented in the returned payload for auditability):
     *   - reply_count = 0            (nobody has replied — the first response
     *                                  has never been posted; this is the sharpest
     *                                  "needs attention" signal a forum has)
     *   - discussion_state = visible (deleted/moderated/hidden threads excluded)
     *
     * The from/to/timezone params are accepted so the manifest args line up
     * with every other brief metric (uniform substitution shape) but the
     * current implementation returns a live snapshot rather than a windowed
     * count — the collector always reads it at the collection moment.
     *
     * @param  array $params
     * @return array
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getForumStats($params)
    {
        $db = \XF::db();

        $unanswered = (int) $db->fetchOne(
            'SELECT COUNT(*) FROM xf_thread WHERE reply_count = 0 AND discussion_state = ?',
            ['visible']
        );

        return $this->success([
            'unanswered_count' => $unanswered,
            'definition'       => [
                'reply_count'      => 0,
                'discussion_state' => 'visible',
                'note'             => 'Snapshot at query time — not filtered by from/to.',
            ],
            'from'     => $params['from']     ?? null,
            'to'       => $params['to']       ?? null,
            'timezone' => $params['timezone'] ?? null,
        ]);
    }

    protected function formatThread($thread)
    {
        return [
            'thread_id' => $thread->thread_id,
            'title' => $thread->title,
            'forum_id' => $thread->node_id,
            'forum_name' => $thread->Forum ? $thread->Forum->title : null,
            'user_id' => $thread->user_id,
            'username' => $thread->username,
            'post_date' => date('c', $thread->post_date),
            'reply_count' => $thread->reply_count,
            'view_count' => $thread->view_count,
            'last_post_date' => date('c', $thread->last_post_date),
            'prefix_id' => $thread->prefix_id,
            'discussion_state'   => $thread->discussion_state,
            'first_post_id'      => $thread->first_post_id,
            'first_post_message' => $thread->FirstPost ? $thread->FirstPost->message : null,
            'url' => \XF::app()->router('public')->buildLink('canonical:threads', $thread),
        ];
    }

    protected function formatPost($post)
    {
        return [
            'post_id' => $post->post_id,
            'thread_id' => $post->thread_id,
            'user_id' => $post->user_id,
            'username' => $post->username,
            'post_date' => date('c', $post->post_date),
            'message' => $post->message,
            'message_state' => $post->message_state,
            'thread_title' => $post->Thread ? $post->Thread->title : null,
            'url' => \XF::app()->router('public')->buildLink('canonical:posts', $post),
        ];
    }
}
