<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Appearance bundle: Style properties (CSS vars) + Style assets (logo etc).
 * Group A tools: safe, easy to rollback.
 * Group B tools: upload/delete assets with size + type validation.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminAppearanceTrait
{
    protected function registerAppearanceTools()
    {
        // Style properties — CSS vars, safer than templates
        $this->registerTool('listStyleProperties', [
            'description' => 'List CSS-level style properties for a style '
                . '(colors, font sizes, spacing). Editable via setStyleProperty '
                . 'without touching raw template code — the safe way to restyle.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'style_id' => ['type' => 'integer', 'description' => 'Style to inspect (default: master 0)'],
                    'group_name' => ['type' => 'string', 'description' => 'Filter to a property group (color, general, etc)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('setStyleProperty', [
            'description' => 'Set a single CSS property value on a style. '
                . 'Changes take effect after the style asset build (auto). '
                . 'Prefer this over editing raw templates when adjusting colors/fonts/spacing.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['property_name', 'value'],
                'properties' => [
                    'property_name' => ['type' => 'string', 'description' => 'e.g. publicColorPrimary, fontSizeNormal'],
                    'value' => ['description' => 'New value — string or JSON for structured props (colors, dims)'],
                    'style_id' => ['type' => 'integer', 'description' => 'Style ID (default 0 = master)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        // Site logo (special style asset)
        $this->registerTool('uploadSiteLogo', [
            'description' => 'Upload/replace the site logo from a URL. Validates file '
                . 'type (png/jpg/svg/webp) and max size (5MB). Applies to the given style '
                . 'or master (style_id=0) by default.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['file_url'],
                'properties' => [
                    'file_url' => ['type' => 'string'],
                    'style_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('deleteSiteLogo', [
            'description' => 'Remove the logo from a style (reverts to default/no logo).',
            'input_schema' => [
                'type' => 'object',
                'properties' => ['style_id' => ['type' => 'integer']],
                'additionalProperties' => false,
            ],
        ]);

        // Generic style asset management
        $this->registerTool('listStyleAssets', [
            'description' => 'List style asset files (logos, favicons, custom images, backgrounds) '
                . 'for a style. Read-only.',
            'input_schema' => [
                'type' => 'object',
                'properties' => ['style_id' => ['type' => 'integer']],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('uploadStyleAsset', [
            'description' => 'Upload an arbitrary style asset from URL (favicon, custom image). '
                . 'Path is validated — cannot overwrite XF core files.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['file_url', 'target_filename'],
                'properties' => [
                    'file_url' => ['type' => 'string'],
                    'target_filename' => ['type' => 'string', 'description' => 'Basename only, no paths — e.g. custom-bg.jpg'],
                    'style_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('deleteStyleAsset', [
            'description' => 'Delete a style asset by filename.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['filename'],
                'properties' => [
                    'filename' => ['type' => 'string'],
                    'style_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────

    public function execute_listStyleProperties($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $styleId = (int) ($params['style_id'] ?? 0);
        $group   = trim((string) ($params['group_name'] ?? ''));

        $q = 'SELECT property_id, property_name, property_type, group_name, title,
                     property_value, addon_id
              FROM xf_style_property
              WHERE style_id = ?';
        $args = [$styleId];
        if ($group !== '') {
            $q .= ' AND group_name = ?';
            $args[] = $group;
        }
        $q .= ' ORDER BY group_name, property_name LIMIT 500';

        $rows = \XF::db()->fetchAll($q, $args);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'property_name' => $r['property_name'],
                'property_type' => $r['property_type'],
                'group_name'    => $r['group_name'],
                'title'         => $r['title'],
                'value'         => $r['property_value'],
                'addon_id'      => $r['addon_id'],
            ];
        }
        return $this->success([
            'style_id' => $styleId,
            'count'    => count($out),
            'properties' => $out,
        ]);
    }

    public function execute_setStyleProperty($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $styleId = (int) ($params['style_id'] ?? 0);
        $name    = (string) $params['property_name'];

        /** @var \XF\Entity\StyleProperty|null $prop */
        $prop = \XF::em()->findOne('XF:StyleProperty', [
            'style_id' => $styleId,
            'property_name' => $name,
        ]);

        $created = false;
        if (!$prop) {
            // Property doesn't exist on this style — but it may exist on Master
            // (style_id=0) and this is a child style creating its first override.
            // XF ACP creates a new xf_style_property row in this case, copying
            // property_type/group_name from Master. Do the same.
            $master = \XF::em()->findOne('XF:StyleProperty', [
                'style_id' => 0,
                'property_name' => $name,
            ]);
            if (!$master) {
                return $this->error(
                    'not_found',
                    "Style property '$name' not defined anywhere (not on style $styleId, not on Master style 0). "
                    . "Check property_name — see listStyleProperties for valid names."
                );
            }
            if ($styleId === 0) {
                // Master itself missing — real error, no fallback
                return $this->error('not_found', "Style property '$name' not found on Master style 0");
            }
            // Verify the target style exists
            $style = \XF::em()->find('XF:Style', $styleId);
            if (!$style) {
                return $this->error('not_found', "Style $styleId not found");
            }
            // Create override on the child style — copy ALL metadata from Master
            // (property_type=value needs value_type, css_components, etc.).
            $prop = \XF::em()->create('XF:StyleProperty');
            $prop->style_id        = $styleId;
            $prop->property_name   = $name;
            $prop->property_type   = $master->property_type;
            $prop->group_name      = $master->group_name;
            $prop->title           = $master->title;
            $prop->description     = $master->description;
            $prop->css_components  = $master->css_components;
            $prop->value_type      = $master->value_type;
            $prop->value_parameters = $master->value_parameters;
            $prop->has_variations  = $master->has_variations;
            $prop->depends_on      = $master->depends_on;
            $prop->value_group     = $master->value_group;
            $prop->display_order   = $master->display_order;
            $prop->addon_id        = $master->addon_id;
            $created = true;
        }

        // property_value column is XF type=JSON — XF entity json_encodes on save.
        // If we pre-encode here, the string gets double-encoded and LESS compiler
        // sees "{\"default\":\"#XXX\"}" instead of the intended color value.
        // Pass raw PHP value; if caller sent a JSON string, decode first so XF
        // encodes the parsed structure (not the string).
        $value = $params['value'];
        if (is_string($value) && $value !== '' && $value[0] === '{') {
            $decoded = json_decode($value, true);
            if ($decoded !== null) {
                $value = $decoded;
            }
        }
        $prop->property_value = $value;
        if (!$prop->save()) {
            return $this->error('validation_failed', implode(' ', $prop->getErrors()));
        }

        // FULL cache invalidation — API context has no cron so "Later" variants
        // never fire. XF ACP saves via PropertyService which does all of this
        // synchronously; we replicate the same flush cycle here.
        $rebuilt = ['css_cache_wiped' => false, 'style_data_rebuilt' => false];
        try {
            /** @var \XF\Repository\StyleRepository $repo */
            $repo = \XF::em()->getRepository('XF:Style');
            // 1. Bump last_modified_date on every style + wipe xf_css_cache
            //    (invalidates browser + server CSS caches immediately)
            $repo->updateAllStylesLastModifiedDate();
            $rebuilt['css_cache_wiped'] = true;
            // 2. Rebuild asset + property caches (recomputes CSS from properties)
            $repo->triggerPartialStyleDataRebuild();
            $rebuilt['style_data_rebuilt'] = true;
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'setStyleProperty rebuild: ');
        }

        return $this->success([
            'property_name' => $name,
            'style_id'      => $styleId,
            'value'         => $prop->property_value,
            'created'       => $created,
            'rebuilt'       => $rebuilt,
            'note'          => $created
                ? "Override row CREATED on style $styleId (inherited metadata from Master). CSS caches wiped."
                : 'Style property saved + CSS caches wiped. Browser hard-refresh (Ctrl+F5) may be needed.',
        ]);
    }

    public function execute_uploadSiteLogo($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $styleId = (int) ($params['style_id'] ?? 0);
        // Derive extension from URL path (agent tests confirmed 'logo' with no
        // extension → getExtension() returns '' → validation failed).
        // uploadStyleAsset worked because caller provides target_filename with ext.
        $url = (string) $params['file_url'];
        $urlPath = parse_url($url, PHP_URL_PATH) ?: '';
        $urlExt = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
        $suggestedName = $urlExt !== '' ? 'logo.' . $urlExt : 'logo';

        $upload  = $this->fetchUrlToUpload($url, $suggestedName);
        if ($upload instanceof \XF\Http\Upload) {
            return $this->applyLogoUpload($upload, $styleId);
        }
        return $upload;  // error array
    }

    public function execute_deleteSiteLogo($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;
        $styleId = (int) ($params['style_id'] ?? 0);
        return $this->clearLogoProperty($styleId);
    }

    public function execute_listStyleAssets($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $styleId = (int) ($params['style_id'] ?? 0);
        $dir = \XF::app()->config('externalDataPath') . '/assets/' . ($styleId ?: 'default');

        $out = [];
        if (is_dir($dir)) {
            foreach (glob($dir . '/*') ?: [] as $path) {
                if (is_file($path)) {
                    $out[] = [
                        'filename' => basename($path),
                        'size'     => filesize($path),
                        'modified' => filemtime($path),
                    ];
                }
            }
        }
        return $this->success(['style_id' => $styleId, 'count' => count($out), 'assets' => $out]);
    }

    public function execute_uploadStyleAsset($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $filename = basename((string) $params['target_filename']);
        if (preg_match('#^(\.|\.\.)#', $filename) || strpos($filename, '/') !== false) {
            return $this->error('validation_failed', 'target_filename must be a plain basename');
        }
        $upload = $this->fetchUrlToUpload((string) $params['file_url'], $filename);
        if (!($upload instanceof \XF\Http\Upload)) return $upload;

        $styleId = (int) ($params['style_id'] ?? 0);
        $dir = \XF::app()->config('externalDataPath') . '/assets/' . ($styleId ?: 'default');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $target = $dir . '/' . $filename;
        if (!copy($upload->getTempFile(), $target)) {
            return $this->error('write_failed', 'Could not write asset to disk');
        }
        return $this->success([
            'style_id' => $styleId,
            'filename' => $filename,
            'size'     => filesize($target),
        ]);
    }

    public function execute_deleteStyleAsset($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $filename = basename((string) $params['filename']);
        $styleId = (int) ($params['style_id'] ?? 0);
        $target = \XF::app()->config('externalDataPath')
            . '/assets/' . ($styleId ?: 'default') . '/' . $filename;

        if (!file_exists($target)) {
            return $this->error('not_found', "Asset '$filename' not found");
        }
        if (!unlink($target)) {
            return $this->error('delete_failed', 'Could not delete asset');
        }
        return $this->success(['style_id' => $styleId, 'filename' => $filename, 'deleted' => true]);
    }

    // ────────────────────────────────────────────────────────────────────
    // helpers

    private function applyLogoUpload(\XF\Http\Upload $upload, int $styleId): array
    {
        // Extension: prefer $upload->getExtension(), fall back to MIME sniff
        // from the temp file if extension is empty (URL had no path suffix).
        $ext = strtolower($upload->getExtension());
        if ($ext === '') {
            $ext = $this->extFromMime($upload->getTempFile());
        }
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'], true)) {
            return $this->error(
                'validation_failed',
                "Logo type '$ext' not allowed — supported: png/jpg/jpeg/gif/svg/webp. "
                . "If the URL has no extension, ensure the server returns a recognizable Content-Type."
            );
        }
        if ($upload->getFileSize() > 5 * 1024 * 1024) {
            return $this->error('too_large', 'Logo file exceeds 5 MB limit');
        }

        $dir = \XF::app()->config('externalDataPath') . '/assets/' . ($styleId ?: 'default');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $fname = 'logo.' . $ext;
        $target = $dir . '/' . $fname;
        if (!copy($upload->getTempFile(), $target)) {
            return $this->error('write_failed', 'Could not save logo');
        }

        // Point publicLogoUrl style property to the uploaded file
        $prop = \XF::em()->findOne('XF:StyleProperty', [
            'style_id' => $styleId, 'property_name' => 'publicLogoUrl',
        ]);
        if ($prop) {
            $prop->property_value = 'styles/' . ($styleId ?: 'default') . '/xenforo/' . $fname;
            $prop->save();
        }

        return $this->success([
            'style_id' => $styleId,
            'filename' => $fname,
            'size'     => filesize($target),
            'note'     => 'Logo uploaded. If the site still shows the old one, rebuild style caches.',
        ]);
    }

    private function clearLogoProperty(int $styleId): array
    {
        $prop = \XF::em()->findOne('XF:StyleProperty', [
            'style_id' => $styleId, 'property_name' => 'publicLogoUrl',
        ]);
        if ($prop) {
            $prop->property_value = '';
            $prop->save();
        }
        return $this->success(['style_id' => $styleId, 'cleared' => true]);
    }

    private function fetchUrlToUpload(string $url, string $suggestedName)
    {
        // Guard scheme
        if (!preg_match('#^https?://#i', $url)) {
            return $this->error('validation_failed', 'file_url must be http(s)');
        }
        $tmp = tempnam(sys_get_temp_dir(), 'aicadm_');
        $data = @file_get_contents($url);
        if ($data === false) {
            return $this->error('fetch_failed', "Could not fetch $url");
        }
        if (strlen($data) > 5 * 1024 * 1024) {
            return $this->error('too_large', 'File exceeds 5 MB');
        }
        file_put_contents($tmp, $data);

        // If the suggested name has no extension, sniff MIME from the actual
        // file content and append the matching extension. Otherwise
        // Upload::getExtension() returns '' and downstream validation fails.
        if (pathinfo($suggestedName, PATHINFO_EXTENSION) === '') {
            $ext = $this->extFromMime($tmp);
            if ($ext !== '') $suggestedName .= '.' . $ext;
        }
        return new \XF\Http\Upload($tmp, $suggestedName);
    }

    /**
     * Detect a file's image extension from its actual content (magic bytes /
     * MIME sniff). Returns empty string if not a recognizable image.
     */
    private function extFromMime(string $path): string
    {
        if (!is_file($path)) return '';

        // getimagesize covers png/jpg/gif/webp reliably
        $info = @getimagesize($path);
        if (is_array($info) && !empty($info['mime'])) {
            $map = [
                'image/png'  => 'png',
                'image/jpeg' => 'jpg',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
            ];
            if (isset($map[$info['mime']])) return $map[$info['mime']];
        }

        // SVG is XML — getimagesize might not identify it. Sniff the start.
        $head = (string) @file_get_contents($path, false, null, 0, 512);
        if ($head !== '' && (stripos($head, '<svg') !== false || stripos($head, '<?xml') === 0)) {
            if (stripos($head, '<svg') !== false) return 'svg';
        }

        // finfo as last resort
        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            if ($f) {
                $mime = finfo_file($f, $path) ?: '';
                finfo_close($f);
                $map = ['image/png'=>'png','image/jpeg'=>'jpg','image/gif'=>'gif','image/webp'=>'webp','image/svg+xml'=>'svg'];
                if (isset($map[$mime])) return $map[$mime];
            }
        }
        return '';
    }
}
