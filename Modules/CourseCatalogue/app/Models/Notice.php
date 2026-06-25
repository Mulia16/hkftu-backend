<?php

namespace Modules\CourseCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'public.notices';

    protected $fillable = ['title', 'content', 'type', 'is_active', 'published_at'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
