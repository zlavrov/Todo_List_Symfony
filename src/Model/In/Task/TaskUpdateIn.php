<?php

namespace App\Model\In\Task;

use App\Model\In\Task\TaskAbstractIn;

class TaskUpdateIn extends TaskAbstractIn {

    public $updatedAt;

    public $completedAt;
}