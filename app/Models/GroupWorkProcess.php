<?php

namespace App\Models;

class GroupWorkProcess extends WorkProcess
{
    protected $table = 'group_work_processes';
    protected $fillable = ['group_id','number','name','core_task_id','code'];
}
