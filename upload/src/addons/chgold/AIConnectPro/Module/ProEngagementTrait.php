<?php

namespace chgold\AIConnectPro\Module;

use XF\Entity\ContentVote;

/**
 * Engagement tools for the Pro module (7 tools): reactions, votes, polls and
 * thread read state. Split into a trait to keep ProModule under the file-size
 * ceiling. All rely on XenForo's own permission checks.
 */
trait ProEngagementTrait
{
    protected function registerEngagementTools()
    {
        $reactSchema = static function (string $idName, string $idDesc): array {
            return [
                'type' => 'object',
                'required' => [$idName],
                'properties' => [
                    $idName => ['type' => 'integer', 'description' => $idDesc],
                    'reaction_id' => ['type' => 'integer', 'description' => 'Reaction id (default 1 = Like)'],
                ],
            ];
        };

        $this->registerTool('reactToPost', [
            'description' => 'React to a post (toggle; default Like)',
            'input_schema' => $reactSchema('post_id', 'Post to react to'),
        ]);
        $this->registerTool('reactToProfilePost', [
            'description' => 'React to a profile post (toggle; default Like)',
            'input_schema' => $reactSchema('profile_post_id', 'Profile post to react to'),
        ]);
        $this->registerTool('reactToConversationMsg', [
            'description' => 'React to a conversation message (toggle; default Like)',
            'input_schema' => $reactSchema('message_id', 'Conversation message to react to'),
        ]);
        $this->registerTool('votePost', [
            'description' => 'Up/down vote a post in a question thread',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id', 'vote'],
                'properties' => [
                    'post_id' => ['type' => 'integer', 'description' => 'Post to vote on'],
                    'vote' => ['type' => 'string', 'description' => 'up or down'],
                ],
            ],
        ]);
        $this->registerTool('votePoll', [
            'description' => 'Vote on a thread poll',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id', 'response_ids'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread whose poll to vote on'],
                    'response_ids' => ['type' => 'array', 'description' => 'Poll response ids to vote for'],
                ],
            ],
        ]);
        $this->registerTool('createPoll', [
            'description' => 'Add a poll to an existing thread',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id', 'question', 'responses'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread to attach the poll to'],
                    'question' => ['type' => 'string', 'description' => 'Poll question'],
                    'responses' => ['type' => 'array', 'description' => 'Answer options (array of strings)'],
                    'max_votes' => ['type' => 'integer', 'description' => 'Max selectable options (default 1)'],
                ],
            ],
        ]);
        $this->registerTool('markThreadRead', [
            'description' => 'Mark a thread as read for the current user',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => ['thread_id' => ['type' => 'integer', 'description' => 'Thread to mark read']],
            ],
        ]);
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- dynamic dispatch execute_<name>

    public function execute_reactToPost($params)
    {
        return $this->reactTo('post', $params['post_id'], $params['reaction_id'] ?? 1);
    }

    public function execute_reactToProfilePost($params)
    {
        return $this->reactTo('profile_post', $params['profile_post_id'], $params['reaction_id'] ?? 1);
    }

    public function execute_reactToConversationMsg($params)
    {
        return $this->reactTo('conversation_message', $params['message_id'], $params['reaction_id'] ?? 1);
    }

    public function execute_votePost($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $vote = strtolower((string) $params['vote']);
        if (!in_array($vote, ['up', 'down'], true)) {
            return $this->error('invalid_param', 'vote must be "up" or "down"');
        }
        $post = \XF::em()->find('XF:Post', $params['post_id']);
        if (!$post) {
            return $this->error('not_found', 'Post not found');
        }
        $error = null;
        if (!$post->canVoteOnContent($error)) {
            return $this->error('no_permission', $error ?: 'You cannot vote on this post');
        }
        $voteType = $vote === 'up' ? ContentVote::VOTE_UP : ContentVote::VOTE_DOWN;
        $voteRepo = \XF::repository('XF:ContentVote');
        $voteRepo->vote('post', (int) $post->post_id, $voteType);
        $post = \XF::em()->find('XF:Post', $params['post_id']);
        return $this->success(['post_id' => $post->post_id, 'vote_score' => $post->vote_score]);
    }

    public function execute_votePoll($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        if (!is_array($params['response_ids'])) {
            return $this->error('invalid_param', 'response_ids must be an array');
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread || !$thread->Poll) {
            return $this->error('not_found', 'Thread has no poll');
        }
        $poll = $thread->Poll;
        $error = null;
        if (!$poll->canVote($error)) {
            return $this->error('no_permission', $error ?: 'You cannot vote on this poll');
        }
        $voter = \XF::service('XF:Poll\Voter', $poll, array_map('intval', $params['response_ids']));
        if (!$voter->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $voter->save();
        return $this->success(['thread_id' => $thread->thread_id, 'voted' => true]);
    }

    public function execute_createPoll($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        if (!is_array($params['responses']) || count($params['responses']) < 2) {
            return $this->error('invalid_param', 'responses must be an array of at least 2 options');
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        if ($thread->Poll) {
            return $this->error('invalid_param', 'Thread already has a poll');
        }
        $error = null;
        if (!$thread->canCreatePoll($error)) {
            return $this->error('no_permission', $error ?: 'You cannot create a poll on this thread');
        }
        $creator = \XF::service('XF:Poll\Creator', 'thread', $thread);
        $creator->setQuestion($params['question']);
        $creator->addResponses(array_values($params['responses']));
        $creator->setMaxVotes('positive', max(1, (int) ($params['max_votes'] ?? 1)));
        if (!$creator->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $poll = $creator->save();
        return $this->success(['thread_id' => $thread->thread_id, 'poll_id' => $poll->poll_id]);
    }

    public function execute_markThreadRead($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        \XF::repository('XF:Thread')->markThreadReadByUser($thread, \XF::visitor());
        return $this->success(['thread_id' => $thread->thread_id, 'read' => true]);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    private function reactTo(string $contentType, $contentId, $reactionId)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $entityMap = [
            'post' => 'XF:Post',
            'profile_post' => 'XF:ProfilePost',
            'conversation_message' => 'XF:ConversationMessage',
        ];
        $content = \XF::em()->find($entityMap[$contentType], $contentId);
        if (!$content) {
            return $this->error('not_found', 'Content not found');
        }
        if (method_exists($content, 'canReact') && !$content->canReact()) {
            return $this->error('no_permission', 'You cannot react to this content');
        }
        $reactionRepo = \XF::repository('XF:Reaction');
        $result = $reactionRepo->reactToContent((int) $reactionId, $contentType, (int) $contentId, \XF::visitor());
        return $this->success([
            'content_type' => $contentType,
            'content_id' => (int) $contentId,
            'reacted' => $result !== null,
        ]);
    }
}
