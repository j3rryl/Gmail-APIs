<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreadHistory extends Model
{
    use HasFactory;
    protected $fillable = ["threadId", "historyId", "snippet"];
}
