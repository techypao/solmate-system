<script>
(function () {
    'use strict';

    if (window.createPreferredDatePicker) {
        return;
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function normalizeDate(value) {
        return value ? String(value).slice(0, 10) : '';
    }

    function formatDateForApi(date) {
        return [
            date.getFullYear(),
            pad(date.getMonth() + 1),
            pad(date.getDate())
        ].join('-');
    }

    function formatMonthStart(dateString) {
        return dateString.slice(0, 7) + '-01';
    }

    function parseMonthKey(monthKey) {
        var parts = String(monthKey || '').split('-');
        return new Date(Number(parts[0]), Number(parts[1]) - 1, 1);
    }

    function shiftMonth(monthKey, amount) {
        var baseDate = parseMonthKey(monthKey);
        baseDate.setMonth(baseDate.getMonth() + amount);
        return formatDateForApi(new Date(baseDate.getFullYear(), baseDate.getMonth(), 1));
    }

    function formatDateForDisplay(value) {
        if (!value) return 'No date selected';
        var parsed = new Date(value + 'T00:00:00');
        if (isNaN(parsed.getTime())) return value;
        return parsed.toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function formatMonthLabel(monthKey) {
        var parsed = parseMonthKey(monthKey);
        return parsed.toLocaleDateString('en-PH', {
            month: 'long',
            year: 'numeric'
        });
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    window.createPreferredDatePicker = function createPreferredDatePicker(config) {
        var input = typeof config.inputId === 'string' ? document.getElementById(config.inputId) : config.input;
        var mount = typeof config.mountId === 'string' ? document.getElementById(config.mountId) : config.mount;

        if (!input || !mount) {
            return null;
        }

        var endpoint = config.endpoint || '/api/preferred-date-availability';
        var placeholder = config.placeholder || 'Select a preferred date';
        var reservedDateMessage = config.reservedDateMessage || 'Selected date is already reserved. Please choose another date.';
        var helperText = config.helperText || 'Booked dates are unavailable and cannot be selected.';
        var fetchErrorText = config.fetchErrorText || 'Live reserved-date updates could not be loaded right now. The backend will still verify your preferred date when you submit.';
        var todayKey = formatDateForApi(new Date());
        var selectedDate = normalizeDate(input.value);
        var unavailableDates = [];
        var availabilityMessage = '';
        var visibleMonth = formatMonthStart(selectedDate || todayKey);
        var open = false;

        mount.classList.add('sdp-field-host');
        mount.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        function isUnavailable(dateKey) {
            return unavailableDates.indexOf(dateKey) !== -1;
        }

        function isPast(dateKey) {
            return dateKey < todayKey;
        }

        function syncInput() {
            input.value = selectedDate;
        }

        function setOpen(nextValue) {
            open = !!nextValue;
            render();
        }

        function setSelectedDate(nextValue) {
            selectedDate = normalizeDate(nextValue);
            if (selectedDate) {
                visibleMonth = formatMonthStart(selectedDate);
            }
            syncInput();
            render();
        }

        function clear() {
            selectedDate = '';
            syncInput();
            render();
        }

        function setErrorState(hasError) {
            mount.classList.toggle('has-error', !!hasError);
        }

        function getDayCells(monthKey) {
            var monthDate = parseMonthKey(monthKey);
            var year = monthDate.getFullYear();
            var month = monthDate.getMonth();
            var firstDayIndex = new Date(year, month, 1).getDay();
            var daysInMonth = new Date(year, month + 1, 0).getDate();
            var cells = [];
            var dayNumber;

            for (dayNumber = 0; dayNumber < firstDayIndex; dayNumber++) {
                cells.push({ spacer: true, key: 'spacer-start-' + dayNumber });
            }

            for (dayNumber = 1; dayNumber <= daysInMonth; dayNumber++) {
                cells.push({
                    key: formatDateForApi(new Date(year, month, dayNumber)),
                    label: String(dayNumber)
                });
            }

            while (cells.length % 7 !== 0) {
                cells.push({ spacer: true, key: 'spacer-end-' + cells.length });
            }

            return cells;
        }

        function render() {
            var cells = getDayCells(visibleMonth);
            var canGoPrev = visibleMonth > formatMonthStart(todayKey);
            var selectedIsUnavailable = Boolean(selectedDate && isUnavailable(selectedDate));
            var helperMessage = availabilityMessage || helperText;
            var triggerText = selectedDate ? formatDateForDisplay(selectedDate) : placeholder;
            var gridHtml = cells.map(function (cell) {
                if (cell.spacer) {
                    return '<div class="sdp-day-spacer" aria-hidden="true"></div>';
                }

                var dateKey = cell.key;
                var booked = isUnavailable(dateKey);
                var past = isPast(dateKey);
                var disabled = booked || past;
                var classes = ['sdp-day'];
                var ariaLabel = formatDateForDisplay(dateKey);

                if (dateKey === todayKey) classes.push('is-today');
                if (dateKey === selectedDate) classes.push('is-selected');
                if (booked) {
                    classes.push('is-booked');
                    ariaLabel += ', booked';
                }
                if (past) {
                    classes.push('is-past');
                    ariaLabel += ', unavailable';
                }

                return '<button type="button" class="' + classes.join(' ') + '"'
                    + ' data-sdp-date="' + escapeHtml(dateKey) + '"'
                    + ' aria-label="' + escapeHtml(ariaLabel) + '"'
                    + (disabled ? ' disabled' : '')
                    + '>'
                    + escapeHtml(cell.label)
                    + '</button>';
            }).join('');

            mount.innerHTML = ''
                + '<button type="button" class="sdp-trigger" data-sdp-trigger aria-expanded="' + (open ? 'true' : 'false') + '">'
                +   '<span class="sdp-trigger-text ' + (!selectedDate ? 'is-placeholder' : '') + '">' + escapeHtml(triggerText) + '</span>'
                +   '<span class="sdp-trigger-icon" aria-hidden="true">'
                +     '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">'
                +       '<rect x="3" y="4" width="18" height="18" rx="2"></rect>'
                +       '<line x1="16" y1="2" x2="16" y2="6"></line>'
                +       '<line x1="8" y1="2" x2="8" y2="6"></line>'
                +       '<line x1="3" y1="10" x2="21" y2="10"></line>'
                +     '</svg>'
                +   '</span>'
                + '</button>'
                + '<p class="sdp-help-text ' + (availabilityMessage ? 'is-warning' : '') + '">' + escapeHtml(helperMessage) + '</p>'
                + '<div class="sdp-popover" data-sdp-popover' + (open ? '' : ' hidden') + '>'
                +   '<div class="sdp-selected-row">'
                +     '<div>'
                +       '<p class="sdp-selected-label">Selected Date</p>'
                +       '<p class="sdp-selected-value ' + (!selectedDate ? 'is-placeholder' : '') + '">' + escapeHtml(selectedDate ? formatDateForDisplay(selectedDate) : 'No date selected') + '</p>'
                +     '</div>'
                +     (selectedDate ? '<button type="button" class="sdp-clear-btn" data-sdp-clear>Clear</button>' : '')
                +   '</div>'
                +   '<div class="sdp-legend">'
                +     '<span class="sdp-legend-item"><span class="sdp-legend-swatch is-selected"></span>Selected</span>'
                +     '<span class="sdp-legend-item"><span class="sdp-legend-swatch is-booked"></span>Booked</span>'
                +   '</div>'
                +   '<div class="sdp-calendar-head">'
                +     '<button type="button" class="sdp-nav-btn" data-sdp-prev ' + (canGoPrev ? '' : 'disabled') + ' aria-label="Previous month">‹</button>'
                +     '<div class="sdp-month-title">' + escapeHtml(formatMonthLabel(visibleMonth)) + '</div>'
                +     '<button type="button" class="sdp-nav-btn" data-sdp-next aria-label="Next month">›</button>'
                +   '</div>'
                +   '<div class="sdp-weekdays">'
                +     '<div class="sdp-weekday">Sun</div>'
                +     '<div class="sdp-weekday">Mon</div>'
                +     '<div class="sdp-weekday">Tue</div>'
                +     '<div class="sdp-weekday">Wed</div>'
                +     '<div class="sdp-weekday">Thu</div>'
                +     '<div class="sdp-weekday">Fri</div>'
                +     '<div class="sdp-weekday">Sat</div>'
                +   '</div>'
                +   '<div class="sdp-grid">' + gridHtml + '</div>'
                + '</div>';

            setErrorState(selectedIsUnavailable);

            var trigger = mount.querySelector('[data-sdp-trigger]');
            var prevBtn = mount.querySelector('[data-sdp-prev]');
            var nextBtn = mount.querySelector('[data-sdp-next]');
            var clearBtn = mount.querySelector('[data-sdp-clear]');

            if (trigger) {
                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    setOpen(!open);
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    visibleMonth = shiftMonth(visibleMonth, -1);
                    render();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    visibleMonth = shiftMonth(visibleMonth, 1);
                    render();
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    clear();
                });
            }

            Array.prototype.slice.call(mount.querySelectorAll('[data-sdp-date]')).forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    var value = button.getAttribute('data-sdp-date');
                    if (!value || isPast(value) || isUnavailable(value)) {
                        return;
                    }

                    setSelectedDate(value);
                    setOpen(false);
                });
            });
        }

        async function refreshAvailability() {
            try {
                var response = await fetch(endpoint, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Request failed.');
                }

                var payload = await response.json().catch(function () { return {}; });
                unavailableDates = Array.isArray(payload.unavailable_dates)
                    ? payload.unavailable_dates.map(normalizeDate).filter(Boolean)
                    : [];
                availabilityMessage = '';
            } catch (error) {
                availabilityMessage = fetchErrorText;
            }

            render();
            return unavailableDates.slice();
        }

        function isSelectedDateUnavailable() {
            return Boolean(selectedDate && isUnavailable(selectedDate));
        }

        document.addEventListener('click', function (event) {
            if (!open) {
                return;
            }

            if (mount.contains(event.target)) {
                return;
            }

            setOpen(false);
        });

        syncInput();
        render();
        refreshAvailability();

        return {
            clear: clear,
            getValue: function () { return selectedDate; },
            isSelectedDateUnavailable: isSelectedDateUnavailable,
            refreshAvailability: refreshAvailability,
            setErrorState: setErrorState,
            setValue: function (value) { setSelectedDate(value || ''); }
        };
    };
})();
</script>
