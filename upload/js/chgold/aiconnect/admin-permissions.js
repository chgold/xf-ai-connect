/**
 * AI Connect — Admin CP Permission Greying
 *
 * Watches the aiconnect permission group in the User Group Permissions editor.
 * When a "master" permission is set to "Never" (deny), all dependent permissions
 * are visually disabled and their inputs are blocked from interaction.
 *
 * Hierarchy:
 *   useTools (master)
 *     └── use_package_* (package switch, depends on useTools)
 *           └── tool_* within that package
 *     └── tool_* (free-tier tools, depend directly on useTools)
 */
(function () {
    'use strict';

    /**
     * Get the currently-selected value of an aiconnect permission.
     * Looks for a checked radio: permissions[aiconnect][permId]
     *
     * @param {HTMLFormElement} form
     * @param {string} permId
     * @returns {string} 'allow' | 'unset' | 'deny'
     */
    function getPermVal(form, permId) {
        var sel = 'input[name="permissions[aiconnect][' + permId + ']"]:checked';
        var el = form.querySelector(sel);
        return el ? el.value : 'unset';
    }

    /**
     * Enable or disable all permission rows whose permission ID matches `pattern`.
     *
     * @param {HTMLFormElement} form
     * @param {RegExp}          pattern  Regex tested against the permission ID part
     * @param {boolean}         disabled True = grey out + block; false = restore
     */
    function setRowsDisabled(form, pattern, disabled) {
        var allRadios = form.querySelectorAll('input[type="radio"][name]');
        var seen = {};

        allRadios.forEach(function (radio) {
            var m = radio.name.match(/^permissions\[aiconnect\]\[(.+)\]$/);
            if (!m || seen[m[1]]) { return; }
            seen[m[1]] = true;

            if (!pattern.test(m[1])) { return; }

            var row = radio.closest('.formRow');
            if (!row) { return; }

            if (disabled) {
                row.style.opacity        = '0.35';
                row.style.pointerEvents  = 'none';
                row.setAttribute('data-aiconnect-disabled', '1');
            } else {
                /* Only restore if no OTHER parent is still disabled */
                if (row.getAttribute('data-aiconnect-disabled') === '1') {
                    row.style.opacity       = '';
                    row.style.pointerEvents = '';
                    row.removeAttribute('data-aiconnect-disabled');
                }
            }
        });
    }

    /**
     * Collect all package IDs from the form (permissions of the form use_package_*).
     *
     * @param {HTMLFormElement} form
     * @returns {string[]}
     */
    function getPackageIds(form) {
        var ids = [];
        var seen = {};
        form.querySelectorAll('input[name]').forEach(function (el) {
            var m = el.name.match(/^permissions\[aiconnect\]\[use_package_(.+)\]$/);
            if (m && !seen[m[1]]) {
                seen[m[1]] = true;
                ids.push(m[1]);
            }
        });
        return ids;
    }

    /**
     * Re-evaluate and apply all greying rules for the aiconnect permission group.
     *
     * @param {HTMLFormElement} form
     */
    function updateAll(form) {
        var masterDenied = (getPermVal(form, 'useTools') === 'deny');

        /* ── Master switch ────────────────────────────────────────────── */
        /* Affects: all tool_* and all use_package_* */
        setRowsDisabled(form, /^(tool_|use_package_)/, masterDenied);

        /* ── Package switches (only evaluated when master is NOT denied) ─ */
        if (!masterDenied) {
            getPackageIds(form).forEach(function (pkgId) {
                var pkgDenied = (getPermVal(form, 'use_package_' + pkgId) === 'deny');
                /* Convention: package "premium" controls tool_premium_* permissions */
                setRowsDisabled(form, new RegExp('^tool_' + escapeRegExp(pkgId) + '_'), pkgDenied);
            });
        }
    }

    function escapeRegExp(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Attach listeners to a permissions form that contains aiconnect permissions.
     *
     * @param {HTMLFormElement} form
     */
    function attachToForm(form) {
        if (form._aiconnectInit) { return; }
        form._aiconnectInit = true;

        form.addEventListener('change', function (e) {
            if (e.target && e.target.name &&
                e.target.name.indexOf('permissions[aiconnect]') === 0) {
                updateAll(form);
            }
        });

        /* Apply initial state */
        updateAll(form);
    }

    /**
     * Scan the page for aiconnect-bearing permission forms and attach listeners.
     */
    function scanForms() {
        document.querySelectorAll('form').forEach(function (form) {
            if (form.querySelector('input[name^="permissions[aiconnect]"]')) {
                attachToForm(form);
            }
        });
    }

    /* ── Bootstrapping ──────────────────────────────────────────────────── */

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scanForms);
    } else {
        scanForms();
    }

    /* XenForo fires 'xf:reinit' after AJAX navigation; re-scan then */
    document.addEventListener('xf:reinit', scanForms);

}());
