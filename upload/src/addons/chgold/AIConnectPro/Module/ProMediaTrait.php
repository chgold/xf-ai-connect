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
                    'node_id' => ['type' => 'integer', 'description' => 'Target forum, when attaching to a NEW post'],
                    'thread_id' => ['type' => 'integer', 'description' => 'Target thread, when replying'],
                    'post_id' => ['type' => 'integer', 'description' => 'Target post, when editing an existing post'],
                    'conversation_id' => ['type' => 'integer', 'description' => 'Target conversation, for conversation_message'],
                    'message_id' => ['type' => 'integer', 'description' => 'Target conversation message, when editing one'],
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
        $tempHashParam = [
            'type' => 'string',
            'description' => 'Temp hash from uploadAttachment — required only while the file is not yet attached',
        ];

        $this->registerTool('getAttachmentInfo', [
            'description' => 'Get metadata for a single attachment',
            'input_schema' => [
                'type' => 'object',
                'required' => ['attachment_id'],
                'properties' => [
                    'attachment_id' => ['type' => 'integer', 'description' => 'Attachment id'],
                    'hash' => $tempHashParam,
                ],
            ],
        ]);
        $this->registerTool('getPostAttachments', [
            'description' => 'List the files attached to a post, with filenames, sizes and direct URLs',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => ['post_id' => ['type' => 'integer', 'description' => 'Post id to read attachments from']],
            ],
        ]);
        $this->registerTool('readAttachmentText', [
            'description' => 'Read the text of an attachment: plain text formats (txt, csv, tsv, json, xml, md, log, '
                . 'ini, yml, html, css, sql) and PDF documents, whose text is extracted automatically. Scanned or '
                . 'image-only PDFs hold no text and are reported as such. Other binary files are refused — use '
                . 'getAttachmentInfo for those.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['attachment_id'],
                'properties' => [
                    'attachment_id' => ['type' => 'integer', 'description' => 'Attachment id to read'],
                    'hash' => $tempHashParam,
                    'max_bytes' => [
                        'type' => 'integer',
                        'description' => 'Maximum bytes to return (default 100000, hard cap 1000000)',
                    ],
                ],
            ],
        ]);
        $this->registerTool('getAttachmentThumbnail', [
            'description' => 'Get the thumbnail URL of an attachment',
            'input_schema' => [
                'type' => 'object',
                'required' => ['attachment_id'],
                'properties' => [
                    'attachment_id' => ['type' => 'integer', 'description' => 'Attachment id'],
                    'hash' => $tempHashParam,
                ],
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
            'input_schema' => ['type' => 'object', 'properties' => new \stdClass(), 'additionalProperties' => false],
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
        // XenForo's attachment handlers resolve permission from the CONTAINER,
        // not from the user alone: the post handler looks up the forum behind a
        // post_id / thread_id / node_id and asks whether attachments are allowed
        // there. An empty context therefore always failed with
        // "You cannot upload attachments here" — which is why uploading to a
        // post never worked while conversation_message did (a conversation_id
        // was not needed to reach a permissive default in that path).
        $context = [];
        foreach (['post_id', 'thread_id', 'node_id', 'conversation_id', 'message_id'] as $key) {
            if (!empty($params[$key])) {
                $context[$key] = (int) $params[$key];
            }
        }

        $hash = (isset($params['hash']) && strlen($params['hash']) <= 32)
            ? $params['hash']
            : md5(microtime(true) . \XF::generateRandomString(8, true));

        $error = null;
        if (!$handler->canManageAttachments($context, $error)) {
            if (!$context) {
                return $this->error(
                    'invalid_param',
                    'Uploading to "' . $params['content_type'] . '" needs a target: pass node_id or thread_id '
                    . '(new post), post_id (editing a post), or conversation_id / message_id for a conversation.'
                );
            }
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

        // SECURITY: an attachment id alone must never authorise deletion.
        // Without this check any authenticated caller could delete ANY
        // attachment on the board — including other members' files — just by
        // iterating ids. Authorisation is delegated to the content handler that
        // owns the attachment (the same mechanism XF's own attachment
        // controller uses), so the underlying post/node permissions apply.
        $handler = \XF::repository('XF:Attachment')
            ->getAttachmentHandler($attachment->content_type);
        if (!$handler) {
            return $this->error('no_permission', 'Unknown attachment content type');
        }

        $error   = null;
        $context = ['content_id' => $attachment->content_id];
        if (!$handler->canManageAttachments($context, $error)) {
            return $this->error('no_permission', $error ?: 'You cannot delete this attachment');
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
        $attachment = $this->loadAccessibleAttachment($params, $error);
        if (!$attachment) {
            return $error;
        }
        return $this->success($this->attachmentInfo($attachment));
    }

    public function execute_getPostAttachments($params)
    {
        $post = \XF::em()->find('XF:Post', $params['post_id']);
        // canView() on the post enforces node/thread permissions, so a caller
        // cannot enumerate attachments in a forum they cannot read.
        if (!$post || !$post->canView()) {
            return $this->error('not_found', 'Post not found');
        }

        $out = [];
        foreach ($post->Attachments as $attachment) {
            if (!$attachment->canView()) {
                continue;
            }
            $out[] = $this->attachmentInfo($attachment);
        }

        return $this->success([
            'post_id'     => (int) $post->post_id,
            'thread_id'   => (int) $post->thread_id,
            'count'       => count($out),
            'attachments' => $out,
        ]);
    }

    public function execute_readAttachmentText($params)
    {
        $attachment = $this->loadAccessibleAttachment($params, $error);
        if (!$attachment) {
            return $error;
        }

        // Text-only by design. Returning raw bytes of a binary file would waste
        // the agent's context and produce meaningless output, so refuse early
        // and point the caller at the metadata tool instead.
        $textExtensions = [
            'txt', 'csv', 'tsv', 'json', 'xml', 'md', 'markdown',
            'log', 'ini', 'yml', 'yaml', 'html', 'htm', 'css', 'sql',
        ];
        $ext = strtolower((string) $attachment->extension);
        if ($ext !== 'pdf' && !in_array($ext, $textExtensions, true)) {
            return $this->error(
                'unsupported_type',
                'Attachment is not a readable text file (.' . $ext . '). Supported: '
                . implode(', ', $textExtensions) . ', pdf. Use getAttachmentInfo for other files.'
            );
        }

        $data = $attachment->Data;
        if (!$data) {
            return $this->error('not_found', 'Attachment data is missing');
        }

        $maxBytes = isset($params['max_bytes']) ? (int) $params['max_bytes'] : 100000;
        $maxBytes = max(1, min($maxBytes, 1000000));

        $path = $data->getAbstractedDataPath();
        if (!\XF::fs()->has($path)) {
            return $this->error('not_found', 'Attachment file is missing from storage');
        }

        $content = (string) \XF::fs()->read($path);

        if ($ext === 'pdf') {
            $content = $this->extractPdfText($content, $pdfError);
            if ($content === null) {
                return $this->error('unsupported_type', $pdfError);
            }
        }
        $totalSize = strlen($content);
        $truncated = $totalSize > $maxBytes;
        if ($truncated) {
            $content = substr($content, 0, $maxBytes);
        }

        // Guarantee valid UTF-8 so the JSON response cannot fail to encode on a
        // file saved in another encoding.
        if (!preg_match('//u', $content)) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }

        return $this->success([
            'attachment_id' => (int) $attachment->attachment_id,
            'filename'      => $attachment->filename,
            'extension'     => $ext,
            'file_size'     => (int) $attachment->file_size,
            'returned_bytes' => strlen($content),
            'truncated'     => $truncated,
            'content'       => $content,
        ]);
    }

    public function execute_getAttachmentThumbnail($params)
    {
        $attachment = $this->loadAccessibleAttachment($params, $error);
        if (!$attachment) {
            return $error;
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

    /**
     * Loads an attachment for the read tools, telling apart the three cases the
     * caller actually needs to distinguish.
     *
     * XF\Entity\Attachment::canView() returns false for ANY attachment that
     * still carries a temp_hash — an upload that has not yet been posted. The
     * tools therefore answered "Attachment not found" for a file the caller had
     * just uploaded and could see through listAttachmentsByKey, which read as a
     * bug rather than as a state.
     *
     * A temporary attachment is now readable by whoever holds its temp hash —
     * proof they own the upload — and anyone else still gets the plain
     * "not found", so attachment IDs cannot be probed.
     *
     * @param array $params  tool params: attachment_id, optional hash
     * @param array|null $error  populated with the error payload on failure
     */
    private function loadAccessibleAttachment(array $params, &$error)
    {
        $error = null;
        $attachment = \XF::em()->find('XF:Attachment', $params['attachment_id']);

        if (!$attachment) {
            $error = $this->error('not_found', 'Attachment not found');
            return null;
        }

        if ($attachment->canView()) {
            return $attachment;
        }

        if ($attachment->temp_hash) {
            $supplied = isset($params['hash']) ? trim((string) $params['hash']) : '';
            if ($supplied !== '' && hash_equals((string) $attachment->temp_hash, $supplied)) {
                return $attachment;
            }
            $error = $this->error(
                'not_attached',
                'This attachment is still temporary — it has been uploaded but not yet attached to a post or '
                . 'conversation. Pass the "hash" returned by uploadAttachment to inspect it, or attach it first.'
            );
            return null;
        }

        $error = $this->error('not_found', 'Attachment not found');
        return null;
    }

    /**
     * Extracts readable text from a PDF.
     *
     * Deliberately dependency-free: an add-on cannot assume anything is
     * installed on a customer's server, so this uses pdftotext when the host
     * happens to provide it and otherwise parses the file itself.
     *
     * The built-in parser walks the PDF's content streams, inflates the ones
     * that are Flate-compressed, and pulls the strings out of the text-showing
     * operators. That covers ordinary text PDFs. It cannot read a scanned
     * document, which holds images rather than text and would need OCR — in
     * that case the caller is told so plainly instead of receiving noise.
     *
     * @param string $raw    the PDF bytes
     * @param string|null $error  set to a caller-facing explanation on failure
     * @return string|null   extracted text, or null when nothing readable exists
     */
    private function extractPdfText(string $raw, &$error): ?string
    {
        $error = null;

        $viaBinary = $this->extractPdfViaBinary($raw);
        if ($viaBinary !== null && trim($viaBinary) !== '') {
            return $viaBinary;
        }

        $text = '';
        // Content lives in stream objects; inflate each one we can and collect
        // the text-showing operators from it.
        if (preg_match_all('/stream\r?\n(.*?)endstream/s', $raw, $streams)) {
            foreach ($streams[1] as $stream) {
                $chunk = @gzuncompress($stream);
                if ($chunk === false) {
                    $chunk = @gzinflate($stream);
                }
                if ($chunk === false) {
                    // Uncompressed streams are legal too.
                    $chunk = $stream;
                }
                if (strpos($chunk, 'Tj') === false && strpos($chunk, 'TJ') === false) {
                    continue;
                }
                $text .= $this->pdfStreamToText($chunk);
            }
        }

        $text = trim(preg_replace('/[ \t]+/', ' ', $text));

        if ($text === '') {
            $error = 'This PDF contains no extractable text. It is most likely a scan or image-only document, '
                   . 'which needs OCR rather than text extraction.';
            return null;
        }

        return $text;
    }

    /** Uses pdftotext when the host provides it; returns null when unavailable. */
    private function extractPdfViaBinary(string $raw): ?string
    {
        if (!function_exists('shell_exec')) {
            return null;
        }
        $which = @shell_exec('command -v pdftotext 2>/dev/null');
        if (!$which || trim($which) === '') {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'aic_pdf_');
        if (!$tmp || file_put_contents($tmp, $raw) === false) {
            return null;
        }

        $out = @shell_exec('pdftotext -layout -enc UTF-8 ' . escapeshellarg($tmp) . ' - 2>/dev/null');
        @unlink($tmp);

        return is_string($out) ? $out : null;
    }

    /**
     * Pulls the string operands out of a PDF content stream's text operators.
     *
     * Operators are matched in a single pass so the output keeps the order they
     * appear in the document. Handling each operator type in its own pass would
     * reorder the text — every TJ array ahead of every Tj string — and produce
     * scrambled output on any page that mixes them.
     */
    private function pdfStreamToText(string $chunk): string
    {
        $string  = '\(((?:\\\\.|[^\\\\()])*)\)';
        $pattern = '/\[((?:' . $string . '|[^\]])*)\]\s*TJ'   // [(He)-30(llo)] TJ
                 . '|' . $string . '\s*Tj'                     // (Hello) Tj
                 . '|\b(Td|TD|T\*)\b/s';                       // line positioning

        if (!preg_match_all($pattern, $chunk, $matches, PREG_SET_ORDER)) {
            return '';
        }

        $text = '';
        foreach ($matches as $m) {
            if (($m[1] ?? '') !== '') {
                // TJ array: concatenate its pieces, ignoring the kerning numbers.
                if (preg_match_all('/' . $string . '/s', $m[1], $parts)) {
                    foreach ($parts[1] as $part) {
                        $text .= $this->decodePdfString($part);
                    }
                }
                $text .= ' ';
            } elseif (($m[3] ?? '') !== '') {
                $text .= $this->decodePdfString($m[3]) . ' ';
            } elseif (($m[4] ?? '') !== '') {
                $text .= "\n";
            }
        }

        return $text;
    }

    /** Resolves PDF string escapes (\n, \(, \\, and octal codes). */
    private function decodePdfString(string $s): string
    {
        $map = ['\\n' => "\n", '\\r' => "\r", '\\t' => "\t", '\\b' => "\b",
                '\\f' => "\f", '\\(' => '(', '\\)' => ')', '\\\\' => '\\'];
        $s = strtr($s, $map);

        return preg_replace_callback('/\\\\([0-7]{1,3})/', static function ($m) {
            return chr(octdec($m[1]));
        }, $s);
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
