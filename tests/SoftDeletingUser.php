<?php

namespace Harlekoy\ShareWith\Test;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletingUser extends User
{
    use SoftDeletes;

    protected $guard_name = 'web';
}
