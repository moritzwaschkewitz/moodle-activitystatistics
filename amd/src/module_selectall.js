/**
 * Module to select/deselect all module checkboxes in the filter form.
 *
 * @module tool_activitystatistics/module_selectall
 */
define([], function() {

    /**
     * Set all module checkboxes inside a container to checked/unchecked.
     *
     * @param {HTMLElement} container The container element.
     * @param {boolean} checked Whether checkboxes should be checked.
     */
    const setAll = (container, checked) => {
        const boxes = container.querySelectorAll('input[type="checkbox"][name^="modules["]');
        boxes.forEach(box => {
            box.checked = checked;
            box.dispatchEvent(new Event('change', {bubbles: true}));
        });
    };

    return {
        /**
         * @param {string} containerid
         */
        init: function(containerid) {
            const container = document.getElementById(containerid);
            if (!container) {
                return;
            }

            container.addEventListener('click', function(e) {
                const btn = (e.target instanceof Element) ? e.target.closest('button[data-action]') : null;
                if (!btn) {
                    return;
                }

                const action = btn.dataset.action;
                if (action === 'selectall') {
                    e.preventDefault();
                    setAll(container, true);
                } else if (action === 'selectnone') {
                    e.preventDefault();
                    setAll(container, false);
                }
            });
        }
    };
});