<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminQueueController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->query('date_from', Carbon::today()->toDateString());
        $dateTo = $request->query('date_to', Carbon::today()->toDateString());

        $payload = $this->buildDashboardPayload($dateFrom, $dateTo);

        return view('admin.dashboard', $payload + compact('dateFrom', 'dateTo'));
    }

    public function live(Request $request)
    {
        $dateFrom = $request->query('date_from', Carbon::today()->toDateString());
        $dateTo = $request->query('date_to', Carbon::today()->toDateString());

        return response()->json($this->buildDashboardPayload($dateFrom, $dateTo));
    }

    public function callNext(): RedirectResponse
    {
        $today = Carbon::today()->toDateString();

        $queue = Queue::query()
            ->whereDate('queue_date', $today)
            ->where('status', 'waiting')
            ->orderBy('sequence_no')
            ->first();

        if (! $queue) {
            return back()->with('error', 'Tidak ada antrian menunggu untuk dipanggil.');
        }

        $justCalled = $this->transitionToCalled($queue);

        if ($justCalled) {
            $this->sendWhatsappCallNotification($queue);
        }

        return back()->with('success', 'Antrian '.$queue->queue_number.' dipanggil.');
    }

    public function call(Queue $queue): RedirectResponse
    {
        if (! in_array($queue->status, ['waiting', 'called'], true)) {
            return back()->with('error', 'Antrian tidak bisa dipanggil dari status saat ini.');
        }

        $justCalled = $this->transitionToCalled($queue);

        if ($justCalled) {
            $this->sendWhatsappCallNotification($queue);
        }

        return back()->with('success', 'Antrian '.$queue->queue_number.' dipanggil.');
    }

    public function start(Request $request, Queue $queue): RedirectResponse
    {
        $validated = $request->validate([
            'service_estimate_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);

        if ($queue->status === 'waiting') {
            $justCalled = $this->transitionToCalled($queue);

            if ($justCalled) {
                $this->sendWhatsappCallNotification($queue);
            }

            $queue->refresh();
        }

        if (! in_array($queue->status, ['called', 'in_service'], true)) {
            return back()->with('error', 'Antrian tidak bisa diproses dari status saat ini.');
        }

        $queue->update([
            'status' => 'in_service',
            'service_started_at' => $queue->service_started_at ?? now(),
            'service_estimate_minutes' => $validated['service_estimate_minutes'],
        ]);

        $this->sendWhatsappInServiceNotification($queue, (int) $validated['service_estimate_minutes']);

        return back()->with('success', 'Antrian '.$queue->queue_number.' sedang diproses.');
    }

    public function finish(Request $request, Queue $queue): RedirectResponse
    {
        if (! in_array($queue->status, ['called', 'in_service'], true)) {
            return back()->with('error', 'Antrian tidak bisa diselesaikan dari status saat ini.');
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        $queue->update([
            'status' => 'done',
            'service_started_at' => $queue->service_started_at ?? now(),
            'service_finished_at' => now(),
            'admin_note' => $validated['admin_note'],
        ]);

        $this->sendWhatsappDoneNotification($queue, $validated['admin_note']);

        return back()->with('success', 'Antrian '.$queue->queue_number.' selesai.');
    }

    public function cancel(Queue $queue): RedirectResponse
    {
        if (in_array($queue->status, ['done', 'cancelled'], true)) {
            return back()->with('error', 'Antrian tidak bisa dibatalkan.');
        }

        $queue->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Antrian '.$queue->queue_number.' dibatalkan.');
    }

    public function destroy(Queue $queue): RedirectResponse
    {
        $queueNumber = $queue->queue_number;
        $queue->delete();

        return back()->with('success', 'Antrian '.$queueNumber.' ditolak dan dihapus permanen.');
    }

    private function transitionToCalled(Queue $queue): bool
    {
        return DB::transaction(function () use ($queue) {
            $queue->refresh();

            if (in_array($queue->status, ['done', 'cancelled'], true)) {
                return false;
            }

            if ($queue->status !== 'waiting') {
                return false;
            }

            $queue->update([
                'status' => 'called',
                'called_at' => $queue->called_at ?? now(),
            ]);

            return true;
        });
    }

    private function buildDashboardPayload(string $dateFrom, string $dateTo): array
    {
        $today = Carbon::today()->toDateString();

        $todayQueues = Queue::query()
            ->whereDate('queue_date', $today)
            ->orderBy('sequence_no')
            ->get();

        $historyQueues = Queue::query()
            ->whereBetween('queue_date', [$dateFrom, $dateTo])
            ->orderByDesc('queue_date')
            ->orderBy('sequence_no')
            ->limit(200)
            ->get();

        $stats = [
            'total_today' => Queue::query()->whereDate('queue_date', $today)->count(),
            'waiting_today' => Queue::query()->whereDate('queue_date', $today)->where('status', 'waiting')->count(),
            'done_today' => Queue::query()->whereDate('queue_date', $today)->where('status', 'done')->count(),
        ];

        $latestQueue = Queue::query()
            ->whereDate('queue_date', $today)
            ->orderByDesc('sequence_no')
            ->first();

        return [
            'todayQueues' => $todayQueues,
            'historyQueues' => $historyQueues,
            'stats' => $stats,
            'latestQueueId' => $latestQueue?->id,
            'latestQueueNumber' => $latestQueue?->queue_number,
            'totalToday' => $stats['total_today'],
        ];
    }

    private function sendWhatsappCallNotification(Queue $queue): void
    {
        if (! config('services.whatsapp.enabled')) {
            return;
        }

        $token = (string) config('services.whatsapp.fonnte_token');

        if ($token === '') {
            Log::warning('WhatsApp notification skipped: token not configured.');
            return;
        }

        $target = $this->normalizePhoneNumber($queue->customer_phone);

        if ($target === null) {
            Log::warning('WhatsApp notification skipped: invalid customer phone.', [
                'queue_id' => $queue->id,
                'customer_phone' => $queue->customer_phone,
            ]);
            return;
        }

        $message = sprintf(
            "Halo %s, nomor antrian Anda %s sedang DIPANGGIL sekarang di Service Center Infinix Roxy Mas. Dimohon untuk segera mendatangi ruangan service center dan membawa perangkat yang akan diperbaiki.",
            $queue->customer_name,
            $queue->queue_number
        );

        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $token])
                ->withoutVerifying()
                ->timeout(10)
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp notification failed.', [
                    'queue_id' => $queue->id,
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('WhatsApp notification error.', [
                'queue_id' => $queue->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendWhatsappDoneNotification(Queue $queue, string $adminNote): void
    {
        if (! config('services.whatsapp.enabled')) {
            return;
        }

        $token = (string) config('services.whatsapp.fonnte_token');

        if ($token === '') {
            Log::warning('WhatsApp done notification skipped: token not configured.');
            return;
        }

        $target = $this->normalizePhoneNumber($queue->customer_phone);

        if ($target === null) {
            Log::warning('WhatsApp done notification skipped: invalid customer phone.', [
                'queue_id' => $queue->id,
                'customer_phone' => $queue->customer_phone,
            ]);
            return;
        }

        $message = sprintf(
            "Halo %s, layanan untuk nomor antrian %s sudah SELESAI. Catatan admin: %s",
            $queue->customer_name,
            $queue->queue_number,
            $adminNote
        );

        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $token])
                ->withoutVerifying()
                ->timeout(10)
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp done notification failed.', [
                    'queue_id' => $queue->id,
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('WhatsApp done notification error.', [
                'queue_id' => $queue->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendWhatsappInServiceNotification(Queue $queue, int $estimateMinutes): void
    {
        if (! config('services.whatsapp.enabled')) {
            return;
        }

        $token = (string) config('services.whatsapp.fonnte_token');

        if ($token === '') {
            Log::warning('WhatsApp in-service notification skipped: token not configured.');
            return;
        }

        $target = $this->normalizePhoneNumber($queue->customer_phone);

        if ($target === null) {
            Log::warning('WhatsApp in-service notification skipped: invalid customer phone.', [
                'queue_id' => $queue->id,
                'customer_phone' => $queue->customer_phone,
            ]);
            return;
        }

        $message = sprintf(
            'Halo %s, antrian %s saat ini sedang DIPROSES. Estimasi pengerjaan: %s.',
            $queue->customer_name,
            $queue->queue_number,
            $this->formatEstimateMinutes($estimateMinutes)
        );

        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $token])
                ->withoutVerifying()
                ->timeout(10)
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp in-service notification failed.', [
                    'queue_id' => $queue->id,
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('WhatsApp in-service notification error.', [
                'queue_id' => $queue->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function formatEstimateMinutes(int $estimateMinutes): string
    {
        if ($estimateMinutes < 60) {
            return $estimateMinutes.' menit';
        }

        $hours = intdiv($estimateMinutes, 60);
        $minutes = $estimateMinutes % 60;

        if ($minutes === 0) {
            return $hours.' jam';
        }

        return $hours.' jam '.$minutes.' menit';
    }

    private function normalizePhoneNumber(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        if (! str_starts_with($digits, '62')) {
            return null;
        }

        return $digits;
    }
}
