<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\FingerprintDevice;
use App\Models\FingerprintDeviceCommand;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AdmsCommandService
{
    public function queueUpdateUser(FingerprintDevice $device, Model $attendable, ?User $requestedBy = null): FingerprintDeviceCommand
    {
        $this->ensureDeviceCanManageAttendable($device, $attendable);

        $pin = $this->pinFor($attendable);
        $name = $this->sanitizeField((string) $attendable->getAttribute('name'));

        return $this->createCommand(
            device: $device,
            action: FingerprintDeviceCommand::ACTION_UPDATE_USER,
            command: implode(' ', [
                'DATA',
                'UPDATE',
                'USERINFO',
                implode("\t", [
                    "PIN={$pin}",
                    "Name={$name}",
                    'Pri=0',
                    'Passwd=',
                    'Card=0',
                    'Grp=1',
                    'TZ=0000000000000000',
                    'Verify=-1',
                ]),
            ]),
            attendable: $attendable,
            requestedBy: $requestedBy,
            payload: [
                'pin' => $pin,
                'name' => $name,
            ],
        );
    }

    public function queueDeleteUser(FingerprintDevice $device, Model $attendable, ?User $requestedBy = null): FingerprintDeviceCommand
    {
        $this->ensureDeviceCanManageAttendable($device, $attendable);

        $pin = $this->pinFor($attendable);

        return $this->createCommand(
            device: $device,
            action: FingerprintDeviceCommand::ACTION_DELETE_USER,
            command: "DATA DELETE USERINFO PIN={$pin}",
            attendable: $attendable,
            requestedBy: $requestedBy,
            payload: ['pin' => $pin],
        );
    }

    public function queueQueryUser(FingerprintDevice $device, Model $attendable, ?User $requestedBy = null): FingerprintDeviceCommand
    {
        $this->ensureDeviceCanManageAttendable($device, $attendable);

        $pin = $this->pinFor($attendable);

        return $this->createCommand(
            device: $device,
            action: FingerprintDeviceCommand::ACTION_QUERY_USER,
            command: "DATA QUERY USERINFO PIN={$pin}",
            attendable: $attendable,
            requestedBy: $requestedBy,
            payload: ['pin' => $pin],
        );
    }

    public function queueQueryUsers(FingerprintDevice $device, ?User $requestedBy = null): FingerprintDeviceCommand
    {
        return $this->createCommand(
            device: $device,
            action: FingerprintDeviceCommand::ACTION_QUERY_USERS,
            command: 'DATA QUERY USERINFO',
            requestedBy: $requestedBy,
            payload: ['all' => true],
        );
    }

    public function queueDeleteFingerprintTemplate(
        FingerprintDevice $device,
        Model $attendable,
        ?int $fingerId = null,
        ?User $requestedBy = null,
    ): FingerprintDeviceCommand {
        $this->ensureDeviceCanManageAttendable($device, $attendable);

        if ($fingerId !== null && $fingerId < 0) {
            throw new InvalidArgumentException('FID fingerprint tidak valid.');
        }

        $pin = $this->pinFor($attendable);
        $fields = ["PIN={$pin}"];

        if ($fingerId !== null) {
            $fields[] = "FID={$fingerId}";
        }

        return $this->createCommand(
            device: $device,
            action: FingerprintDeviceCommand::ACTION_DELETE_FINGERPRINT_TEMPLATE,
            command: implode(' ', [
                'DATA',
                'DELETE',
                'FINGERTMP',
                implode("\t", $fields),
            ]),
            attendable: $attendable,
            requestedBy: $requestedBy,
            payload: [
                'pin' => $pin,
                'fid' => $fingerId,
            ],
        );
    }

    public function queueUpdateFingerprintTemplate(
        FingerprintDevice $device,
        Model $attendable,
        int $fingerId,
        string $template,
        bool $valid = true,
        ?User $requestedBy = null,
    ): FingerprintDeviceCommand {
        $this->ensureDeviceCanManageAttendable($device, $attendable);

        if ($fingerId < 0) {
            throw new InvalidArgumentException('FID fingerprint tidak valid.');
        }

        $pin = $this->pinFor($attendable);
        $template = $this->normalizeBase64Template($template);
        $decodedTemplate = base64_decode($template, true);

        if ($decodedTemplate === false || $decodedTemplate === '') {
            throw new InvalidArgumentException('TMP fingerprint harus berupa base64 yang valid.');
        }

        $size = strlen($decodedTemplate);

        return $this->createCommand(
            device: $device,
            action: FingerprintDeviceCommand::ACTION_UPDATE_FINGERPRINT_TEMPLATE,
            command: implode(' ', [
                'DATA',
                'UPDATE',
                'FINGERTMP',
                implode("\t", [
                    "PIN={$pin}",
                    "FID={$fingerId}",
                    "Size={$size}",
                    'Valid='.($valid ? '1' : '0'),
                    "TMP={$template}",
                ]),
            ]),
            attendable: $attendable,
            requestedBy: $requestedBy,
            payload: [
                'pin' => $pin,
                'fid' => $fingerId,
                'size' => $size,
                'valid' => $valid,
                'tmp_sha256' => hash('sha256', $decodedTemplate),
            ],
        );
    }

    public function queueQueryFingerprintTemplate(
        FingerprintDevice $device,
        Model $attendable,
        ?int $fingerId = null,
        ?User $requestedBy = null,
    ): FingerprintDeviceCommand {
        $this->ensureDeviceCanManageAttendable($device, $attendable);

        if ($fingerId !== null && $fingerId < 0) {
            throw new InvalidArgumentException('FingerID fingerprint tidak valid.');
        }

        $pin = $this->pinFor($attendable);
        $fields = ["PIN={$pin}"];

        if ($fingerId !== null) {
            $fields[] = "FingerID={$fingerId}";
        }

        return $this->createCommand(
            device: $device,
            action: FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE,
            command: implode(' ', [
                'DATA',
                'QUERY',
                'FINGERTMP',
                implode("\t", $fields),
            ]),
            attendable: $attendable,
            requestedBy: $requestedBy,
            payload: [
                'pin' => $pin,
                'finger_id' => $fingerId,
            ],
        );
    }

    public function nextCommandFor(FingerprintDevice $device): ?FingerprintDeviceCommand
    {
        return $device->commands()
            ->whereIn('status', [
                FingerprintDeviceCommand::STATUS_PENDING,
                FingerprintDeviceCommand::STATUS_SENT,
            ])
            ->where(function ($query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at')
            ->first();
    }

    /**
     * @param  array{ID?: string, Return?: string, CMD?: string}  $reply
     */
    public function recordReply(FingerprintDevice $device, array $reply, string $rawReply): ?FingerprintDeviceCommand
    {
        $commandId = $this->replyValue($reply, $rawReply, 'ID');

        if ($commandId === '') {
            return null;
        }

        $command = FingerprintDeviceCommand::query()
            ->whereBelongsTo($device)
            ->where('command_id', $commandId)
            ->first();

        if (! $command) {
            return null;
        }

        $returnCode = $this->replyValue($reply, $rawReply, 'Return');
        $succeeded = $returnCode === '0';
        $replyPayload = $this->parseReplyPayload($rawReply);
        $comparison = $this->compareReplyPayload($command, $returnCode, $replyPayload);
        $hasComparablePayload = $this->hasComparablePayload($command, $replyPayload);
        $storedReplyPayload = $this->storedReplyPayload($command, $replyPayload, $hasComparablePayload);

        $command->update([
            'status' => $succeeded
                ? FingerprintDeviceCommand::STATUS_SUCCEEDED
                : FingerprintDeviceCommand::STATUS_FAILED,
            'return_code' => $returnCode === '' ? null : $returnCode,
            'raw_reply' => $rawReply,
            'reply_payload' => $storedReplyPayload,
            'comparison_status' => $hasComparablePayload || $command->comparison_status === null
                ? $comparison['status']
                : $command->comparison_status,
            'comparison_details' => $hasComparablePayload || $command->comparison_details === null
                ? $comparison['details']
                : $command->comparison_details,
            'error_message' => $succeeded ? null : $this->errorMessageFor($returnCode),
            'replied_at' => now(),
        ]);

        if ($succeeded) {
            $this->applySuccessfulCommandResult($command);
        }

        return $command->refresh();
    }

    public function recordQueryFingerprintTemplatePayload(FingerprintDevice $device, string $rawPayload): ?FingerprintDeviceCommand
    {
        $replyPayload = $this->parseReplyPayload($rawPayload);
        $pin = trim((string) ($replyPayload['PIN'] ?? ''));

        if ($pin === '') {
            return null;
        }

        $command = FingerprintDeviceCommand::query()
            ->whereBelongsTo($device)
            ->where('action', FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE)
            ->whereIn('status', [
                FingerprintDeviceCommand::STATUS_PENDING,
                FingerprintDeviceCommand::STATUS_SENT,
                FingerprintDeviceCommand::STATUS_SUCCEEDED,
            ])
            ->where('payload->pin', $pin)
            ->latest()
            ->get()
            ->first(fn (FingerprintDeviceCommand $command): bool => $this->fingerprintReplyMatchesCommand($command, $replyPayload));

        if (! $command) {
            return null;
        }

        $command->update([
            'reply_payload' => $this->mergeFingerprintTemplatePayload($command, $replyPayload),
            'comparison_status' => FingerprintDeviceCommand::COMPARISON_SYNCED,
            'comparison_details' => null,
        ]);

        return $command->refresh();
    }

    public function recordQueryUserPayload(FingerprintDevice $device, string $rawPayload): ?FingerprintDeviceCommand
    {
        $replyPayload = $this->parseReplyPayload($rawPayload);
        $pin = trim((string) ($replyPayload['PIN'] ?? ''));

        if ($pin === '') {
            return null;
        }

        $command = FingerprintDeviceCommand::query()
            ->whereBelongsTo($device)
            ->where('action', FingerprintDeviceCommand::ACTION_QUERY_USER)
            ->whereIn('status', [
                FingerprintDeviceCommand::STATUS_PENDING,
                FingerprintDeviceCommand::STATUS_SENT,
                FingerprintDeviceCommand::STATUS_SUCCEEDED,
            ])
            ->where('payload->pin', $pin)
            ->latest()
            ->first();

        if (! $command) {
            $command = FingerprintDeviceCommand::query()
                ->whereBelongsTo($device)
                ->where('action', FingerprintDeviceCommand::ACTION_QUERY_USERS)
                ->whereIn('status', [
                    FingerprintDeviceCommand::STATUS_PENDING,
                    FingerprintDeviceCommand::STATUS_SENT,
                    FingerprintDeviceCommand::STATUS_SUCCEEDED,
                ])
                ->where('payload->all', true)
                ->latest()
                ->first();
        }

        if (! $command) {
            return null;
        }

        $comparison = $this->compareReplyPayload($command, '0', $replyPayload);

        $command->update([
            'reply_payload' => $command->action === FingerprintDeviceCommand::ACTION_QUERY_USERS
                ? $this->mergeQueryUsersPayload($command, $replyPayload)
                : $replyPayload,
            'comparison_status' => $comparison['status'],
            'comparison_details' => $comparison['details'],
        ]);

        return $command->refresh();
    }

    private function replyValue(array $reply, string $rawReply, string $key): string
    {
        $value = trim((string) ($reply[$key] ?? ''));

        if ($value !== '' && ! str_contains($value, "\t") && ! str_contains($value, "\n")) {
            return $value;
        }

        if (preg_match('/(?:^|[\t&\r\n ])'.preg_quote($key, '/').'=([^\t&\r\n ]*)/', $rawReply, $matches) === 1) {
            return trim($matches[1]);
        }

        return $value;
    }

    private function createCommand(
        FingerprintDevice $device,
        string $action,
        string $command,
        ?Model $attendable = null,
        ?User $requestedBy = null,
        ?array $payload = null,
    ): FingerprintDeviceCommand {
        if (blank($device->serial_number)) {
            throw new InvalidArgumentException('Serial ADMS device belum diisi.');
        }

        return FingerprintDeviceCommand::create([
            'fingerprint_device_id' => $device->id,
            'attendable_type' => $attendable?->getMorphClass(),
            'attendable_id' => $attendable?->getKey(),
            'requested_by_user_id' => $requestedBy?->id,
            'command_id' => $this->makeCommandId(),
            'action' => $action,
            'command' => $command,
            'payload' => $payload,
            'status' => FingerprintDeviceCommand::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    private function makeCommandId(): string
    {
        do {
            $commandId = Str::upper(Str::random(12));
        } while (FingerprintDeviceCommand::where('command_id', $commandId)->exists());

        return $commandId;
    }

    private function ensureDeviceCanManageAttendable(FingerprintDevice $device, Model $attendable): void
    {
        $expectedType = match ($attendable::class) {
            Student::class => 'student',
            Employee::class => 'employee',
            default => throw new InvalidArgumentException('Tipe user tidak didukung untuk command ADMS.'),
        };

        if ($device->type !== $expectedType) {
            throw new InvalidArgumentException('Tipe device tidak cocok dengan tipe user.');
        }

        if (! $device->supportsUnit($attendable->getAttribute('unit_id'))) {
            throw new InvalidArgumentException('Unit user tidak terhubung ke device ini.');
        }
    }

    private function pinFor(Model $attendable): string
    {
        $pin = (string) $attendable->getAttribute('pin');

        if ($pin === '') {
            throw new InvalidArgumentException('PIN user kosong.');
        }

        return $this->sanitizeField($pin);
    }

    private function sanitizeField(string $value): string
    {
        return str_replace(["\r", "\n", "\t"], ' ', $value);
    }

    private function normalizeBase64Template(string $template): string
    {
        return preg_replace('/\s+/', '', $template) ?? '';
    }

    private function applySuccessfulCommandResult(FingerprintDeviceCommand $command): void
    {
        $attendable = $command->attendable;

        if (! $attendable instanceof Model) {
            return;
        }

        $relation = match ($attendable::class) {
            Student::class => 'students',
            Employee::class => 'employees',
            default => null,
        };

        if ($relation === null) {
            return;
        }

        if ($command->action === FingerprintDeviceCommand::ACTION_UPDATE_USER) {
            $command->fingerprintDevice->{$relation}()->syncWithoutDetaching([
                $attendable->getKey() => ['pushed_at' => now()],
            ]);
        }

        if ($command->action === FingerprintDeviceCommand::ACTION_DELETE_USER) {
            $command->fingerprintDevice->{$relation}()->detach($attendable->getKey());
        }
    }

    /**
     * @return array<string, string>
     */
    private function parseReplyPayload(string $rawReply): array
    {
        $payload = [];

        $queryStringPayload = [];

        if (str_contains($rawReply, '&') || preg_match('/^[A-Za-z][A-Za-z0-9_]*=/', $rawReply) === 1) {
            parse_str($rawReply, $queryStringPayload);
        }

        foreach ($queryStringPayload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = $value;
            }
        }

        preg_match_all('/(?:^|[\t&\r\n ])([A-Za-z][A-Za-z0-9_]*)=([^\t&\r\n]*)/', $rawReply, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $payload[$match[1]] = trim($match[2]);
        }

        return $payload;
    }

    /**
     * @param  array<string, string>  $replyPayload
     * @return array{status: string|null, details: array<string, array{web: ?string, device: ?string}>|null}
     */
    private function compareReplyPayload(FingerprintDeviceCommand $command, string $returnCode, array $replyPayload): array
    {
        if ($command->action === FingerprintDeviceCommand::ACTION_QUERY_USERS) {
            return [
                'status' => match (true) {
                    $returnCode !== '0' => FingerprintDeviceCommand::COMPARISON_MISSING,
                    empty($replyPayload) => FingerprintDeviceCommand::COMPARISON_UNKNOWN,
                    default => FingerprintDeviceCommand::COMPARISON_SYNCED,
                },
                'details' => null,
            ];
        }

        if ($command->action !== FingerprintDeviceCommand::ACTION_QUERY_USER) {
            if ($command->action !== FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE) {
                return ['status' => null, 'details' => null];
            }

            if ($returnCode !== '0') {
                return [
                    'status' => FingerprintDeviceCommand::COMPARISON_MISSING,
                    'details' => null,
                ];
            }

            return [
                'status' => $this->hasFingerprintTemplatePayload($replyPayload)
                    ? FingerprintDeviceCommand::COMPARISON_SYNCED
                    : FingerprintDeviceCommand::COMPARISON_UNKNOWN,
                'details' => null,
            ];
        }

        if ($returnCode !== '0') {
            return [
                'status' => FingerprintDeviceCommand::COMPARISON_MISSING,
                'details' => null,
            ];
        }

        $attendable = $command->attendable;

        if (! $attendable instanceof Model) {
            return [
                'status' => FingerprintDeviceCommand::COMPARISON_UNKNOWN,
                'details' => null,
            ];
        }

        $checks = [
            'PIN' => $this->pinFor($attendable),
            'Name' => $this->sanitizeField((string) $attendable->getAttribute('name')),
        ];

        $details = [];

        foreach ($checks as $field => $webValue) {
            $deviceValue = $replyPayload[$field] ?? null;

            if ($deviceValue === null || $deviceValue === '') {
                continue;
            }

            if ($deviceValue !== $webValue) {
                $details[$field] = [
                    'web' => $webValue,
                    'device' => $deviceValue,
                ];
            }
        }

        if (empty($replyPayload) || (! array_key_exists('PIN', $replyPayload) && ! array_key_exists('Name', $replyPayload))) {
            return [
                'status' => FingerprintDeviceCommand::COMPARISON_UNKNOWN,
                'details' => null,
            ];
        }

        return [
            'status' => empty($details)
                ? FingerprintDeviceCommand::COMPARISON_SYNCED
                : FingerprintDeviceCommand::COMPARISON_DIFFERENT,
            'details' => empty($details) ? null : $details,
        ];
    }

    /**
     * @param  array<string, string>  $replyPayload
     */
    private function hasComparablePayload(FingerprintDeviceCommand $command, array $replyPayload): bool
    {
        return match ($command->action) {
            FingerprintDeviceCommand::ACTION_QUERY_USER => $this->hasQueryUserPayload($replyPayload),
            FingerprintDeviceCommand::ACTION_QUERY_USERS => $this->hasQueryUserPayload($replyPayload),
            FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE => $this->hasFingerprintTemplatePayload($replyPayload),
            default => false,
        };
    }

    /**
     * @param  array<string, string>  $replyPayload
     */
    private function hasQueryUserPayload(array $replyPayload): bool
    {
        return array_key_exists('PIN', $replyPayload) || array_key_exists('Name', $replyPayload);
    }

    /**
     * @param  array<string, string>  $replyPayload
     */
    private function hasFingerprintTemplatePayload(array $replyPayload): bool
    {
        return array_key_exists('PIN', $replyPayload)
            && (array_key_exists('FID', $replyPayload) || array_key_exists('TMP', $replyPayload));
    }

    /**
     * @param  array<string, string>  $replyPayload
     */
    private function fingerprintReplyMatchesCommand(FingerprintDeviceCommand $command, array $replyPayload): bool
    {
        $fingerId = $command->payload['finger_id'] ?? null;

        return $fingerId === null
            || ! array_key_exists('FID', $replyPayload)
            || (string) $fingerId === (string) $replyPayload['FID'];
    }

    /**
     * @param  array<string, string>  $replyPayload
     * @return array<string, mixed>
     */
    private function mergeFingerprintTemplatePayload(FingerprintDeviceCommand $command, array $replyPayload): array
    {
        if (($command->payload['finger_id'] ?? null) !== null) {
            return $replyPayload;
        }

        $fingerId = (string) ($replyPayload['FID'] ?? count($command->reply_payload['templates'] ?? []));
        $payload = $command->reply_payload ?? [];
        $payload['PIN'] = $replyPayload['PIN'];
        $payload['templates'][$fingerId] = $replyPayload;

        return $payload;
    }

    /**
     * @param  array<string, string>  $replyPayload
     * @return array<string, mixed>|null
     */
    private function storedReplyPayload(FingerprintDeviceCommand $command, array $replyPayload, bool $hasComparablePayload): ?array
    {
        if ($command->action === FingerprintDeviceCommand::ACTION_QUERY_USERS && filled($replyPayload['PIN'] ?? null)) {
            return $this->mergeQueryUsersPayload($command, $replyPayload);
        }

        if (! $hasComparablePayload && ! empty($command->reply_payload)) {
            return $command->reply_payload;
        }

        return empty($replyPayload) ? null : $replyPayload;
    }

    /**
     * @param  array<string, string>  $replyPayload
     * @return array<string, mixed>
     */
    private function mergeQueryUsersPayload(FingerprintDeviceCommand $command, array $replyPayload): array
    {
        $pin = (string) $replyPayload['PIN'];
        $payload = $command->reply_payload ?? [];
        $payload['users'][$pin] = $replyPayload;

        return $payload;
    }

    private function errorMessageFor(string $returnCode): ?string
    {
        return match ($returnCode) {
            '' => 'Device tidak mengirim return code.',
            '-1' => 'Parameter command salah.',
            '-2' => 'Command tidak didukung device.',
            '-3' => 'Akses ditolak device.',
            default => "Device mengembalikan kode {$returnCode}.",
        };
    }
}
