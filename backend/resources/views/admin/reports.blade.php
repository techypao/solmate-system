@extends('layouts.app', ['title' => 'Admin Reports'])

@section('content')
    <style>
        .reports-page {
            display: grid;
            gap: 22px;
        }

        .reports-page .card + .card {
            margin-top: 0;
        }

        .reports-hero {
            display: grid;
            gap: 18px;
        }

        .reports-hero-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .reports-kicker {
            margin: 0 0 8px;
            color: #6b7f99;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .reports-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .reports-filter-copy {
            margin: 0;
            color: #52606d;
            font-size: 14px;
        }

        .reports-filter-chips {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .reports-filter-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid #d6e2ef;
            background: #f8fbff;
            color: #425466;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition: all .18s ease;
        }

        .reports-filter-chip:hover {
            border-color: #b8cadf;
            background: #ffffff;
            color: #173b63;
            text-decoration: none;
        }

        .reports-filter-chip.active {
            border-color: #173b63;
            background: #173b63;
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(23, 59, 99, 0.18);
        }

        .reports-summary-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .reports-summary-card {
            display: grid;
            gap: 8px;
            padding: 18px;
            border: 1px solid #dbe7f3;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .reports-summary-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .reports-summary-value {
            color: #102a43;
            font-size: 32px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .reports-grid {
            display: grid;
            gap: 22px;
        }

        .reports-chart-grid {
            display: grid;
            align-items: stretch;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .reports-chart-card {
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 16px;
            height: 100%;
            margin-top: 0 !important;
        }

        .reports-card-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: flex-start;
            gap: 12px 16px;
        }

        .reports-card-head > :first-child {
            min-width: 0;
        }

        .reports-card-title {
            margin: 0;
            color: #102a43;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .reports-card-copy {
            margin: 6px 0 0;
            color: #52606d;
            font-size: 14px;
            line-height: 1.65;
        }

        .reports-card-total {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            justify-self: end;
            align-self: start;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            background: #eef6ff;
            color: #173b63;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .reports-chart-shell {
            position: relative;
            height: 100%;
            min-height: 300px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #fbfdff;
        }

        .reports-chart-shell canvas {
            width: 100% !important;
            height: 280px !important;
        }

        .reports-chart-empty {
            display: grid;
            place-items: center;
            min-height: 280px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
            border: 1px dashed #d7e1ec;
            border-radius: 16px;
            background: #ffffff;
            padding: 18px;
        }

        .reports-table-wrap {
            overflow-x: auto;
        }

        .reports-rate {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 74px;
            min-height: 32px;
            padding: 0 10px;
            border-radius: 999px;
            background: #eef6ff;
            color: #173b63;
            font-size: 12px;
            font-weight: 800;
        }

        .reports-activity-grid {
            display: grid;
            align-items: stretch;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .reports-empty-row {
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 1200px) {
            .reports-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 980px) {
            .reports-chart-grid,
            .reports-activity-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .reports-summary-grid {
                grid-template-columns: 1fr;
            }

            .reports-card-head {
                grid-template-columns: 1fr;
            }

            .reports-card-total {
                justify-self: start;
            }

            .reports-filter-bar {
                align-items: flex-start;
                flex-direction: column;
            }

            .reports-filter-chips {
                width: 100%;
            }

            .reports-filter-chip {
                flex: 1 1 calc(50% - 10px);
            }
        }
    </style>

    <div class="reports-page">
        <section class="card reports-hero">
            <div class="reports-hero-head">
                <div>
                    <p class="reports-kicker">Admin Insights</p>
                    <h1 class="page-title">Reports</h1>
                    <p class="page-copy" style="margin-bottom: 0;">Track customer growth, request flow, technician workload, and quotation activity using the existing SolMate records already stored in the system.</p>
                </div>
                <span class="reports-card-total">{{ $selectedRangeLabel }}</span>
            </div>

            <div class="reports-filter-bar">
                <p class="reports-filter-copy">Showing dashboard data for <strong>{{ $selectedRangeLabel }}</strong>. Change the date range to refresh the cards, charts, technician table, and recent activity.</p>
                <div class="reports-filter-chips">
                    @foreach ($rangeOptions as $rangeKey => $rangeLabel)
                        <a
                            href="{{ route('admin.reports', ['range' => $rangeKey]) }}"
                            class="reports-filter-chip {{ $selectedRange === $rangeKey ? 'active' : '' }}"
                        >
                            {{ $rangeLabel }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="reports-grid">
            <div class="reports-summary-grid">
                @foreach ($summaryCards as $card)
                    <article class="reports-summary-card">
                        <div class="reports-summary-label">{{ $card['label'] }}</div>
                        <div class="reports-summary-value">{{ number_format($card['value']) }}</div>
                    </article>
                @endforeach
            </div>

            <section class="reports-chart-grid">
                @php
                    $chartCards = [
                        'requestsByType' => [
                            'title' => 'Requests by Type',
                            'copy' => 'Breakdown of inspection, installation, and maintenance requests in the selected range.',
                            'dataset' => $requestTypeChart,
                        ],
                        'requestsByStatus' => [
                            'title' => 'Requests by Status',
                            'copy' => 'Grouped view of request progress so admin can spot where work is accumulating.',
                            'dataset' => $requestStatusChart,
                        ],
                        'quotationsByType' => [
                            'title' => 'Quotations by Type',
                            'copy' => 'Compare how many quotations are still initial estimates versus completed final quotations.',
                            'dataset' => $quotationTypeChart,
                        ],
                        'quotationsByStatus' => [
                            'title' => 'Quotations by Status',
                            'copy' => 'Monitor final quotation decisions and follow-up needs across the current reporting window.',
                            'dataset' => $quotationStatusChart,
                        ],
                    ];
                @endphp

                @foreach ($chartCards as $chartKey => $chartCard)
                    <article class="card reports-chart-card">
                        <div class="reports-card-head">
                            <div>
                                <h2 class="reports-card-title">{{ $chartCard['title'] }}</h2>
                                <p class="reports-card-copy">{{ $chartCard['copy'] }}</p>
                            </div>
                            <span class="reports-card-total">{{ number_format($chartCard['dataset']['total']) }} total</span>
                        </div>

                        <div class="reports-chart-shell">
                            <canvas id="{{ $chartKey }}Chart" data-chart-key="{{ $chartKey }}" @if (!$chartCard['dataset']['hasData']) hidden @endif></canvas>
                            @unless ($chartCard['dataset']['hasData'])
                                <div class="reports-chart-empty">No data available for this chart in the selected date range.</div>
                            @endunless
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="card">
                <div class="reports-card-head">
                    <div>
                        <h2 class="reports-card-title">Technician Performance</h2>
                        <p class="reports-card-copy">Each technician shows assigned workload and completion progress based on requests created within the selected range.</p>
                    </div>
                    <span class="reports-card-total">{{ number_format($technicianPerformance->count()) }} technicians</span>
                </div>

                <div class="reports-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Technician</th>
                                <th>Email</th>
                                <th>Total Assigned</th>
                                <th>Completed</th>
                                <th>Pending / In Progress</th>
                                <th>Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($technicianPerformance as $technician)
                                <tr>
                                    <td>{{ $technician['name'] }}</td>
                                    <td>{{ $technician['email'] }}</td>
                                    <td>{{ number_format($technician['total_assigned']) }}</td>
                                    <td>{{ number_format($technician['completed_requests']) }}</td>
                                    <td>{{ number_format($technician['active_requests']) }}</td>
                                    <td><span class="reports-rate">{{ rtrim(rtrim(number_format($technician['completion_rate'], 1), '0'), '.') }}%</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="reports-empty-row">No technicians found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="reports-activity-grid">
                <article class="card">
                    <div class="reports-card-head">
                        <div>
                            <h2 class="reports-card-title">Recent Requests</h2>
                            <p class="reports-card-copy">Latest five requests from the chosen reporting window.</p>
                        </div>
                        <span class="reports-card-total">{{ number_format($recentRequests->count()) }} items</span>
                    </div>

                    <div class="reports-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Type</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentRequests as $requestItem)
                                    <tr>
                                        <td>{{ $requestItem['label'] }}</td>
                                        <td>{{ $requestItem['type'] }}</td>
                                        <td>{{ $requestItem['customer_name'] }}</td>
                                        <td>{{ $requestItem['status'] }}</td>
                                        <td>{{ optional($requestItem['created_at'])->format('M d, Y h:i A') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="reports-empty-row">No requests found for this date range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="card">
                    <div class="reports-card-head">
                        <div>
                            <h2 class="reports-card-title">Recent Quotations</h2>
                            <p class="reports-card-copy">Latest five quotations from the chosen reporting window.</p>
                        </div>
                        <span class="reports-card-total">{{ number_format($recentQuotations->count()) }} items</span>
                    </div>

                    <div class="reports-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Quotation</th>
                                    <th>Type</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentQuotations as $quotation)
                                    <tr>
                                        <td>{{ $quotation['label'] }}</td>
                                        <td>{{ $quotation['type'] }}</td>
                                        <td>{{ $quotation['customer_name'] }}</td>
                                        <td>{{ $quotation['status'] }}</td>
                                        <td>{{ optional($quotation['created_at'])->format('M d, Y h:i A') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="reports-empty-row">No quotations found for this date range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script type="application/json" id="__reports-chart-data">
        {!! json_encode([
            'requestsByType' => $requestTypeChart,
            'requestsByStatus' => $requestStatusChart,
            'quotationsByType' => $quotationTypeChart,
            'quotationsByStatus' => $quotationStatusChart,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
    <script>
        (function () {
            const chartSource = document.getElementById('__reports-chart-data');

            if (!chartSource || typeof Chart === 'undefined') {
                return;
            }

            const chartData = JSON.parse(chartSource.textContent || '{}');

            Object.entries(chartData).forEach(([chartKey, dataset]) => {
                if (!dataset || !dataset.hasData) {
                    return;
                }

                const canvas = document.querySelector(`[data-chart-key="${chartKey}"]`);

                if (!canvas) {
                    return;
                }

                new Chart(canvas, {
                    type: 'pie',
                    data: {
                        labels: dataset.labels,
                        datasets: [{
                            data: dataset.values,
                            backgroundColor: dataset.colors,
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 18,
                                    color: '#17324f',
                                    font: {
                                        family: 'Plus Jakarta Sans, Segoe UI, sans-serif',
                                        size: 12,
                                        weight: '700',
                                    },
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const value = Number(context.raw || 0);
                                        const total = Number(dataset.total || 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

                                        return `${context.label}: ${value} (${percentage}%)`;
                                    },
                                },
                            },
                        },
                    },
                });
            });
        }());
    </script>
@endpush
