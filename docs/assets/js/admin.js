(function ($) {
    'use strict';

    function getFirstOrderUnixMs() {
        var v = $('#wsom-from-date-unix').val();
        if (!v) return null;
        var n = parseInt(v, 10);
        if (isNaN(n)) return null;
        return n * 1000;
    }

    function initMonthPicker() {
        if (typeof $.fn.persianDatepicker !== 'function') return;

        var minMs = getFirstOrderUnixMs();

        $('#wsom-from-date-month, #wsom-from-date-month2').persianDatepicker({
            viewMode: 'month',
            format: 'YYYY/MM',
            autoClose: true,
            initialValue: false,
            observer: true,   // important: keep input value synced
            calendar: { persian: { locale: 'fa' } },
            dayPicker: { enabled: false },
            toolbox: { enabled: true, calendarSwitch: { enabled: false } },
            minDate: minMs || undefined,
            maxDate: new persianDate().add('month', 0).valueOf(),
        });
    }

    function initDayPicker() {
        if (typeof $.fn.persianDatepicker !== 'function') return;

        var minMs = getFirstOrderUnixMs();

        $('#wsom-from-date-day').persianDatepicker({
            viewMode: 'day',
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false,
            observer: true,
            calendar: { persian: { locale: 'fa' } },
            toolbox: { enabled: true, calendarSwitch: { enabled: false } },
            minDate: minMs || undefined,
            maxDate: new persianDate().add('month', 0).valueOf(),
        });
    }

    $(document).ready(function () {
        initMonthPicker();
        initDayPicker();
    });

})(jQuery);
