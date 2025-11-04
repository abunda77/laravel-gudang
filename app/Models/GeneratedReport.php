<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GeneratedReport extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'report_name',
        'file_path',
        'report_month',
        'status',
    ];

    protected $casts = [
        'report_month' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileSizeAttribute(): string
    {
        if (Storage::exists($this->file_path)) {
            $bytes = Storage::size($this->file_path);
            $units = ['B', 'KB', 'MB', 'GB'];
            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            return round($bytes, 2) . ' ' . $units[$i];
        }
        return 'N/A';
    }

    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }
        return true;
    }
}
