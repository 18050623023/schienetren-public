<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class LeaveAttachment extends Model
{
    use \App\Traits\HasDeletableFiles;

    public array $deletableFileColumns = ['file_path'];
    public array $deletableFileDirectories = ['file/leave-attachments'];
    
    protected $fillable = [
        'leave_request_id',
        'file_name',
        'file_path',
        'created_by',
    ];

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
