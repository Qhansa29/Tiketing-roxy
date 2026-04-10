@extends('layouts.app', ['title' => 'Nomor Antrian Berhasil Dibuat', 'pageClass' => 'theme-public'])

@php
    $statusMap = [
        'waiting' => ['label' => 'Menunggu', 'class' => 'b-waiting'],
        'called' => ['label' => 'Dipanggil', 'class' => 'b-called'],
        'in_service' => ['label' => 'Diproses', 'class' => 'b-in-service'],
        'done' => ['label' => 'Selesai', 'class' => 'b-done'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'b-cancelled'],
    ];
    $statusMeta = $statusMap[$queue->status] ?? ['label' => ucfirst($queue->status), 'class' => 'b-cancelled'];
@endphp

@section('content')
    <div class="card">
        <h1 class="title">Nomor Antrian Anda</h1>
        <p class="subtitle">Silakan simpan nomor ini dan tunggu dipanggil admin di area layanan Roxy Mas.</p>

        <div style="font-size: 36px; font-weight: bold; margin: 12px 0 20px;">
            {{ $queue->queue_number }}
        </div>

        <div class="stat-box" style="margin-bottom: 14px;">
            <div class="stat-label">Status Antrian Anda Saat Ini</div>
            <div style="margin-top: 8px;">
                <span class="badge {{ $statusMeta['class'] }}" id="my-ticket-status">{{ $statusMeta['label'] }}</span>
            </div>
        </div>

        <table>
            <tr>
                <th>Nama</th>
                <td>{{ $queue->customer_name }}</td>
            </tr>
            <tr>
                <th>Telepon</th>
                <td>{{ $queue->customer_phone }}</td>
            </tr>
            <tr>
                <th>Tipe Perangkat</th>
                <td>{{ $queue->device_type }}</td>
            </tr>
            <tr>
                <th>Waktu Daftar</th>
                <td>{{ $queue->created_at?->format('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td id="my-ticket-status-text">{{ $statusMeta['label'] }}</td>
            </tr>
            <tr>
                <th>Estimasi Pengerjaan</th>
                <td id="my-ticket-estimate-text">{{ $queue->service_estimate_minutes ? $queue->service_estimate_minutes.' menit' : '-' }}</td>
            </tr>
        </table>

        <div class="card mt-20" style="padding: 14px;">
            <h2 class="title" style="font-size: 18px; margin-bottom: 10px;">Display Antrian Saat Ini</h2>
            <div class="stat-grid">
                <div class="stat-box">
                    <div class="stat-label">Sedang Diproses</div>
                    <div class="stat-value" id="board-in-service">-</div>
                    <div class="subtitle" id="board-in-service-list" style="margin: 6px 0 0; line-height: 1.4;">-</div>
                    <div class="subtitle" style="margin: 6px 0 0;">Total diproses: <strong id="board-in-service-count">0</strong></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Sedang Dipanggil</div>
                    <div class="stat-value" id="board-called">-</div>
                    <div class="subtitle" style="margin: 6px 0 0;">Total dipanggil: <strong id="board-called-count">0</strong></div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Antrean Berikutnya</div>
                    <div class="stat-value" id="board-next-waiting">-</div>
                    <div class="subtitle" style="margin: 6px 0 0;">Total menunggu: <strong id="board-waiting-count">0</strong></div>
                </div>
            </div>
        </div>

        <div class="mt-20">
            <a class="quick-link" href="{{ route('queue.create') }}">Ambil nomor antrian baru</a>
        </div>
    </div>

    <script>
        (() => {
            const statusBadge = document.getElementById('my-ticket-status');
            const statusText = document.getElementById('my-ticket-status-text');
            const inService = document.getElementById('board-in-service');
            const inServiceList = document.getElementById('board-in-service-list');
            const inServiceCount = document.getElementById('board-in-service-count');
            const called = document.getElementById('board-called');
            const calledCount = document.getElementById('board-called-count');
            const nextWaiting = document.getElementById('board-next-waiting');
            const waitingCount = document.getElementById('board-waiting-count');
            const estimateText = document.getElementById('my-ticket-estimate-text');
            let inServiceEntries = [];

            const renderValue = (value) => value && value.trim() !== '' ? value : '-';

            const formatRemaining = (seconds) => {
                if (seconds === null || seconds === undefined || Number.isNaN(Number(seconds))) {
                    return 'estimasi -';
                }

                const normalized = Math.max(0, Math.floor(Number(seconds)));
                const hours = Math.floor(normalized / 3600);
                const minutes = Math.floor((normalized % 3600) / 60);
                const secs = normalized % 60;

                if (hours > 0) {
                    return `sisa ${hours} Jam ${minutes} Menit ${secs} Detik`;
                }

                return `sisa ${minutes} Menit ${secs} Detik`;
            };

            const renderInServiceEntries = () => {
                if (!inServiceEntries.length) {
                    inServiceList.textContent = '-';
                    return;
                }

                inServiceList.innerHTML = inServiceEntries
                    .map((entry) => `${entry.queue_number} (${formatRemaining(entry.remaining_seconds)})`)
                    .join('<br>');
            };

            const updateBoard = (data) => {
                const inServiceList = Array.isArray(data.in_service_list) ? data.in_service_list : [];
                inService.textContent = inServiceList.length ? inServiceList.join(', ') : renderValue(data.in_service);
                inServiceCount.textContent = Number.isFinite(Number(data.in_service_count)) ? String(data.in_service_count) : '0';
                inServiceEntries = Array.isArray(data.in_service_entries)
                    ? data.in_service_entries.map((entry) => ({
                        queue_number: entry.queue_number,
                        remaining_seconds: entry.remaining_seconds,
                    }))
                    : [];
                renderInServiceEntries();
                const calledNumbers = Array.isArray(data.called_list) ? data.called_list : [];
                called.textContent = calledNumbers.length ? calledNumbers.join(', ') : renderValue(data.called);
                calledCount.textContent = Number.isFinite(Number(data.called_count)) ? String(data.called_count) : '0';
                nextWaiting.textContent = renderValue(data.next_waiting);
                waitingCount.textContent = Number.isFinite(Number(data.waiting_count)) ? String(data.waiting_count) : '0';
            };

            const updateMyStatus = (data) => {
                statusBadge.textContent = data.status_label || '-';
                statusBadge.className = 'badge ' + (data.status_class || 'b-cancelled');
                statusText.textContent = data.status_label || data.status || '-';
                estimateText.textContent = data.service_estimate_text || '-';
            };

            const tickCountdown = () => {
                if (!inServiceEntries.length) {
                    return;
                }

                inServiceEntries = inServiceEntries.map((entry) => {
                    if (entry.remaining_seconds === null || entry.remaining_seconds === undefined) {
                        return entry;
                    }

                    return {
                        ...entry,
                        remaining_seconds: Math.max(0, Number(entry.remaining_seconds) - 1),
                    };
                });

                renderInServiceEntries();
            };

            const fetchBoard = async () => {
                try {
                    const response = await fetch('{{ route('queue.board') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    updateBoard(data);
                } catch (error) {
                    // Keep last known board values if request fails.
                }
            };

            const fetchMyStatus = async () => {
                try {
                    const response = await fetch('{{ route('queue.status', $queue) }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    updateMyStatus(data);
                } catch (error) {
                    // Keep last known ticket status if request fails.
                }
            };

            fetchBoard();
            fetchMyStatus();
            setInterval(fetchBoard, 5000);
            setInterval(fetchMyStatus, 5000);
            setInterval(tickCountdown, 1000);
        })();
    </script>
@endsection
