@extends('layouts.app', ['title' => 'Ambil Nomor Antrian', 'pageClass' => 'theme-public'])

@section('content')
    <div class="card" style="margin-bottom: 16px;">
        <h2 class="title" style="font-size: 20px; margin-bottom: 10px;">Display Antrian Hari Ini</h2>
        <p class="subtitle" style="margin-bottom: 12px;">Status ini otomatis mengikuti aksi admin: dipanggil, diproses, dan antrean berikutnya.</p>

        <div class="stat-grid" id="queue-board">
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

    <div class="card">
        <h1 class="title">Sistem Antrian Online</h1>
        <p class="subtitle">Service Center Infinix Roxy Mas dengan pengalaman antrean bernuansa mall modern.</p>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('queue.store') }}">
            @csrf
            <div class="row">
                <div class="col">
                    <label for="customer_name">Nama Pelanggan</label>
                    <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required>
                </div>
                <div class="col">
                    <label for="customer_phone">Nomor Telepon</label>
                    <input id="customer_phone" name="customer_phone" type="text" value="{{ old('customer_phone') }}" inputmode="numeric" pattern="[0-9]{8,25}" maxlength="25" oninvalid="this.setCustomValidity('Nomor telepon hanya boleh angka (8-25 digit).')" oninput="const raw = this.value; const cleaned = raw.replace(/[^0-9]/g, ''); if (raw !== cleaned) { this.value = cleaned; this.setCustomValidity('Nomor telepon hanya boleh angka.'); this.reportValidity(); } else { this.setCustomValidity(''); }" required>
                </div>
            </div>
            <div class="row mt-12">
                <div class="col">
                    <label for="device_type">Tipe Perangkat</label>
                    <input id="device_type" name="device_type" type="text" value="{{ old('device_type') }}" required>
                </div>
            </div>
            <div class="row mt-12">
                <div class="col">
                    <label for="complaint">Keluhan</label>
                    <textarea id="complaint" name="complaint" required>{{ old('complaint') }}</textarea>
                </div>
            </div>

            <div class="mt-20">
                <button type="submit">Ambil Nomor Antrian</button>
            </div>
        </form>

        <div class="mt-20">
            <a class="quick-link" href="{{ route('admin.login') }}">Login Admin</a>
        </div>
    </div>

    <script>
        (() => {
            const inService = document.getElementById('board-in-service');
            const inServiceList = document.getElementById('board-in-service-list');
            const inServiceCount = document.getElementById('board-in-service-count');
            const called = document.getElementById('board-called');
            const calledCount = document.getElementById('board-called-count');
            const nextWaiting = document.getElementById('board-next-waiting');
            const waitingCount = document.getElementById('board-waiting-count');
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

            fetchBoard();
            setInterval(fetchBoard, 5000);
            setInterval(tickCountdown, 1000);
        })();
    </script>
@endsection
