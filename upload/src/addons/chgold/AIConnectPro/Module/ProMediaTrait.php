<?php

namespace chgold\AIConnectPro\Module;

use XF\Attachment\Manipulator;
use XF\Http\Upload;
use XF\Util\File;

/**
 * Media tools for the Pro module (9 tools): attachment upload/read/delete and
 * user avatar management. Files arrive as a URL to fetch (AI tools use JSON,
 * not multipart uploads). Split into a trait to keep ProModule under the
 * file-size ceiling. All rely on XenForo's own permission checks.
 */
trait ProMediaTrait
{
    protected function registerMediaTools()
    {
        $this->registerTool('uploadAttachment', [
            'description' => 'Upload an attachment from a URL, returning a temp hash to attach to a thread/post/conversation',
            'input_schema' => [
                'type' => 'object',
                'required' => ['content_type', 'file_url'],
                'properties' => [
                    'content_type' => ['type' => 'string', 'description' => 'Attachment context, e.g. post, conversation_message'],
                    'file_url' => ['type' => 'string', 'description' => 'Publicly reachable URL of the file to attach'],
                    'filename' => ['type' => 'string', 'description' => 'Filename to use (optional; inferred from URL)'],
                    'hash' => ['type' => 'string', 'description' => 'Existing temp hash to append to (optional)'],
                ],
            ],
        ]);
        $this->registerTool('deleteAttachment', [
            'description' => 'Delete an attachment by id',
            'input_schema' => [
                'type' => 'object',
                'required' => ['attachment_id'],
                'properties' => ['attachment_id' => ['type' => 'integer', 'description' => 'Attachment to delete']],
            ],
        ]);
        $this->registerTool('listAttachmentsByKey', [
            'description' => 'List temporary attachments uploaded under a temp hash',
            'input_schema' => [
                'type' => 'object',
                'required' => ['hash'],
                'properties' => ['hash' => ['type' => 'string', 'description' => 'Temp hash returned by uploadAttachment']],
            ],
        ]);
        $this->registerTool('getAttachmentInfo', [
            'description' => 'Get metadata for a single attachment',
            'input_schema' => [
                'type' => 'object',
                'required' => ['attachment_id'],
                'properties' => ['attachment_id' => ['type' => 'integer', 'description' => 'Attachment id']],
            ],
        ]);
        $this->registerTool('getAttachmentThumbnail', [
            'description' => 'Get the thumbnail URL of an attachment',
            'input_schema' => [
                'type' => 'object',
                'required' => ['attachment_id'],
                'properties' => ['attachment_id' => ['type' => 'integer', 'description' => 'Attachment id']],
            ],
        ]);
        $this->registerTool('uploadMyAvatar', [
            'description' => 'Set your own avatar from a URL',
            'input_schema' => [
                'type' => 'object',
                'required' => ['file_url'],
                'properties' => ['file_url' => ['type' => 'string', 'description' => 'Publicly reachable image URL']],
            ],
        ]);
        $this->registerTool('deleteMyAvatar', [
            'description' => 'Delete your own avatar',
            'input_schema' => ['type' => 'object', 'properties' => new \stdClass()],
        ]);
        $this->registerTool('uploadUserAvatar', [
            'description' => 'Set another user\'s avatar from a URL (admin)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id', 'file_url'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Target user id'],
                    'file_url' => ['type' => 'string', 'description' => 'Publicly reachable image URL'],
                ],
            ],
        ]);
        $this->registerTool('deleteUserAvatar', [
            'description' => 'Delete another user\'s avatar (admin)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => ['user_id' => ['type' => 'integer', 'description' => 'Target user id']],
            ],
        ]);
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- dynamic dispatch execute_<name>

    public function execute_uploadAttachment($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $repo = \XF::repository('XF:Attachment');
        $handler = $repo->getAttachmentHandler($params['content_type']);
        if (!$handler) {
            return $this->error('invalid_param', 'Unknown attachment content_type');
        }
        $context = [];
        $hash = (isset($params['hash']) && strlen($params['hash']) <= 32)
            ? $params['hash']
            : md5(microtime(true) . \XF::generateRandomString(8, true));

        $error = null;
        if (!$handler->canManageAttachments($context, $error)) {
            return $this->error('no_permission', $error ?: 'You cannot upload attachments here');
        }

        $upload = $this->fetchUpload($params['file_url'], $params['filename'] ?? null, $fetchError);
        if (!$upload) {
            return $this->error('upload_failed', $fetchError);
        }

        $class = \XF::extendClass(Manipulator::class);
        /** @var Manipulator $manipulator */
        $manipulator = new $class($handler, $repo, $context, $hash);
        if (!$manipulator->canUpload($uploadError)) {
            return $this->error('no_permission', $uploadError ?: 'Upload not allowed');
        }
        $attachment = $manipulator->insertAttachmentFromUpload($upload, $insertError);
        if (!$attachment) {
            return $this->error('upload_failed', $insertError ?: 'Attachment could not be created');
        }
        return $this->success([
            'attachment_id' => $attachment->attachment_id,
            'hash' => $hash,
            'filename' => $attachment->filename,
        ]);
    }

    public function execute_deleteAttachment($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $attachment = \XF::em()->find('XF:Attachment', $params['attachment_id']);
        if (!$attachment) {
            return $this->error('not_found', 'Attachment not found');
        }
        $attachment->delete();
        return $this->success(['attachment_id' => (int) $params['attachment_id'], 'deleted' => true]);
    }

    public function execute_listAttachmentsByKey($params)
    {
        $finder = \XF::repository('XF:Attachment')->findAttachmentsByTempHash($params['hash']);
        $out = [];
        foreach ($finder->fetch() as $attachment) {
            $out[] = $this->attachmentInfo($attachment);
        }
        return $this->success($out);
    }

    public function execute_getAttachmentInfo($params)
    {
        $attachment = \XF::em()->find('XF:Attachment', $params['attachment_id']);
        if (!$attachment || !$attachment->canView()) {
            return $this->error('not_found', 'Attachment not found');
        }
        return $this->success($this->attachmentInfo($attachment));
    }

    public function execute_getAttachmentThumbnail($params)
    {
        $attachment = \XF::em()->find('XF:Attachment', $params['attachment_id']);
        if (!$attachment || !$attachment->canView()) {
            return $this->error('not_found', 'Attachment not found');
        }
        return $this->success([
            'attachment_id' => $attachment->attachment_id,
            'thumbnail_url' => $attachment->getThumbnailUrlFull(),
        ]);
    }

    public function execute_uploadMyAvatar($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        return $this->setAvatarFromUrl(\XF::visitor(), $params['file_url']);
    }

    public function execute_deleteMyAvatar($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        return $this->deleteAvatarFor(\XF::visitor());
    }

    public function execute_uploadUserAvatar($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        return $this->setAvatarFromUrl($user, $params['file_url']);
    }

    public function execute_deleteUserAvatar($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        return $this->deleteAvatarFor($user);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    private function fetchUpload(string $url, ?string $filename, &$error): ?Upload
    {
        $error = null;
        $contents = @file_get_contents($url);
        if ($contents === false) {
            $error = 'Could not fetch file from URL';
            return null;
        }
        $temp = File::getTempFile();
        if (!$temp || file_put_contents($temp, $contents) === false) {
            $error = 'Could not buffer downloaded file';
            return null;
        }
        $name = $filename ?: (basename(parse_url($url, PHP_URL_PATH)) ?: 'upload.dat');
        return new Upload($temp, $name);
    }

    private function setAvatarFromUrl($user, string $url)
    {
        if (!$user->canUploadAvatar()) {
            return $this->error('no_permission', 'Avatar uploads are not allowed for this user');
        }
        $upload = $this->fetchUpload($url, 'avatar.jpg', $fetchError);
        if (!$upload) {
            return $this->error('upload_failed', $fetchError);
        }
        $avatarService = \XF::service('XF:User\Avatar', $user);
        $avatarService->setImageFromUpload($upload);
        if (!$avatarService->updateAvatar()) {
            return $this->error('upload_failed', 'Avatar could not be updated');
        }
        return $this->success(['user_id' => $user->user_id, 'avatar' => true]);
    }

    private function deleteAvatarFor($user)
    {
        $avatarService = \XF::service('XF:User\Avatar', $user);
        $avatarService->deleteAvatar();
        return $this->success(['user_id' => $user->user_id, 'avatar' => false]);
    }

    private function attachmentInfo($attachment): array
    {
        return [
            'attachment_id' => $attachment->attachment_id,
            'content_type' => $attachment->content_type,
            'content_id' => $attachment->content_id,
            'filename' => $attachment->filename,
            'file_size' => $attachment->file_size,
            'view_count' => $attachment->view_count,
            'direct_url' => $attachment->getDirectUrl(),
        ];
    }
}
