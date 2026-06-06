<style>
    .sdp-field-host {
        position: relative;
        display: grid;
        gap: 8px;
    }

    .sdp-trigger {
        width: 100%;
        min-height: 52px;
        border: 1px solid #DDE7EE;
        border-radius: 14px;
        background: #fff;
        padding: 0 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #123A5A;
        font-size: 14px;
        font-weight: 600;
        text-align: left;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
    }

    .sdp-trigger:hover {
        border-color: #DDE7EE;
        background: #F8FAFC;
    }

    .sdp-trigger:focus-visible {
        outline: none;
        border-color: #7DDFF2;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
    }

    .sdp-field-host.has-error .sdp-trigger {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .09);
    }

    .sdp-trigger-text {
        min-width: 0;
        color: #123A5A;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sdp-trigger-text.is-placeholder {
        color: #7F92A3;
        font-weight: 500;
    }

    .sdp-trigger-icon {
        flex-shrink: 0;
        color: #F4D000;
    }

    .sdp-help-text {
        font-size: 12px;
        line-height: 1.55;
        color: #7F92A3;
        margin: 0;
    }

    .sdp-help-text.is-warning {
        color: #b45309;
    }

    .sdp-popover {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        z-index: 40;
        width: min(360px, calc(100vw - 32px));
        max-width: calc(100vw - 32px);
        background: #fff;
        border: 1px solid #DDE7EE;
        border-radius: 18px;
        box-shadow: 0 24px 44px rgba(15, 23, 42, .16);
        padding: 14px;
        box-sizing: border-box;
    }

    .sdp-popover.is-fixed {
        position: fixed;
        top: var(--sdp-popover-top, auto);
        right: auto;
        bottom: auto;
        left: var(--sdp-popover-left, 16px);
        z-index: 10000;
        width: min(360px, calc(100vw - 32px));
        max-height: calc(100vh - 32px);
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .sdp-popover.is-compact {
        padding: 12px;
    }

    .sdp-popover.is-compact .sdp-selected-row {
        margin-bottom: 10px;
    }

    .sdp-popover.is-compact .sdp-legend {
        margin-bottom: 10px;
    }

    .sdp-popover.is-compact .sdp-day,
    .sdp-popover.is-compact .sdp-day-spacer {
        min-height: 36px;
    }

    .sdp-popover[hidden] {
        display: none;
    }

    .sdp-selected-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .sdp-selected-label {
        margin: 0 0 4px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #7F92A3;
    }

    .sdp-selected-value {
        margin: 0;
        color: #123A5A;
        font-size: 15px;
        font-weight: 700;
    }

    .sdp-selected-value.is-placeholder {
        color: #7F92A3;
        font-weight: 600;
    }

    .sdp-clear-btn {
        border: 0;
        background: transparent;
        color: #F4D000;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        padding: 6px 4px;
    }

    .sdp-legend {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .sdp-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: #5E7288;
        font-weight: 600;
    }

    .sdp-legend-swatch {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        border: 1px solid #DDE7EE;
        background: #fff;
    }

    .sdp-legend-swatch.is-selected {
        background: #123A5A;
        border-color: #123A5A;
    }

    .sdp-legend-swatch.is-booked {
        background:
            repeating-linear-gradient(135deg, #DDE7EE 0, #DDE7EE 4px, #f8fafc 4px, #f8fafc 8px);
        border-color: #DDE7EE;
    }

    .sdp-calendar-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .sdp-month-title {
        color: #123A5A;
        font-size: 15px;
        font-weight: 800;
        text-align: center;
        flex: 1;
    }

    .sdp-nav-btn {
        width: 34px;
        height: 34px;
        border: 1px solid #DDE7EE;
        border-radius: 10px;
        background: #fff;
        color: #123A5A;
        font-size: 18px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    .sdp-nav-btn:hover:not(:disabled) {
        background: #F8FAFC;
        border-color: #DDE7EE;
    }

    .sdp-nav-btn:disabled {
        cursor: not-allowed;
        opacity: .45;
    }

    .sdp-weekdays,
    .sdp-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 6px;
        min-width: 0;
    }

    .sdp-weekday {
        text-align: center;
        color: #7F92A3;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding-bottom: 2px;
        min-width: 0;
        overflow: hidden;
    }

    .sdp-day-spacer {
        min-height: 42px;
    }

    .sdp-day {
        width: 100%;
        min-width: 0;
        min-height: 42px;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        background: #fff;
        padding: 0;
        color: #123A5A;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform .15s ease, border-color .15s ease, background-color .15s ease, color .15s ease;
        position: relative;
    }

    .sdp-day:hover:not(:disabled) {
        transform: translateY(-1px);
        border-color: #F4D000;
        background: #fffdf5;
    }

    .sdp-day.is-today {
        border-color: #E6C200;
        color: #E6C200;
    }

    .sdp-day.is-selected {
        background: #123A5A;
        border-color: #123A5A;
        color: #fff;
    }

    .sdp-day.is-booked {
        cursor: not-allowed;
        color: #7F92A3;
        border-color: #DDE7EE;
        background:
            repeating-linear-gradient(135deg, #f8fafc 0, #f8fafc 6px, #eef2f7 6px, #eef2f7 12px);
    }

    .sdp-day.is-booked::after {
        content: '';
        position: absolute;
        width: 18px;
        height: 2px;
        background: #DDE7EE;
        border-radius: 999px;
        transform: rotate(-35deg);
    }

    .sdp-day.is-selected.is-booked {
        background: #fee2e2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .sdp-day.is-selected.is-booked::after {
        background: #f87171;
    }

    .sdp-day:disabled {
        cursor: not-allowed;
    }

    .sdp-day.is-past {
        color: #DDE7EE;
        background: #f8fafc;
        border-color: #edf2f7;
        cursor: not-allowed;
    }

    @media (max-width: 640px) {
        .sdp-popover {
            width: min(100%, calc(100vw - 32px));
            max-width: calc(100vw - 32px);
        }

        .sdp-popover.is-fixed {
            width: calc(100vw - 32px);
            max-width: calc(100vw - 32px);
            max-height: calc(100vh - 32px);
        }
    }

    @media (max-width: 480px) {
        .sdp-popover.is-fixed {
            padding: 10px;
        }

        .sdp-calendar-head {
            gap: 8px;
        }

        .sdp-weekdays,
        .sdp-grid {
            gap: 4px;
        }

        .sdp-nav-btn {
            width: 32px;
            height: 32px;
        }

        .sdp-day,
        .sdp-day-spacer {
            min-height: 36px;
        }

        .sdp-weekday {
            font-size: 10px;
        }
    }
</style>
