# ADMS ZKTeco Migration Guide

Dokumen ini menjelaskan cara memigrasikan mekanisme koneksi device fingerprint ZKTeco berbasis ADMS / Push Protocol ke project lain.

Target utamanya:

- Device dari banyak lokasi bisa register otomatis ke server.
- Server bisa tahu device online atau offline.
- Data absensi masuk realtime atau mendekati realtime.
- Implementasi bisa disesuaikan dengan table yang sudah ada di project tujuan.

## Konsep Utama

Pada mode ADMS / Push Protocol, server tidak melakukan scan ke device. Device yang aktif mengirim HTTP request ke server.

Flow dasarnya:

```text
Device fingerprint
  -> GET /iclock/cdata
  -> Server balas konfigurasi handshake
  -> Device mulai push log
  -> POST /iclock/cdata?table=ATTLOG
  -> Server simpan absensi
```

Jadi yang dimasukkan ke menu ADMS device adalah base URL project, misalnya:

```text
https://absensi.example.com
```

Device lalu otomatis memanggil endpoint:

```text
GET /iclock/cdata?SN=DEVICE_SERIAL&options=all&...
POST /iclock/cdata?SN=DEVICE_SERIAL&table=ATTLOG&Stamp=9999
GET /iclock/getrequest?SN=DEVICE_SERIAL
```

## Endpoint Minimal

Project tujuan perlu menyediakan endpoint berikut:

```text
GET  /iclock/cdata
POST /iclock/cdata
GET  /iclock/getrequest
```

Contoh route Laravel:

```php
Route::get('/iclock/cdata', [AdmsController::class, 'handshake']);
Route::post('/iclock/cdata', [AdmsController::class, 'receiveRecords']);
Route::get('/iclock/getrequest', [AdmsController::class, 'getRequest']);
```

Endpoint ini sebaiknya dikecualikan dari CSRF, karena device bukan browser dan tidak punya CSRF token.

## Handshake Device

Saat device pertama connect, request biasanya berbentuk:

```text
GET /iclock/cdata?SN=BOCK200961014&options=all&language=69&pushver=2.4.0&DeviceType=...
```

Server perlu:

1. Ambil serial number dari query `SN`.
2. Simpan raw request untuk debugging.
3. Register atau update device berdasarkan serial number.
4. Update waktu terakhir device terlihat online.
5. Balas response konfigurasi ADMS.

Contoh response handshake:

```text
GET OPTION FROM: BOCK200961014
Stamp=9999
OpStamp=1710000000
ErrorDelay=60
Delay=30
ResLogDay=18250
ResLogDelCount=10000
ResLogCount=50000
TransTimes=00:00;14:05
TransInterval=1
TransFlag=1111000000
Realtime=1
Encrypt=0
```

Arti field penting:

- `Delay=30`: interval device menghubungi server, kira-kira tiap 30 detik.
- `Realtime=1`: device diminta push data realtime.
- `Encrypt=0`: payload tidak dienkripsi.
- `TransTimes`: jadwal transfer berkala.
- `TransFlag`: tipe data yang diminta device kirim.
- `Stamp`: penanda sinkronisasi log.

## Deteksi Online / Offline

Gunakan timestamp terakhir device menghubungi server.

Field yang disarankan:

```text
devices.last_seen_at
```

Setiap handshake atau request dari device:

```php
Device::updateOrCreate(
    ['serial_number' => $sn],
    ['last_seen_at' => now()]
);
```

Status online bisa dihitung dari `last_seen_at`.

Contoh rule:

```php
$isOnline = $device->last_seen_at >= now()->subMinutes(2);
```

Jika `Delay=30`, threshold 2 menit cukup aman untuk menghindari false offline karena jaringan lambat.

## Menerima Absensi

Saat ada data absensi, device akan POST ke:

```text
POST /iclock/cdata?SN=BOCK200961014&table=ATTLOG&Stamp=9999
```

Body biasanya berupa baris tab-separated:

```text
1	2024-07-28 01:25:24	0	1		0	0
4	2024-07-28 10:41:31	0	1		0	0
```

Parser dasar:

```php
$rows = preg_split('/\r\n|\r|\n/', $request->getContent());

foreach ($rows as $row) {
    if (trim($row) === '') {
        continue;
    }

    $columns = explode("\t", $row);

    $employeeId = $columns[0] ?? null;
    $punchTime = $columns[1] ?? null;
    $status1 = $columns[2] ?? null;
    $status2 = $columns[3] ?? null;
    $status3 = $columns[4] ?? null;
    $status4 = $columns[5] ?? null;
    $status5 = $columns[6] ?? null;
}
```

Setelah berhasil simpan, server wajib membalas:

```text
OK: 2
```

Angka setelah `OK:` adalah jumlah record yang berhasil diproses. Response ini penting karena device memakai response tersebut sebagai tanda upload berhasil.

## Operation Log dan Fingerprint Template

Selain `ATTLOG`, device bisa mengirim:

```text
table=OPERLOG
```

Payload `OPERLOG` bisa berisi log operasi device dan fingerprint template mentah, contoh:

```text
OPLOG 6	0	2024-06-13 12:55:41	1	0	0	906
FP PIN=1	FID=0	Size=440	Valid=1	TMP=...
```

Untuk tahap awal, simpan raw payload ke table log dulu. Parsing fingerprint template bisa dibuat belakangan jika project memang butuh sinkronisasi template sidik jari.

Minimal response untuk `OPERLOG`:

```text
OK: jumlah_record
```

## Mapping Table Fleksibel

Saat implementasi di project tujuan, jangan wajib mengikuti nama table dari project referensi. Gunakan mapping ke table yang sudah ada.

Mapping yang perlu ditentukan:

| Kebutuhan ADMS | Field Sumber | Mapping ke Project Tujuan |
| --- | --- | --- |
| Serial device | query `SN` | device serial / kode mesin |
| Status online | waktu request terakhir | `last_seen_at`, `online_at`, atau field sejenis |
| Employee ID dari mesin | kolom 0 payload `ATTLOG` | user pin / employee code / biometric id |
| Waktu absensi | kolom 1 payload `ATTLOG` | attendance time / check time |
| Status mesin | kolom 2-6 payload `ATTLOG` | verify type / punch state / raw status |
| Raw request | query + body | log table untuk debugging |

## Struktur Data yang Disarankan

Jika project tujuan belum punya table, struktur minimalnya:

```text
devices
- id
- serial_number
- name
- location_id
- last_seen_at
- created_at
- updated_at
```

```text
attendance_logs
- id
- device_serial_number
- employee_id
- punch_time
- status1
- status2
- status3
- status4
- status5
- raw_payload
- created_at
- updated_at
```

```text
device_raw_logs
- id
- device_serial_number
- method
- endpoint
- query_payload
- body_payload
- table_name
- created_at
```

## Realtime Dashboard

Ada dua pendekatan:

### Polling

Frontend mengambil data terbaru setiap 5-10 detik.

Cocok untuk implementasi awal karena sederhana.

```text
Device -> Server -> Database -> Frontend polling
```

### WebSocket / Broadcast

Setelah data absensi tersimpan, server broadcast event.

```text
Device -> Server -> Database -> Broadcast Event -> Dashboard
```

Pilihan stack:

- Laravel Reverb
- Pusher
- Soketi
- Socket.IO

Untuk production realtime, pendekatan broadcast lebih ideal.

## Keamanan

Endpoint ADMS tidak bisa memakai auth session biasa. Gunakan proteksi yang kompatibel dengan device:

- Whitelist serial number device.
- Whitelist IP publik lokasi jika memungkinkan.
- Gunakan path khusus, misalnya `/zkteco-adms/iclock/cdata`.
- Simpan raw log semua request.
- Tambahkan rate limit ringan.
- Jangan tampilkan detail error internal ke response device.
- Gunakan HTTPS jika device dan firmware mendukung.

## Checklist Implementasi di Project Tujuan

1. Tentukan table mana yang menyimpan device.
2. Tentukan field unik serial number device.
3. Tentukan field untuk menyimpan waktu online terakhir.
4. Tentukan table absensi tujuan.
5. Tentukan mapping employee ID mesin ke user/pegawai project.
6. Buat endpoint `GET /iclock/cdata`.
7. Buat endpoint `POST /iclock/cdata`.
8. Exclude endpoint ADMS dari CSRF.
9. Simpan raw request untuk debugging.
10. Implementasi handshake response.
11. Implementasi parser `ATTLOG`.
12. Return `OK: jumlah_record` setelah data berhasil diterima.
13. Test pakai Postman.
14. Arahkan satu device fisik ke URL project.
15. Validasi device online/offline.
16. Validasi absensi realtime.
17. Baru rollout ke device lokasi lain.

## Contoh Controller Ringkas

```php
class AdmsController extends Controller
{
    public function handshake(Request $request)
    {
        $sn = $request->input('SN');

        // TODO: sesuaikan dengan model/table device project tujuan.
        Device::updateOrCreate(
            ['serial_number' => $sn],
            ['last_seen_at' => now()]
        );

        // TODO: simpan raw request untuk debugging.

        return "GET OPTION FROM: {$sn}\r\n"
            . "Stamp=9999\r\n"
            . "OpStamp=" . time() . "\r\n"
            . "ErrorDelay=60\r\n"
            . "Delay=30\r\n"
            . "ResLogDay=18250\r\n"
            . "ResLogDelCount=10000\r\n"
            . "ResLogCount=50000\r\n"
            . "TransTimes=00:00;14:05\r\n"
            . "TransInterval=1\r\n"
            . "TransFlag=1111000000\r\n"
            . "Realtime=1\r\n"
            . "Encrypt=0";
    }

    public function receiveRecords(Request $request)
    {
        $sn = $request->input('SN');
        $table = $request->input('table');
        $stamp = $request->input('Stamp');
        $body = $request->getContent();
        $count = 0;

        // TODO: update last_seen_at device.
        // TODO: simpan raw request untuk debugging.

        $rows = preg_split('/\r\n|\r|\n/', $body);

        if ($table === 'OPERLOG') {
            foreach ($rows as $row) {
                if (trim($row) !== '') {
                    $count++;
                }
            }

            return "OK: {$count}";
        }

        foreach ($rows as $row) {
            if (trim($row) === '') {
                continue;
            }

            $columns = explode("\t", $row);

            // TODO: sesuaikan insert dengan table absensi project tujuan.
            AttendanceLog::create([
                'device_serial_number' => $sn,
                'employee_id' => $columns[0] ?? null,
                'punch_time' => $columns[1] ?? null,
                'status1' => $columns[2] ?? null,
                'status2' => $columns[3] ?? null,
                'status3' => $columns[4] ?? null,
                'status4' => $columns[5] ?? null,
                'status5' => $columns[6] ?? null,
                'raw_payload' => $row,
            ]);

            $count++;
        }

        return "OK: {$count}";
    }

    public function getRequest(Request $request)
    {
        return "OK";
    }
}
```

## Catatan Migrasi

Saat dokumen ini dipakai di project lain, langkah pertama adalah membaca schema project tujuan:

- table device
- table pegawai/user
- table absensi
- relasi lokasi/cabang
- format status kehadiran yang sudah dipakai

Setelah itu implementasi ADMS cukup disesuaikan di bagian mapping, bukan mengubah seluruh struktur project.
