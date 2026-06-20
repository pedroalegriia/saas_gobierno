<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MunicipalSaas\Shared\Domain\Enums\CaptureLineStatus;
use MunicipalSaas\Shared\Domain\Enums\PaymentStatus;

final class ExpireCaptureLinesCommand extends Command
{
    protected $signature = 'capture-lines:expire';

    protected $description = 'Expire pending capture lines and associated pending payment references.';

    public function handle(): int
    {
        $expiredIds = DB::table('capture_lines')
            ->where('status', CaptureLineStatus::Pending->value)
            ->whereDate('expiration_date', '<', now()->toDateString())
            ->pluck('id');

        if ($expiredIds->isEmpty()) {
            $this->info('No pending capture lines to expire.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($expiredIds): void {
            DB::table('capture_lines')
                ->whereIn('id', $expiredIds)
                ->update([
                    'status' => CaptureLineStatus::Expired->value,
                    'updated_at' => now(),
                ]);

            DB::table('payments')
                ->whereIn('capture_line_id', $expiredIds)
                ->where('status', PaymentStatus::Pending->value)
                ->update([
                    'status' => PaymentStatus::Failed->value,
                    'updated_at' => now(),
                ]);
        });

        $this->info(sprintf('Expired %d capture lines.', $expiredIds->count()));

        return self::SUCCESS;
    }
}
