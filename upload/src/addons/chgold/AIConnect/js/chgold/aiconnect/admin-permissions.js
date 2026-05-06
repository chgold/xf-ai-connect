(function () {
    'use strict';

    function getPermVal(form, permId) {
        var el = form.querySelector('input[name="permissions[aiconnect][' + permId + ']"]:checked');
        return el ? el.value : 'unset';
    }

    function setRowsDisabled(form, pattern, disabled) {
        var seen = {};
        form.querySelectorAll('input[type="radio"][name]').forEach(function (radio) {
            var m = radio.name.match(/^permissions\[aiconnect\]\[(.+)\]$/);
            if (!m || seen[m[1]]) { return; }
            seen[m[1]] = true;
            if (!pattern.test(m[1])) { return; }

            var row = radio.closest('.formRow');
            if (!row) { return; }

            if (disabled) {
                row.style.opacity       = '0.35';
                row.style.pointerEvents = 'none';
                row.setAttribute('data-aiconnect-disabled', '1');
            } else if (row.getAttribute('data-aiconnect-disabled') === '1') {
                row.style.opacity       = '';
                row.style.pointerEvents = '';
                row.removeAttribute('data-aiconnect-disabled');
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
        var masterDenied = (getPermVal(form, 'useTools') === 'deny');
        setRowsDisabled(form, /^(tool_|use_package_)/, masterDenied);

        if (!masterDenied) {
            getPackageIds(form).forEach(function (pkgId) {
                var pkgDenied = (getPermVal(form, 'use_package_' + pkgId) === 'deny');
                setRowsDisabled(form, new RegExp('^tool_' + escapeRegExp(pkgId) + '_'), pkgDenied);
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
