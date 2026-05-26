<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class P5Assessment extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['p5_project_id','student_id','dimensi_1','dimensi_2','dimensi_3','dimensi_4','dimensi_5','dimensi_6','catatan_proses','created_by'];
    public function p5Project(): BelongsTo { return $this->belongsTo(P5Project::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
}
