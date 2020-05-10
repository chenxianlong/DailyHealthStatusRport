<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAllowExportDepartment extends Model
{
    use CompositeKeyModel;

    protected $primaryKey = ['user_id', 'department'];

    protected $guarded = [];
}
