/**
 * Rupiah Formatter — SanthiGraha
 * Auto-applies to any <input data-rupiah> element.
 * Format: 6.000.000,00 (dots as thousand separator, comma as decimal)
 * On submit: converts back to raw numeric string (6000000.00) for server validation.
 */
(function () {
    'use strict';

    /**
     * Format a raw value string into Rupiah display format.
     * @param {string} value  - raw or partially-formatted string
     * @param {boolean} isBlur - if true, pads decimal to 2 digits
     * @returns {string}
     */
    function formatRupiah(value, isBlur) {
        if (!value) return '';

        // Strip all formatting characters except digits and comma (decimal separator)
        let clean = value.toString().replace(/[^,\d]/g, '');

        // Guard: leading comma
        if (clean.startsWith(',')) clean = '0' + clean;

        // Split integer part and decimal part
        let parts = clean.split(',');
        let intPart = parts[0];
        let decPart = parts.length > 1 ? parts.slice(1).join('').replace(/,/g, '').slice(0, 2) : '';

        // On blur: pad/fill decimal to always show 2 digits
        if (isBlur) {
            if (decPart === '')       decPart = '00';
            else if (decPart.length === 1) decPart += '0';
        }

        // Add thousand-separator dots
        let formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        return formatted + (decPart !== '' || isBlur ? ',' + decPart : '');
    }

    /**
     * Convert display format back to raw numeric string for server.
     * E.g. "6.000.000,50" → "6000000.50"
     * @param {string} value
     * @returns {string}
     */
    function unformatRupiah(value) {
        return value.replace(/\./g, '').replace(',', '.');
    }

    /**
     * Convert a raw DB value (e.g. "6000000.50") to display format on page load.
     * Only converts strings that look like a plain DB float (digits.digits).
     * @param {string} value
     * @returns {string}
     */
    function prepareInitialValue(value) {
        if (!value) return '';
        // Plain DB float: e.g. "6000000.50" — convert dot to comma for initial format
        if (/^\d+\.\d+$/.test(value)) {
            value = value.replace('.', ',');
        }
        // Plain integer: e.g. "6000000"
        return formatRupiah(value, true);
    }

    /**
     * Attach all listeners to a single Rupiah input element.
     * @param {HTMLInputElement} input
     */
    function attachRupiah(input) {
        const form = input.closest('form');

        // Format existing value on load
        if (input.value) {
            input.value = prepareInitialValue(input.value);
        }

        // Live formatting on each keystroke
        input.addEventListener('input', function () {
            const cursorPos = this.selectionStart;
            const oldLen    = this.value.length;

            this.value = formatRupiah(this.value, false);

            const newLen = this.value.length;
            const newCur = cursorPos + (newLen - oldLen);
            this.setSelectionRange(newCur, newCur);
        });

        // Finalise format (pad decimals) when focus leaves
        input.addEventListener('blur', function () {
            this.value = formatRupiah(this.value, true);
        });

        // Convert back to raw number before POST
        if (form) {
            form.addEventListener('submit', function () {
                input.value = unformatRupiah(input.value);
            }, { once: false });
        }
    }

    // Initialise all [data-rupiah] inputs present in the DOM
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[data-rupiah]').forEach(attachRupiah);
    });

    // Expose helpers globally so individual pages can call them if needed
    window.RupiahFormatter = { formatRupiah, unformatRupiah, prepareInitialValue, attachRupiah };
})();
