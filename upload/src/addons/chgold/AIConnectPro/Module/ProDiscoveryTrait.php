<?php

namespace chgold\AIConnectPro\Module;

/**
 * Discovery bundle: forum structure, activity and thread metadata.
 *
 * Read-only, but Pro — the free add-on ships only its five original read tools
 * (searchThreads / getThread / searchPosts / getPost / getCurrentUser); every
 * tool beyond that set is licensed, regardless of scope.
 *
 * Everything here passes through XenForo's own permission system, so an agent
 * can never see a node, thread or post that the connected account could not
 * already see through the web interface.
 */
trait ProDiscoveryTrait
{
    protected function registerDiscoveryTools()
    {
        $limit = [
            'type' => 'integer',
            'description' => 'Maximum results (default 20, cap 100)',
        ];

        // Node listing lives here rather than in Automation: these read the forum
        // structure and carry no admin gate, so pairing them with node/user
        // creation forced anyone who only wanted to browse the tree to buy the
        // administrative bundle.
        $this->registerTool('listNodes', [
            'description' => 'List the full node tree (categories, forums and pages the caller may see)',
            'input_schema' => ['type' => 'object', 'properties' => new \stdClass(), 'additionalProperties' => false],
        ]);
        $this->registerTool('getNode', [
            'description' => 'Get a single node by ID',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => ['node_id' => ['type' => 'integer', 'description' => 'Node id']],
            ],
        ]);
        $this->registerTool('listNodesFlat', [
            'description' => 'List nodes as a flat array with depth, for rendering the tree',
            'input_schema' => ['type' => 'object', 'properties' => new \stdClass(), 'additionalProperties' => false],
        ]);

        $this->registerTool('getForumNode', [
            'description' => 'Get a forum node by ID, with its title, description and counts',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => [
                    'node_id' => ['type' => 'integer', 'description' => 'Forum node ID'],
                ],
            ],
        ]);

        $this->registerTool('getForumStats', [
            'description' => 'Get board-wide statistics: totals for threads, posts and members',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getRecentThreads', [
            'description' => 'List the most recently active threads across the board',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'node_id' => ['type' => 'integer', 'description' => 'Restrict to one forum node'],
                    'limit' => $limit,
                ],
            ],
        ]);

        $this->registerTool('getTrendingThreads', [
            'description' => 'List threads with the most replies in a recent window',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'days' => ['type' => 'integer', 'description' => 'Window in days (default 7)'],
                    'limit' => $limit,
                ],
            ],
        ]);

        $this->registerTool('getThreadTags', [
            'description' => 'List the tags applied to a thread',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread ID'],
                ],
            ],
        ]);

        $this->registerTool('getThreadPoll', [
            'description' => 'Get the poll attached to a thread, with its responses and vote counts',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread ID'],
                ],
            ],
        ]);

        $this->registerTool('getPostReactions', [
            'description' => 'List the reactions a post has received, grouped by type and with the members who reacted',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => [
                    'post_id' => ['type' => 'integer', 'description' => 'Post ID'],
                    'limit' => $limit,
                ],
            ],
        ]);

        $this->registerTool('getUserProfile', [
            'description' => 'Get a member profile by user ID, with join date and activity counts',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'User ID'],
                ],
            ],
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_listNodes($params)
    {
        $out = [];
        foreach (\XF::repository('XF:Node')->getFullNodeList() as $node) {
            // getFullNodeList() is unfiltered by design — it returns every node
            // regardless of who is asking. Without this guard the tool leaked
            // the titles and descriptions of private and staff-only forums to
            // any caller holding nothing more than a read token.
            if (!$node->canView()) {
                continue;
            }
            $out[] = $this->nodeData($node);
        }
        return $this->success($out);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getNode($params)
    {
        $node = \XF::em()->find('XF:Node', $params['node_id']);
        // Same "not found" response for a missing node and for one the caller
        // may not see, so the tool cannot be used to probe which node IDs exist.
        if (!$node || !$node->canView()) {
            return $this->error('not_found', 'Node not found');
        }
        return $this->success($this->nodeData($node));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_listNodesFlat($params)
    {
        // Filter before building the tree: a hidden parent must not contribute
        // its depth or leak its existence through the flattened output.
        $nodes = \XF::repository('XF:Node')->getFullNodeList()
            ->filter(function ($node) {
                return $node->canView();
            });
        $tree = \XF::repository('XF:Node')->createNodeTree($nodes);
        $out = [];
        foreach ($tree->getFlattened() as $entry) {
            $data = $this->nodeData($entry['record']);
            $data['depth'] = $entry['depth'];
            $out[] = $data;
        }
        return $this->success($out);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getForumNode($params)
    {
        $node = \XF::em()->find('XF:Node', $params['node_id']);
        if (!$node) {
            return $this->error('not_found', 'Forum node not found');
        }
        if (!$node->canView()) {
            return $this->error('not_accessible', 'Forum node is not accessible');
        }

        $forum = $node->Data instanceof \XF\Entity\Forum ? $node->Data : null;

        return $this->success([
            'node_id' => (int) $node->node_id,
            'title' => $node->title,
            'description' => strip_tags((string) $node->description),
            'node_type' => $node->node_type_id,
            'parent_node_id' => (int) $node->parent_node_id,
            'thread_count' => $forum ? (int) $forum->discussion_count : null,
            'post_count' => $forum ? (int) $forum->message_count : null,
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getForumStats($params)
    {
        // Read the registry cache when it is warm, and fall back to computing
        // the totals directly. The registry key is absent on a board whose
        // statistics cache has never been built, which would otherwise make
        // this tool fail on an otherwise healthy site.
        $stats = \XF::registry()->get('forumStatistics');
        if (!is_array($stats)) {
            $stats = \XF::repository('XF:Counters')->getForumStatisticsCacheData();
        }

        return $this->success([
            'thread_count' => (int) ($stats['discussions'] ?? $stats['threads'] ?? 0),
            'post_count' => (int) ($stats['messages'] ?? $stats['posts'] ?? 0),
            'user_count' => (int) ($stats['users'] ?? 0),
            'latest_user' => $stats['latestUser']['username'] ?? null,
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getRecentThreads($params)
    {
        $finder = \XF::finder('XF:Thread')
            ->where('discussion_state', 'visible')
            ->order('last_post_date', 'DESC')
            ->limit($this->boundedLimit($params));

        if (!empty($params['node_id'])) {
            $finder->where('node_id', (int) $params['node_id']);
        }

        return $this->success($this->formatThreads($finder->fetch()));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getTrendingThreads($params)
    {
        $days = isset($params['days']) ? max(1, (int) $params['days']) : 7;

        // "Trending" = most replies within the window, so the cutoff filters on
        // post_date rather than last_post_date: a long-dormant thread with one
        // recent reply is not trending.
        $finder = \XF::finder('XF:Thread')
            ->where('discussion_state', 'visible')
            ->where('post_date', '>', \XF::$time - ($days * 86400))
            ->order('reply_count', 'DESC')
            ->limit($this->boundedLimit($params));

        return $this->success([
            'window_days' => $days,
            'threads' => $this->formatThreads($finder->fetch()),
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getThreadTags($params)
    {
        $thread = $this->loadViewableThread($params['thread_id'], $error);
        if (!$thread) {
            return $error;
        }

        $tags = [];
        foreach ($thread->tags as $tagId => $tag) {
            $tags[] = [
                'tag_id' => (int) $tagId,
                'tag' => is_array($tag) ? ($tag['tag'] ?? '') : (string) $tag,
            ];
        }

        return $this->success([
            'thread_id' => (int) $thread->thread_id,
            'title' => $thread->title,
            'tags' => $tags,
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getThreadPoll($params)
    {
        $thread = $this->loadViewableThread($params['thread_id'], $error);
        if (!$thread) {
            return $error;
        }

        if (!$thread->discussion_type || $thread->discussion_type !== 'poll') {
            return $this->error('not_found', 'This thread does not have a poll');
        }

        $poll = $thread->Poll;
        if (!$poll) {
            return $this->error('not_found', 'Poll not found');
        }

        $responses = [];
        foreach ($poll->Responses as $response) {
            $responses[] = [
                'response_id' => (int) $response->poll_response_id,
                'response' => $response->response,
                'vote_count' => (int) $response->response_vote_count,
            ];
        }

        return $this->success([
            'thread_id' => (int) $thread->thread_id,
            'question' => $poll->question,
            'voter_count' => (int) $poll->voter_count,
            'public_votes' => (bool) $poll->public_votes,
            'closed' => (bool) $poll->close_date && $poll->close_date < \XF::$time,
            'responses' => $responses,
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getPostReactions($params)
    {
        $post = \XF::em()->find('XF:Post', $params['post_id'], ['Thread']);
        if (!$post) {
            return $this->error('not_found', 'Post not found');
        }
        if (!$post->canView()) {
            return $this->error('not_accessible', 'Post is not accessible');
        }

        $counts = [];
        $total  = 0;
        foreach ((array) $post->reactions as $reactionId => $count) {
            $reaction = \XF::em()->find('XF:Reaction', $reactionId);
            $counts[] = [
                'reaction_id' => (int) $reactionId,
                'title' => $reaction ? $reaction->title : null,
                'count' => (int) $count,
            ];
            $total += (int) $count;
        }

        // total_reactions is the number of reactions, NOT $post->reaction_score.
        // reaction_score is a WEIGHTED figure: each reaction type carries its own
        // weight, and types such as Wow and Sad are weighted 0 by default. Reading
        // it here reported "0 reactions" on posts that visibly had one, and made
        // the field contradict the reactions[] array beside it. Summing the counts
        // is the only value consistent with what the array reports.
        $limit    = $this->boundedLimit($params);
        $reactors = [];
        $finder   = \XF::finder('XF:ReactionContent')
            ->where('content_type', 'post')
            ->where('content_id', $post->post_id)
            ->where('is_counted', 1)
            ->with(['Reaction', 'ReactionUser'])
            ->order('reaction_date', 'DESC')
            ->limit($limit);

        foreach ($finder->fetch() as $rc) {
            $reactors[] = [
                'user_id' => (int) $rc->reaction_user_id,
                'username' => $rc->ReactionUser ? $rc->ReactionUser->username : null,
                'reaction_id' => (int) $rc->reaction_id,
                'reaction_title' => $rc->Reaction ? $rc->Reaction->title : null,
                'reaction_date' => \XF::language()->date($rc->reaction_date),
            ];
        }

        return $this->success([
            'post_id' => (int) $post->post_id,
            'total_reactions' => $total,
            'reaction_score' => (int) $post->reaction_score,
            'reactions' => $counts,
            'reactors' => $reactors,
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getUserProfile($params)
    {
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }

        // XenForo governs profile visibility through the VISITOR's member-list
        // permission rather than a method on the target user, mirroring what
        // XF\Pub\Controller\MemberController does before rendering a profile.
        if (!\XF::visitor()->canViewMemberList()) {
            return $this->error('not_accessible', 'You do not have permission to view member profiles');
        }
        if ($user->user_state !== 'valid' || $user->is_banned) {
            return $this->error('not_accessible', 'User profile is not accessible');
        }

        return $this->success([
            'user_id' => (int) $user->user_id,
            'username' => $user->username,
            'message_count' => (int) $user->message_count,
            'reaction_score' => (int) $user->reaction_score,
            'trophy_points' => (int) $user->trophy_points,
            'register_date' => \XF::language()->date($user->register_date),
            'last_activity' => $user->last_activity ? \XF::language()->date($user->last_activity) : null,
            'is_staff' => (bool) $user->is_staff,
        ]);
    }

    /**
     * Load a thread the caller may view, or populate $error and return null.
     */
    private function nodeData($node): array
    {
        return [
            'node_id' => $node->node_id,
            'title' => $node->title,
            'node_type_id' => $node->node_type_id,
            'parent_node_id' => $node->parent_node_id,
            'description' => $node->description,
            'display_order' => $node->display_order,
        ];
    }

    protected function loadViewableThread($threadId, &$error = null)
    {
        $thread = \XF::em()->find('XF:Thread', $threadId, ['Forum']);
        if (!$thread) {
            $error = $this->error('not_found', 'Thread not found');
            return null;
        }
        if (!$thread->canView()) {
            $error = $this->error('not_accessible', 'Thread is not accessible');
            return null;
        }
        return $thread;
    }

    /**
     * Clamp a caller-supplied limit so one call cannot pull the whole board.
     */
    protected function boundedLimit(array $params)
    {
        $limit = isset($params['limit']) ? (int) $params['limit'] : 20;
        return max(1, min($limit, 100));
    }

    /**
     * Shape a thread collection, dropping any the caller may not view.
     */
    protected function formatThreads($threads)
    {
        $out = [];
        foreach ($threads as $thread) {
            if (!$thread->canView()) {
                continue;
            }
            $out[] = [
                'thread_id' => (int) $thread->thread_id,
                'title' => $thread->title,
                'node_id' => (int) $thread->node_id,
                'user_id' => (int) $thread->user_id,
                'username' => $thread->username,
                'reply_count' => (int) $thread->reply_count,
                'view_count' => (int) $thread->view_count,
                'post_date' => \XF::language()->date($thread->post_date),
                'last_post_date' => \XF::language()->date($thread->last_post_date),
            ];
        }
        return $out;
    }
}
