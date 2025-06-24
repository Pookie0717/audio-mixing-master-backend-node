<?php

use App\Models\LogActivity;

function addLogActivity(String $action)
{
    LogActivity::create([
        'user_id' => auth()->user()->id,
        'action' => $action
    ]);
}

function standardDateTimeFormat($date)
{
    return date('D, d M Y h:i A', strtotime($date));
}

function simpleDateTimeFormat($date)
{
    return date('d/m/Y', strtotime($date));
}
