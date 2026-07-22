<?php

namespace chgold\AIConnectPro\Module;

/**
 * Profile-post engagement tools for the Pro module (6 tools): profile posts,
 * their comments, reading a user's profile posts, and editing your own profile.
 * Split into a trait to keep ProModule under the file-size ceiling.
 */
trait ProProfileTrait
{
    protected function registerProfileTools()
    {
        $this->registerTool('postProfileMessage', [
            'description' => 'Post a message on a user profile',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id', 'message'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Owner of the profile to post on'],
                    'message' => ['type' => 'string', 'description' => 'Message content'],
                ],
            ],
        ]);
        $this->registerTool('editProfilePost', [
            'description' => 'Edit a profile post',
            'input_schema' => [
                'type' => 'object',
                'required' => ['profile_post_id', 'message'],
                'properties' => [
                    'profile_post_id' => ['type' => 'integer', 'description' => 'Profile post to edit'],
                    'message' => ['type' => 'string', 'description' => 'New content'],
                ],
            ],
        ]);
        $this->registerTool('commentOnProfilePost', [
            'description' => 'Comment on a profile post',
            'input_schema' => [
                'type' => 'object',
                'required' => ['profile_post_id', 'message'],
                'properties' => [
                    'profile_post_id' => ['type' => 'integer', 'description' => 'Profile post to comment on'],
                    'message' => ['type' => 'string', 'description' => 'Comment content'],
                ],
            ],
        ]);
        $this->registerTool('editProfilePostComment', [
            'description' => 'Edit a profile post comment',
            'input_schema' => [
                'type' => 'object',
                'required' => ['comment_id', 'message'],
                'properties' => [
                    'comment_id' => ['type' => 'integer', 'description' => 'Comment to edit'],
                    'message' => ['type' => 'string', 'description' => 'New content'],
                ],
            ],
        ]);
        $this->registerTool('getProfilePosts', [
            'description' => 'List profile posts on a user profile',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Profile owner user id'],
                    'limit' => ['type' => 'integer', 'description' => 'Max posts (default 20)'],
                ],
            ],
        ]);
        $this->registerTool('updateMyProfile', [
            'description' => 'Update your own profile (about text)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['about'],
                'properties' => ['about' => ['type' => 'string', 'description' => 'New about text']],
            ],
        ]);
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- dynamic dispatch execute_<name>

    public function execute_postProfileMessage($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        $profile = $user->Profile;
        if (!$profile) {
            return $this->error('not_found', 'User profile not found');
        }
        if (!$user->canPostOnProfile()) {
            return $this->error('no_permission', 'You cannot post on this profile');
        }
        $creator = \XF::service('XF:ProfilePost\Creator', $profile);
        $creator->setContent($params['message']);
        if (!$creator->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $post = $creator->save();
        return $this->success(['profile_post_id' => $post->profile_post_id, 'user_id' => (int) $params['user_id']]);
    }

    public function execute_editProfilePost($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $pp = \XF::em()->find('XF:ProfilePost', $params['profile_post_id']);
        if (!$pp) {
            return $this->error('not_found', 'Profile post not found');
        }
        if (!$pp->canEdit()) {
            return $this->error('no_permission', 'You cannot edit this profile post');
        }
        $editor = \XF::service('XF:ProfilePost\Editor', $pp);
        $editor->setMessage($params['message']);
        if (!$editor->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $editor->save();
        return $this->success(['profile_post_id' => $pp->profile_post_id]);
    }

    public function execute_commentOnProfilePost($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $pp = \XF::em()->find('XF:ProfilePost', $params['profile_post_id']);
        if (!$pp) {
            return $this->error('not_found', 'Profile post not found');
        }
        $error = null;
        if (!$pp->canComment($error)) {
            return $this->error('no_permission', $error ?: 'You cannot comment on this profile post');
        }
        $creator = \XF::service('XF:ProfilePostComment\Creator', $pp);
        $creator->setContent($params['message']);
        if (!$creator->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $comment = $creator->save();
        return $this->success(['comment_id' => $comment->profile_post_comment_id, 'profile_post_id' => $pp->profile_post_id]);
    }

    public function execute_editProfilePostComment($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $comment = \XF::em()->find('XF:ProfilePostComment', $params['comment_id']);
        if (!$comment) {
            return $this->error('not_found', 'Comment not found');
        }
        if (!$comment->canEdit()) {
            return $this->error('no_permission', 'You cannot edit this comment');
        }
        $editor = \XF::service('XF:ProfilePostComment\Editor', $comment);
        $editor->setMessage($params['message']);
        if (!$editor->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $editor->save();
        return $this->success(['comment_id' => $comment->profile_post_comment_id]);
    }

    public function execute_getProfilePosts($params)
    {
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        $limit = max(1, min(50, (int) ($params['limit'] ?? 20)));
        $finder = \XF::repository('XF:ProfilePost')->findProfilePostsOnProfile($user);
        $finder->limit($limit);
        $out = [];
        foreach ($finder->fetch() as $pp) {
            if (!$pp->canView()) {
                continue;
            }
            $out[] = [
                'profile_post_id' => $pp->profile_post_id,
                'user_id' => $pp->user_id,
                'username' => $pp->username,
                'message' => $pp->message,
                'post_date' => $pp->post_date,
            ];
        }
        return $this->success($out);
    }

    public function execute_updateMyProfile($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $visitor = \XF::visitor();
        $profile = $visitor->Profile;
        if (!$profile) {
            return $this->error('not_found', 'Your profile could not be loaded');
        }
        $profile->about = (string) $params['about'];
        if (!$profile->preSave()) {
            return $this->error('validation_failed', implode(' ', $profile->getErrors()));
        }
        $profile->save();
        return $this->success(['user_id' => $visitor->user_id, 'about' => $profile->about]);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
}
