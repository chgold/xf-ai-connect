(function () {
    'use strict';

    var HARD_OPACITY = '0.35';
    var SOFT_OPACITY = '0.60';

    var HARD_TITLE = 'Blocked by "Use AI Connect tools = Never". Cannot be overridden.';
    var SOFT_TITLE = 'No effect while "Use AI Connect tools" is Not Set. ' +
                     'These settings apply only when the master switch is set to Allow.';

    function getPermVal(form, permId) {
        var el = form.querySelector('input[name="permissions[aiconnect][' + permId + ']"]:checked');
        return el ? el.value : 'unset';
    }

    /**
     * Set visual state on all rows matching pattern.
     * mode: false = normal | 'soft' = dimmed (Not Set) | 'hard' = disabled (Never)
     * skipUpgrade: when true, only apply if mode is strictly stronger than current state.
     */
    function setRowsMode(form, pattern, mode, skipUpgrade) {
        var priority = { 'false': 0, 'soft': 1, 'hard': 2 };
        var seen = {};

        form.querySelectorAll('input[type="radio"][name]').forEach(function (radio) {
            var m = radio.name.match(/^permissions\[aiconnect\]\[(.+)\]$/);
            if (!m || seen[m[1]]) { return; }
            seen[m[1]] = true;
            if (!pattern.test(m[1])) { return; }

            var row = radio.closest('.formRow');
            if (!row) { return; }

            var current = row.getAttribute('data-aiconnect-state') || 'false';
            var modeKey = mode ? mode : 'false';

            // When skipUpgrade is true, only apply if new mode is strictly stronger
            if (skipUpgrade && priority[modeKey] <= priority[current]) { return; }

            if (mode === 'hard') {
                row.style.opacity       = HARD_OPACITY;
                row.style.pointerEvents = 'none';
                row.style.cursor        = '';
                row.setAttribute('data-aiconnect-state', 'hard');
                row.title = HARD_TITLE;
            } else if (mode === 'soft') {
                row.style.opacity       = SOFT_OPACITY;
                row.style.pointerEvents = '';
                row.style.cursor        = 'help';
                row.setAttribute('data-aiconnect-state', 'soft');
                row.title = SOFT_TITLE;
            } else {
                row.style.opacity       = '';
                row.style.pointerEvents = '';
                row.style.cursor        = '';
                row.removeAttribute('data-aiconnect-state');
                row.title = '';
            }
        });
    }

    function getPackageIds(form) {
        var ids = [], seen = {};
        form.querySelectorAll('input[name]').forEach(function (el) {
            var m = el.name.match(/^permissions\[aiconnect\]\[use_package_(.+)\]$/);
            if (m && !seen[m[1]]) { seen[m[1]] = true; ids.push(m[1]); }
        });
        return ids;
    }

    function escapeRegExp(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function updateAll(form) {
        var masterVal  = getPermVal(form, 'useTools');
        var masterMode = masterVal === 'deny'  ? 'hard' :
                         masterVal === 'unset' ? 'soft' : false;

        // Step 1: Apply master state to all tool and package rows
        setRowsMode(form, /^(tool_|use_package_)/, masterMode, false);

        // Step 2: Apply per-package overrides — only if they are strictly stronger
        // (never downgrade a row the master already set)
        if (masterMode !== 'hard') {
            getPackageIds(form).forEach(function (pkgId) {
                var pkgVal  = getPermVal(form, 'use_package_' + pkgId);
                var pkgMode = pkgVal === 'deny'  ? 'hard' :
                              pkgVal === 'unset' ? 'soft' : false;

                if (pkgMode) {
                    // skipUpgrade=true: only upgrade soft→hard or none→soft/hard
                    setRowsMode(
                        form,
                        new RegExp('^tool_' + escapeRegExp(pkgId) + '_'),
                        pkgMode,
                        true
                    );
                }
            });
        }
    }

    function attachToForm(form) {
        if (form._aiconnectInit) { return; }
        form._aiconnectInit = true;

        form.querySelectorAll('input[type="radio"][name^="permissions[aiconnect]"]').forEach(function (radio) {
            radio.addEventListener('click', function () {
                setTimeout(function () { updateAll(form); }, 0);
            });
        });

        form.addEventListener('change', function (e) {
            if (e.target && e.target.name &&
                    e.target.name.indexOf('permissions[aiconnect]') === 0) {
                updateAll(form);
            }
        });

        updateAll(form);
    }

    function scanForms() {
        document.querySelectorAll('form').forEach(function (form) {
            if (form.querySelector('input[name^="permissions[aiconnect]"]')) {
                attachToForm(form);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scanForms);
    } else {
        scanForms();
    }

    document.addEventListener('xf:reinit', scanForms);
}());
