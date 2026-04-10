@extends('layouts.app', ['title' => 'Dashboard Admin Antrian', 'pageClass' => 'theme-admin'])

@php
    $statusMap = [
        'waiting' => ['label' => 'Menunggu', 'class' => 'b-waiting'],
        'called' => ['label' => 'Dipanggil', 'class' => 'b-called'],
        'in_service' => ['label' => 'Diproses', 'class' => 'b-in-service'],
        'done' => ['label' => 'Selesai', 'class' => 'b-done'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'b-cancelled'],
    ];
@endphp

@section('content')
    <div class="card">
        <div class="row" style="justify-content: space-between; align-items: center;">
            <div>
                <h1 class="title" style="margin-bottom: 4px;">Dashboard Admin</h1>
                <p class="subtitle" style="margin-bottom:0;">Halo, {{ session('admin_user_name') }}</p>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <a class="quick-link" href="{{ route('admin.profile') }}">Profil Admin</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="secondary">Logout</button>
                </form>
            </div>
        </div>

        <div class="stat-grid mt-20">
            <div class="stat-box">
                <div class="stat-label">Total Hari Ini</div>
                <strong class="stat-value" id="stat-total-today">{{ $stats['total_today'] }}</strong>
            </div>
            <div class="stat-box">
                <div class="stat-label">Menunggu Hari Ini</div>
                <strong class="stat-value" id="stat-waiting-today">{{ $stats['waiting_today'] }}</strong>
            </div>
            <div class="stat-box">
                <div class="stat-label">Selesai Hari Ini</div>
                <strong class="stat-value" id="stat-done-today">{{ $stats['done_today'] }}</strong>
            </div>
        </div>

        <div class="mt-20">
            <form method="POST" action="{{ route('admin.queues.call-next') }}" style="display:inline-block;">
                @csrf
                <button type="submit">Panggil Antrian Berikutnya</button>
            </form>
        </div>
    </div>

    <div class="card mt-20">
        <h2 class="title" style="font-size: 18px;">Antrian Hari Ini</h2>
        <table>
            <thead>
            <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>Perangkat</th>
                <th>Estimasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody id="today-queues-body">
            @forelse($todayQueues as $queue)
                <tr>
                    <td>{{ $queue->queue_number }}</td>
                    <td>
                        <strong>{{ $queue->customer_name }}</strong><br>
                        <small>{{ $queue->customer_phone }}</small><br>
                        <small>{{ $queue->complaint }}</small>
                    </td>
                    <td>{{ $queue->device_type }}</td>
                    <td>{{ $queue->service_estimate_minutes ? $queue->service_estimate_minutes.' menit' : '-' }}</td>
                    <td>
                        <span class="badge {{ $statusMap[$queue->status]['class'] }}">{{ $statusMap[$queue->status]['label'] }}</span>
                    </td>
                    <td class="actions">
                        <form method="POST" action="{{ route('admin.queues.call', $queue) }}">
                            @csrf
                            <button type="submit">Panggil</button>
                        </form>
                        <form method="POST" action="{{ route('admin.queues.start', $queue) }}" onsubmit="const estimate = prompt('Masukkan estimasi pengerjaan (menit):'); if (estimate === null || estimate.trim() === '') { alert('Estimasi pengerjaan wajib diisi.'); return false; } if (!/^\d+$/.test(estimate.trim()) || Number(estimate) < 1) { alert('Estimasi harus berupa angka menit yang valid.'); return false; } this.querySelector('input[name=service_estimate_minutes]').value = estimate.trim(); return true;">
                            @csrf
                            <input type="hidden" name="service_estimate_minutes" value="">
                            <button type="submit" class="secondary">Proses</button>
                        </form>
                        <form method="POST" action="{{ route('admin.queues.finish', $queue) }}" onsubmit="const note = prompt('Masukkan catatan admin untuk pelanggan:'); if (note === null || note.trim() === '') { alert('Catatan admin wajib diisi untuk status selesai.'); return false; } this.querySelector('input[name=admin_note]').value = note; return true;">
                            @csrf
                            <input type="hidden" name="admin_note" value="">
                            <button type="submit">Selesai</button>
                        </form>
                        <form method="POST" action="{{ route('admin.queues.cancel', $queue) }}">
                            @csrf
                            <button type="submit" class="secondary">Batalkan</button>
                        </form>
                        <form method="POST" action="{{ route('admin.queues.destroy', $queue) }}" onsubmit="return confirm('Yakin ingin menolak dan menghapus antrian ini secara permanen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="secondary">Tolak/Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada antrian hari ini.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card mt-20">
        <h2 class="title" style="font-size: 18px;">Riwayat Antrian</h2>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="row">
            <div class="col">
                <label for="date_from">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="col">
                <label for="date_to">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}">
            </div>
            <div class="col" style="align-self:end;">
                <button type="submit">Filter</button>
            </div>
        </form>

        <table>
            <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Antrian</th>
                <th>Pelanggan</th>
                <th>Estimasi</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($historyQueues as $queue)
                <tr>
                    <td>{{ $queue->queue_date?->format('d-m-Y') }}</td>
                    <td>{{ $queue->queue_number }}</td>
                    <td>{{ $queue->customer_name }}</td>
                    <td>{{ $queue->service_estimate_minutes ? $queue->service_estimate_minutes.' menit' : '-' }}</td>
                    <td>
                        <span class="badge {{ $statusMap[$queue->status]['class'] }}">{{ $statusMap[$queue->status]['label'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Data riwayat tidak ditemukan.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <script>
        (() => {
            const liveUrl = '{{ route('admin.dashboard.live') }}';
            const dateFrom = @json($dateFrom);
            const dateTo = @json($dateTo);
            const statusMap = @json($statusMap);
            const tableBody = document.getElementById('today-queues-body');
            const statTotal = document.getElementById('stat-total-today');
            const statWaiting = document.getElementById('stat-waiting-today');
            const statDone = document.getElementById('stat-done-today');
            let knownTotalToday = Number(@json($stats['total_today']));
            let audioContext = null;
            let audioUnlocked = false;

            const unlockAudio = () => {
                if (audioUnlocked) {
                    return;
                }

                try {
                    audioContext = audioContext || new (window.AudioContext || window.webkitAudioContext)();
                    if (audioContext.state === 'suspended') {
                        audioContext.resume();
                    }
                    audioUnlocked = true;
                } catch (error) {
                    audioContext = null;
                }
            };

            const playBellSound = () => {
                if (!audioUnlocked || !audioContext) {
                    return;
                }

                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioContext.currentTime);
                gainNode.gain.setValueAtTime(0.0001, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.12, audioContext.currentTime + 0.02);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, audioContext.currentTime + 0.5);

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.55);
            };

            const renderRows = (queues) => {
                if (!queues.length) {
                    tableBody.innerHTML = '<tr><td colspan="6">Belum ada antrian hari ini.</td></tr>';
                    return;
                }

                tableBody.innerHTML = queues.map((queue) => {
                    const status = statusMap[queue.status] || { label: queue.status, class: 'b-cancelled' };

                    return `
                        <tr>
                            <td>${queue.queue_number}</td>
                            <td>
                                <strong>${queue.customer_name}</strong><br>
                                <small>${queue.customer_phone}</small><br>
                                <small>${queue.complaint}</small>
                            </td>
                            <td>${queue.device_type}</td>
                            <td>${queue.service_estimate_minutes ? `${queue.service_estimate_minutes} menit` : '-'}</td>
                            <td><span class="badge ${status.class}">${status.label}</span></td>
                            <td class="actions">
                                <form method="POST" action="/admin/queues/${queue.id}/call">
                                    @csrf
                                    <button type="submit">Panggil</button>
                                </form>
                                <form method="POST" action="/admin/queues/${queue.id}/start" onsubmit="const estimate = prompt('Masukkan estimasi pengerjaan (menit):'); if (estimate === null || estimate.trim() === '') { alert('Estimasi pengerjaan wajib diisi.'); return false; } if (!/^\\d+$/.test(estimate.trim()) || Number(estimate) < 1) { alert('Estimasi harus berupa angka menit yang valid.'); return false; } this.querySelector('input[name=service_estimate_minutes]').value = estimate.trim(); return true;">
                                    @csrf
                                    <input type="hidden" name="service_estimate_minutes" value="">
                                    <button type="submit" class="secondary">Proses</button>
                                </form>
                                <form method="POST" action="/admin/queues/${queue.id}/finish" onsubmit="const note = prompt('Masukkan catatan admin untuk pelanggan:'); if (note === null || note.trim() === '') { alert('Catatan admin wajib diisi untuk status selesai.'); return false; } this.querySelector('input[name=admin_note]').value = note; return true;">
                                    @csrf
                                    <input type="hidden" name="admin_note" value="">
                                    <button type="submit">Selesai</button>
                                </form>
                                <form method="POST" action="/admin/queues/${queue.id}/cancel">
                                    @csrf
                                    <button type="submit" class="secondary">Batalkan</button>
                                </form>
                                <form method="POST" action="/admin/queues/${queue.id}" onsubmit="return confirm('Yakin ingin menolak dan menghapus antrian ini secara permanen?');">
                                    @csrf
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="secondary">Tolak/Hapus</button>
                                </form>
                            </td>
                        </tr>
                    `;
                }).join('');
            };

            const fetchLiveDashboard = async () => {
                try {
                    const response = await fetch(`${liveUrl}?date_from=${encodeURIComponent(dateFrom)}&date_to=${encodeURIComponent(dateTo)}`, {
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
                    statTotal.textContent = data.stats.total_today;
                    statWaiting.textContent = data.stats.waiting_today;
                    statDone.textContent = data.stats.done_today;
                    renderRows(data.todayQueues || []);

                    const currentTotal = Number(data.totalToday || 0);
                    if (currentTotal > knownTotalToday) {
                        knownTotalToday = currentTotal;
                        playBellSound();
                    } else {
                        knownTotalToday = currentTotal;
                    }
                } catch (error) {
                    // keep current dashboard view if polling fails
                }
            };
            document.addEventListener('click', unlockAudio, { once: true });
            fetchLiveDashboard();
            setInterval(fetchLiveDashboard, 5000);
        })();
    </script>
@endsection
