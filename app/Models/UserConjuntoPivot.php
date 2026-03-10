<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserConjuntoPivot extends Pivot
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_conjunto';
}
