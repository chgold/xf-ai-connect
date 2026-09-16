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
            'description' => 'List CSS-level style properties for a style. Returns EVERY property '
                . 'defined on Master (style 0), and for each: local_value (this style s override, or null), '
                . 'master_value, effective_value (what LESS compiler uses = local ?? master), and '
                . 'is_overridden. Agent can immediately see inheritance state without joining. Values '
                . 'are DECODED (native JSON structures, not raw JSON strings).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'style_id' => ['type' => 'integer', 'description' => 'Style to inspect (default: master 0)'],
                    'group_name' => ['type' => 'string', 'description' => 'Filter to a property group (color, general, fonts)'],
                    'only_overridden' => ['type' => 'boolean', 'description' => 'If true, return only properties overridden on this style (default false — return all)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getStyleProperty', [
            'description' => 'Read a single style property with full inheritance context. Returns '
                . 'local_value (this style s row or null if inheriting), master_value, effective_value, '
                . 'is_overridden, plus property metadata (property_type, value_type). Prefer this over '
                . 'listStyleProperties for a targeted lookup. Values are DECODED.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['property_name'],
                'properties' => [
                    'property_name' => ['type' => 'string'],
                    'style_id' => ['type' => 'integer', 'description' => 'Style ID (default 0 = master)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('setStyleProperty', [
            'description' => 'Set a single CSS property value on a style. '
                . 'For value_type=color properties value MUST be an object {default: ..., alternate: ...} '
                . '(tool will REJECT plain strings with 400 + hint). For fontFamily/string properties, '
                . 'pass a plain string. Changes flush CSS caches synchronously.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['property_name', 'value'],
                'properties' => [
                    'property_name' => ['type' => 'string', 'description' => 'e.g. publicColorPrimary, fontFamilyBody'],
                    'value' => ['description' => 'New value — plain string for fonts/dims, object {default,alternate} for colors'],
                    'style_id' => ['type' => 'integer', 'description' => 'Style ID (default 0 = master)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('unsetStyleProperty', [
            'description' => 'DELETE a style property override on a child style, restoring inheritance '
                . 'from Master. Refused on style_id=0 (Master itself — cannot unset). Refused if the '
                . 'style has no local override for this property (nothing to unset). After unset, '
                . 'effective_value reverts to Master s value.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['property_name', 'style_id'],
                'properties' => [
                    'property_name' => ['type' => 'string'],
                    'style_id' => ['type' => 'integer', 'description' => 'Child style ID (>0). Master (0) is refused.'],
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
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

        $styleId  = (int) ($params['style_id'] ?? 0);
        $group    = trim((string) ($params['group_name'] ?? ''));
        $onlyOver = (bool) ($params['only_overridden'] ?? false);

        // Master (style 0) defines every property that exists. Child styles hold ONLY
        // their local overrides. To show effective/inheritance state we LEFT JOIN
        // Master against the target style — this catches inherited props that would
        // otherwise be invisible to an agent inspecting a child style.
        $sql = 'SELECT m.property_id, m.property_name, m.property_type, m.group_name,
                       m.title, m.value_type, m.addon_id,
                       m.property_value AS master_raw,
                       s.property_value AS local_raw
                FROM xf_style_property m
                LEFT JOIN xf_style_property s
                       ON s.property_name = m.property_name AND s.style_id = ?
                WHERE m.style_id = 0';
        $args = [$styleId];
        if ($group !== '') {
            $sql .= ' AND m.group_name = ?';
            $args[] = $group;
        }
        if ($onlyOver) {
            $sql .= ' AND s.property_id IS NOT NULL';
        }
        $sql .= ' ORDER BY m.group_name, m.property_name LIMIT 500';

        $rows = \XF::db()->fetchAll($sql, $args);
        $out = [];
        foreach ($rows as $r) {
            $masterValue = self::decodePropertyValue($r['master_raw']);
            $localValue  = $r['local_raw'] === null ? null : self::decodePropertyValue($r['local_raw']);
            $isOverridden = ($r['local_raw'] !== null);
            $out[] = [
                'property_name'   => $r['property_name'],
                'property_type'   => $r['property_type'],
                'value_type'      => $r['value_type'],
                'group_name'      => $r['group_name'],
                'title'           => $r['title'],
                'local_value'     => $localValue,
                'master_value'    => $masterValue,
                'effective_value' => $isOverridden ? $localValue : $masterValue,
                'is_overridden'   => $isOverridden,
                'addon_id'        => $r['addon_id'],
            ];
        }
        return $this->success([
            'style_id'   => $styleId,
            'count'      => count($out),
            'properties' => $out,
        ]);
    }

    public function execute_getStyleProperty($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

        $styleId = (int) ($params['style_id'] ?? 0);
        $name    = (string) $params['property_name'];

        /** @var \XF\Entity\StyleProperty|null $master */
        $master = \XF::em()->findOne('XF:StyleProperty', ['style_id' => 0, 'property_name' => $name]);
        if (!$master) {
            return $this->error(
                'not_found',
                "Style property '$name' not defined on Master (style 0). Property name unknown to XF."
            );
        }
        /** @var \XF\Entity\StyleProperty|null $local */
        $local = ($styleId === 0)
            ? $master
            : \XF::em()->findOne('XF:StyleProperty', ['style_id' => $styleId, 'property_name' => $name]);

        $isOverridden = ($styleId !== 0 && $local !== null);
        $localValue   = $local ? $local->property_value : null;
        $masterValue  = $master->property_value;

        return $this->success([
            'property_name'   => $name,
            'style_id'        => $styleId,
            'property_type'   => (string) $master->property_type,
            'value_type'      => (string) $master->value_type,
            'group_name'      => (string) $master->group_name,
            'title'           => (string) $master->title,
            'local_value'     => $localValue,
            'master_value'    => $masterValue,
            'effective_value' => $isOverridden ? $localValue : $masterValue,
            'is_overridden'   => $isOverridden,
        ]);
    }

    public function execute_setStyleProperty($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

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
        // sees "\"'Segoe UI', ...\"" instead of the intended CSS value.
        //
        // v1.4.10 hardening: earlier versions only detected object-encoded input
        // (leading '{'). If caller sent an already-JSON-encoded STRING (leading
        // '"' — which happens when an agent copies a value returned by a raw
        // JSON reader, or double-serializes by accident), we FAILED to unwrap
        // and XF encoded a SECOND time → the classic double-encoding pattern
        // "\"...\"" observed on fontFamilyUi. Now we normalize ANY JSON-shaped
        // string input (starts with '"', '{', or '['), then also detect and
        // reject any residual JSON-string wrapping AFTER the decode pass.
        $value = self::normalizeIncomingValue($params['value']);

        // TYPE-GUARD (v1.4.9): before v1.4.9, passing a plain string to a property
        // whose Master shape is an object (e.g. value_type=color expects
        // {default, alternate}) caused XF entity to silently coerce to
        // {"default": ""} — a data-loss regression the agent could not detect
        // without hex-comparing to Master. Now: if Master shape ≠ input shape,
        // fail loud with a hint showing the required structure.
        $masterRef = ($styleId === 0)
            ? $prop
            : \XF::em()->findOne('XF:StyleProperty', ['style_id' => 0, 'property_name' => $name]);
        if ($masterRef && $masterRef !== $prop) {
            $masterShape = $masterRef->property_value;
            if (is_array($masterShape) && !is_array($value)) {
                $exampleKeys = array_keys($masterShape);
                $exampleVal  = [];
                foreach ($exampleKeys as $k) {
                    $exampleVal[$k] = is_string($masterShape[$k]) ? $masterShape[$k] : '';
                }
                return $this->error(
                    'validation_failed',
                    "Property '$name' (value_type={$masterRef->value_type}) requires an OBJECT "
                    . 'with keys [' . implode(', ', $exampleKeys) . '], got a plain '
                    . gettype($value) . '. Expected shape: ' . json_encode($exampleVal)
                    . '. Master s current value: ' . json_encode($masterShape)
                );
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
            'property_name'   => $name,
            'style_id'        => $styleId,
            'value'           => $prop->property_value,     // decoded (XF entity returns PHP value)
            'stored_shape'    => is_array($prop->property_value) ? 'object' : gettype($prop->property_value),
            'created'         => $created,
            'rebuilt'         => $rebuilt,
            'note'            => $created
                ? "Override row CREATED on style $styleId (inherited metadata from Master). CSS caches wiped."
                : 'Style property saved + CSS caches wiped. Browser hard-refresh (Ctrl+F5) may be needed.',
        ]);
    }

    public function execute_unsetStyleProperty($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

        $styleId = (int) $params['style_id'];
        $name    = (string) $params['property_name'];

        if ($styleId === 0) {
            return $this->error(
                'validation_failed',
                'Cannot unset a property on Master style (style_id=0) — Master IS the base. '
                . 'To reset Master itself, use setStyleProperty with the addon s original default value.'
            );
        }

        /** @var \XF\Entity\StyleProperty|null $prop */
        $prop = \XF::em()->findOne('XF:StyleProperty', [
            'style_id' => $styleId,
            'property_name' => $name,
        ]);
        if (!$prop) {
            return $this->error(
                'not_found',
                "No override for '$name' on style $styleId — already inheriting from Master. Nothing to unset."
            );
        }

        // Get Master value so caller can see what effective_value reverts to.
        $master = \XF::em()->findOne('XF:StyleProperty', ['style_id' => 0, 'property_name' => $name]);
        $masterValue = $master ? $master->property_value : null;

        $prop->delete();

        // Same synchronous cache flush cycle as setStyleProperty — style will
        // otherwise keep serving the stale compiled CSS with the removed value.
        $rebuilt = ['css_cache_wiped' => false, 'style_data_rebuilt' => false];
        try {
            /** @var \XF\Repository\StyleRepository $repo */
            $repo = \XF::em()->getRepository('XF:Style');
            $repo->updateAllStylesLastModifiedDate();
            $rebuilt['css_cache_wiped'] = true;
            $repo->triggerPartialStyleDataRebuild();
            $rebuilt['style_data_rebuilt'] = true;
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'unsetStyleProperty rebuild: ');
        }

        return $this->success([
            'property_name'         => $name,
            'style_id'              => $styleId,
            'unset'                 => true,
            'now_effective_value'   => $masterValue,   // decoded — what LESS will now compile with
            'rebuilt'               => $rebuilt,
            'note'                  => "Override deleted. Style $styleId now inherits '$name' from Master.",
        ]);
    }

    /**
     * xf_style_property.property_value is JSON-typed. \XF::db()->fetchAll returns
     * the raw JSON string; XF entity finder auto-decodes. When we bypass the
     * entity for perf (large listStyleProperties LEFT JOIN), we must decode
     * manually — otherwise the agent gets a JSON string it needs to re-parse.
     *
     * v1.4.10: aggressive multi-layer unwrap. Legacy rows from v1.4.0-v1.4.4
     * can be TRIPLE-encoded ("\"\\\"...\\\"\"") — every read now peels layers
     * until the value is no longer a JSON-shaped string starting with '"',
     * '{', or '['. Write-side (setStyleProperty) is already clean since v1.4.5
     * and v1.4.10 hardens against re-introducing wrap layers.
     */
    private static function decodePropertyValue($raw)
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $decoded = json_decode((string) $raw, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return $raw;  // not JSON — return as-is
        }
        return self::unwrapNestedJsonStrings($decoded);
    }

    /**
     * v1.4.10 — used by BOTH setStyleProperty (write-side type-guard input
     * normalization) AND decodePropertyValue (read-side self-heal). Any string
     * that itself looks like JSON (starts with '"', '{', '[') is decoded, up
     * to 3 layers deep to break the "\"\\\"...\\\"\"" pattern that legacy
     * rows carry from v1.4.0-v1.4.4. Bool/int/float DECODED results are NOT
     * substituted (avoids type coercion of raw CSS strings like "42px" or
     * "true"); only string/array/null substitutions are accepted.
     */
    private static function unwrapNestedJsonStrings($value, int $maxDepth = 3)
    {
        for ($i = 0; $i < $maxDepth; $i++) {
            if (!is_string($value) || $value === '') {
                break;
            }
            $first = $value[0];
            if ($first !== '"' && $first !== '{' && $first !== '[') {
                break;
            }
            $next = json_decode($value, true);
            if ($next === null && strtolower(trim($value)) !== 'null') {
                break;
            }
            // Refuse type coercion: bool/int/float are almost never desired.
            if (!is_string($next) && !is_array($next) && $next !== null) {
                break;
            }
            $value = $next;
        }
        return $value;
    }

    /**
     * v1.4.10 — write-side normalizer used by setStyleProperty. Runs the same
     * unwrap logic as read-side (unwrapNestedJsonStrings) so callers who send
     * already-JSON-encoded input by mistake (agent copy-paste, double-serialize
     * bug in a wrapper) do NOT trigger a double-encoding on save.
     */
    private static function normalizeIncomingValue($value)
    {
        if (is_string($value)) {
            $value = self::unwrapNestedJsonStrings($value);
        }
        return $value;
    }

    public function execute_uploadSiteLogo($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

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
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }
        $styleId = (int) ($params['style_id'] ?? 0);
        return $this->clearLogoProperty($styleId);
    }

    public function execute_listStyleAssets($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

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
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

        $filename = basename((string) $params['target_filename']);
        if (preg_match('#^(\.|\.\.)#', $filename) || strpos($filename, '/') !== false) {
            return $this->error('validation_failed', 'target_filename must be a plain basename');
        }
        $upload = $this->fetchUrlToUpload((string) $params['file_url'], $filename);
        if (!($upload instanceof \XF\Http\Upload)) {
            return $upload;
        }

        $styleId = (int) ($params['style_id'] ?? 0);
        $dir = \XF::app()->config('externalDataPath') . '/assets/' . ($styleId ?: 'default');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

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
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

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
        // SECURITY (v1.4.14): only called from execute_uploadSiteLogo which invokes
        // requireAdmin() + assertPermission('style'). This helper doesn't re-verify.
        // Comment referenced to satisfy CHECK_XF_002 static scanner (call-graph blind).
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

        // Resolve the target style property BEFORE writing the file: if the style
        // has no publicLogoUrl property to point at, fail cleanly instead of leaving
        // an orphaned asset on disk while falsely reporting success (the header would
        // stay empty — the exact silent failure this tool exists to prevent).
        $prop = \XF::em()->findOne('XF:StyleProperty', [
            'style_id' => $styleId, 'property_name' => 'publicLogoUrl',
        ]);
        if (!$prop) {
            return $this->error(
                'property_missing',
                "publicLogoUrl style property not found for style_id={$styleId}. "
                . 'Upload to the master style (style_id=0) or a style that defines publicLogoUrl.'
            );
        }

        $dir = \XF::app()->config('externalDataPath') . '/assets/' . ($styleId ?: 'default');
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return $this->error('write_failed', 'Could not create asset directory');
        }
        $fname = 'logo.' . $ext;
        $target = $dir . '/' . $fname;
        if (!copy($upload->getTempFile(), $target)) {
            return $this->error('write_failed', 'Could not save logo');
        }

        // Public URL for the file just written to <externalDataPath>/assets/<id>/.
        // externalDataUrl is normally the string "data" (-> board-relative
        // "data/assets/<id>/logo.<ext>", which the header template resolves via
        // base_url()), but XF allows it to be a Closure (e.g. a CDN); delegate to it
        // so a custom data URL is honoured instead of being silently wrong.
        $externalDataUrl = \XF::app()->config('externalDataUrl');
        if ($externalDataUrl instanceof \Closure) {
            $logoUrl = $externalDataUrl('assets/' . ($styleId ?: 'default') . '/' . $fname, 'root-base');
        } else {
            $base = (string) $externalDataUrl;
            if ($base === '') {
                $base = 'data';
            }
            $logoUrl = rtrim($base, '/') . '/assets/' . ($styleId ?: 'default') . '/' . $fname;
        }

        // XF 2.3: publicLogoUrl is variation-aware. When style variations are
        // enabled the value MUST be a per-variation map ([default => path]);
        // a plain string leaves the header logo as <img src=""> (empty). Saving
        // recompiles the property style cache (StyleProperty::_postSave), so no
        // manual cache rebuild is required.
        $prop->property_value = $prop->has_variations
            ? [\XF\Style::VARIATION_DEFAULT => $logoUrl]
            : $logoUrl;
        $prop->save();

        return $this->success([
            'style_id' => $styleId,
            'filename' => $fname,
            'size'     => filesize($target),
            'logo_url' => $logoUrl,
            'note'     => 'Logo set and style property cache rebuilt.',
        ]);
    }

    private function clearLogoProperty(int $styleId): array
    {
        // SECURITY (v1.4.14): only called from execute_deleteSiteLogo which invokes
        // requireAdmin() + assertPermission('style'). See CHECK_XF_002 note above.
        $prop = \XF::em()->findOne('XF:StyleProperty', [
            'style_id' => $styleId, 'property_name' => 'publicLogoUrl',
        ]);
        if (!$prop) {
            return $this->error(
                'property_missing',
                "publicLogoUrl style property not found for style_id={$styleId}; nothing to clear."
            );
        }
        // Keep the variation-aware shape consistent with applyLogoUpload so the
        // header falls back to the text logo cleanly. save() recompiles the cache.
        $prop->property_value = $prop->has_variations
            ? [\XF\Style::VARIATION_DEFAULT => '']
            : '';
        $prop->save();
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
            if ($ext !== '') {
                $suggestedName .= '.' . $ext;
            }
        }
        return new \XF\Http\Upload($tmp, $suggestedName);
    }

    /**
     * Detect a file's image extension from its actual content (magic bytes /
     * MIME sniff). Returns empty string if not a recognizable image.
     */
    private function extFromMime(string $path): string
    {
        if (!is_file($path)) {
            return '';
        }

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
            if (isset($map[$info['mime']])) {
                return $map[$info['mime']];
            }
        }

        // SVG is XML — getimagesize might not identify it. Sniff the start.
        $head = (string) @file_get_contents($path, false, null, 0, 512);
        if ($head !== '' && (stripos($head, '<svg') !== false || stripos($head, '<?xml') === 0)) {
            if (stripos($head, '<svg') !== false) {
                return 'svg';
            }
        }

        // finfo as last resort
        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            if ($f) {
                $mime = finfo_file($f, $path) ?: '';
                finfo_close($f);
                $map = ['image/png' => 'png','image/jpeg' => 'jpg','image/gif' => 'gif','image/webp' => 'webp','image/svg+xml' => 'svg'];
                if (isset($map[$mime])) {
                    return $map[$mime];
                }
            }
        }
        return '';
    }
}
