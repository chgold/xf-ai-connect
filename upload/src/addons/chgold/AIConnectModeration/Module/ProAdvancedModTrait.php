<?php

namespace chgold\AIConnectModeration\Module;

/**
 * Advanced moderation bundle: 2 tools — mergePosts + splitPosts.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProAdvancedModTrait
{
    protected function registerAdvancedModTools()
    {
        $this->registerTool('mergePosts', [
            'description' => 'Merge multiple posts into one target post. The target absorbs the messages of the others (with attribution). Sources are deleted.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['target_post_id', 'source_post_ids'],
                'properties' => [
                    'target_post_id' => ['type' => 'integer', 'description' => 'Post that will contain the merged content'],
                    'source_post_ids' => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                        'description' => 'Posts to merge into target (will be deleted after merge)',
                        'minItems' => 1,
                    ],
                    'separator' => ['type' => 'string', 'description' => 'Separator between merged posts (default: newline)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('splitPosts', [
            'description' => 'Split posts from an existing thread into a new thread.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_ids', 'new_thread_title', 'target_forum_id'],
                'properties' => [
                    'post_ids' => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                        'description' => 'Post IDs to move to a new thread',
                        'minItems' => 1,
                    ],
                    'new_thread_title' => ['type' => 'string', 'description' => 'Title for the new thread'],
                    'target_forum_id' => ['type' => 'integer', 'description' => 'Forum node_id for the new thread'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_mergePosts($params)
    {
        $targetPost = \XF::em()->find('XF:Post', (int) $params['target_post_id']);
        if (!$targetPost) {
            return $this->error('not_found', 'Target post not found');
        }

        $sourceIds = array_values(array_map('intval', (array) $params['source_post_ids']));
        $sources = \XF::em()->findByIds('XF:Post', $sourceIds);
        if (count($sources) !== count($sourceIds)) {
            $missing = array_diff($sourceIds, array_keys($sources->toArray()));
            return $this->error('not_found', 'Source posts not found: ' . implode(',', $missing));
        }

        /** @var \XF\Service\Post\MergerService $svc */
        $svc = \XF::service('XF:Post\Merger', $targetPost);
        if (!empty($params['separator'])) {
            $svc->setMessageSeparator((string) $params['separator']);
        }
        $svc->merge($sources);

        return $this->success([
            'target_post_id' => $targetPost->post_id,
            'merged_count'   => count($sourceIds),
            'source_post_ids' => $sourceIds,
        ]);
    }

    public function execute_splitPosts($params)
    {
        $postIds = array_values(array_map('intval', (array) $params['post_ids']));
        $posts = \XF::em()->findByIds('XF:Post', $postIds);
        if (count($posts) !== count($postIds)) {
            $missing = array_diff($postIds, array_keys($posts->toArray()));
            return $this->error('not_found', 'Posts not found: ' . implode(',', $missing));
        }

        $forum = \XF::em()->find('XF:Forum', (int) $params['target_forum_id']);
        if (!$forum) {
            return $this->error('not_found', 'Target forum not found');
        }

        // MoverService's second constructor arg is the TARGET Thread (posts move INTO
        // it), not the posts. To split into a NEW thread we create an unsaved target
        // Thread in the forum, then setExistingTarget(false) + move($posts) — the exact
        // pattern XF core uses in XF\InlineMod\Post\Move. The previous code passed the
        // post ArrayCollection as the target (TypeError) and called a non-existent
        // moveToNewThread(), so the tool always 500'd.
        $targetThread = \XF::em()->create('XF:Thread');
        $targetThread->title = (string) $params['new_thread_title'];
        $targetThread->node_id = $forum->node_id;

        /** @var \XF\Service\Post\MoverService $svc */
        $svc = \XF::service('XF:Post\Mover', $targetThread);
        $svc->setExistingTarget(false);
        if (!$svc->move($posts)) {
            return $this->error('move_failed', 'It is not possible to move any of the selected posts to a new thread.');
        }

        $newThread = $svc->getTarget();

        return $this->success([
            'post_ids' => $postIds,
            'new_thread_id' => (int) $newThread->thread_id,
            'new_thread_title' => (string) $newThread->title,
            'target_forum_id' => (int) $forum->node_id,
        ]);
    }
}
