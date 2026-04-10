<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicQueueController extends Controller
{
    public function create(): View
    {
        return view('public.queue-form');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'regex:/^[0-9]{8,25}$/'],
            'device_type' => ['required', 'string', 'max:100'],
            'complaint' => ['required', 'string', 'max:2000'],
        ], [
            'customer_phone.required' => 'Nomor telepon wajib diisi.',
            'customer_phone.regex' => 'Nomor telepon hanya boleh angka (8-25 digit).',
        ]);

        $queue = DB::transaction(function () use ($validated) {
            $today = Carbon::today()->toDateString();

            $lastSequence = Queue::query()
                ->whereDate('queue_date', $today)
                ->lockForUpdate()
                ->max('sequence_no');

            $nextSequence = ((int) $lastSequence) + 1;
            $queueNumber = sprintf('SV-%02d', $nextSequence);

            return Queue::create([
                'queue_date' => $today,
                'sequence_no' => $nextSequence,
                'queue_number' => $queueNumber,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'device_type' => $validated['device_type'],
                'complaint' => $validated['complaint'],
                'status' => 'waiting',
            ]);
        });

        return redirect()->route('queue.success', $queue);
    }

    public function success(Queue $queue): View
    {
        return view('public.queue-success', compact('queue'));
    }

    public function board(): JsonResponse
    {
        $today = Carbon::today()->toDateString();
        $now = now();

        $inServiceQueues = Queue::query()
            ->whereDate('queue_date', $today)
            ->where('status', 'in_service')
            ->orderByDesc('service_started_at')
            ->orderBy('sequence_no')
            ->get();

        $inServiceNumbers = $inServiceQueues
            ->pluck('queue_number')
            ->values();

        $inServiceEntries = $inServiceQueues
            ->map(function (Queue $item) use ($now) {
                $remainingSeconds = null;

                if ($item->service_started_at && $item->service_estimate_minutes) {
                    $etaAt = $item->service_started_at->copy()->addMinutes((int) $item->service_estimate_minutes);
                    $remainingSeconds = max(0, (int) floor($now->diffInSeconds($etaAt, false)));
                }

                return [
                    'queue_number' => $item->queue_number,
                    'service_estimate_minutes' => $item->service_estimate_minutes,
                    'remaining_seconds' => $remainingSeconds,
                ];
            })
            ->values();

        $calledQueues = Queue::query()
            ->whereDate('queue_date', $today)
            ->where('status', 'called')
            ->orderByDesc('called_at')
            ->orderBy('sequence_no')
            ->get();

        $calledNumbers = $calledQueues
            ->pluck('queue_number')
            ->values();

        $nextWaiting = Queue::query()
            ->whereDate('queue_date', $today)
            ->where('status', 'waiting')
            ->orderBy('sequence_no')
            ->first();

        $waitingCount = Queue::query()
            ->whereDate('queue_date', $today)
            ->where('status', 'waiting')
            ->count();

        return response()->json([
            'in_service' => $inServiceNumbers->first(),
            'in_service_list' => $inServiceNumbers,
            'in_service_count' => $inServiceNumbers->count(),
            'in_service_entries' => $inServiceEntries,
            'called' => $calledNumbers->first(),
            'called_list' => $calledNumbers,
            'called_count' => $calledNumbers->count(),
            'next_waiting' => $nextWaiting?->queue_number,
            'waiting_count' => $waitingCount,
            'server_now' => $now->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function ticketStatus(Queue $queue): JsonResponse
    {
        $statusMap = [
            'waiting' => ['label' => 'Menunggu', 'class' => 'b-waiting'],
            'called' => ['label' => 'Dipanggil', 'class' => 'b-called'],
            'in_service' => ['label' => 'Diproses', 'class' => 'b-in-service'],
            'done' => ['label' => 'Selesai', 'class' => 'b-done'],
            'cancelled' => ['label' => 'Dibatalkan', 'class' => 'b-cancelled'],
        ];

        $mapped = $statusMap[$queue->status] ?? ['label' => ucfirst($queue->status), 'class' => 'b-cancelled'];

        return response()->json([
            'queue_number' => $queue->queue_number,
            'status' => $queue->status,
            'status_label' => $mapped['label'],
            'status_class' => $mapped['class'],
            'service_estimate_minutes' => $queue->service_estimate_minutes,
            'service_estimate_text' => $queue->service_estimate_minutes
                ? $queue->service_estimate_minutes.' menit'
                : null,
            'updated_at' => optional($queue->updated_at)?->toIso8601String(),
        ]);
    }
}
