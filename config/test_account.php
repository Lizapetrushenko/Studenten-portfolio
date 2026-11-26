<?php

return [
    'user_id' => env('TEST_ACCOUNT_USER_ID'),
    'teacher_name' => env('TEST_ACCOUNT_TEACHER_NAME'),
    'teacher_group_id' => env('TEST_ACCOUNT_TEACHER_GROUP_ID'),
    'student' => [
        'user_id' => env('TEST_ACCOUNT_STUDENT_USER_ID'),
        'name' => env('TEST_ACCOUNT_STUDENT_NAME'),
        'number' => env('TEST_ACCOUNT_STUDENT_NUMBER'),
        'study_path' => env('TEST_ACCOUNT_STUDENT_STUDY_PATH'),
    ],
];
