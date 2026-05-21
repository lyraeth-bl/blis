<?php

namespace App\Http\Controllers;

use App\Services\QrScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class QrAttendanceScanController extends Controller
{
    public function __invoke(Request $request, QrScanService $qrScanService): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
        ]);

        try {
            $result = $qrScanService->process($validated['token']);

            return response()->json([
                'message' => 'Absensi berhasil dicatat.',
                'data' => [
                    'nis' => $result['student']->nis,
                    'nama' => $result['student']->name,
                    'unit' => $result['student']->unit,
                    'kelas' => $result['student']->class,
                    'tipe' => $result['tipe'] === 'checkIn' ? 'Check In' : 'Check Out',
                    'waktu' => now()->format('H:i:s'),
                    'status' => match ($result['status']) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        default => $result['status'],
                    },
                    // Untuk audit trail — piket bisa lihat mode QR yang dipakai
                    'qr_mode' => $result['qr_mode'] === 'dynamic' ? 'HP (Dynamic)' : 'Kartu Pelajar (Static)',
                ],
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Scan gagal diproses.',
            ], 500);
        }
    }
}
