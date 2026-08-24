<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function show(Request $request): View
    {
        $logs = $request->user()->activityLogs()->latest()->paginate(20);

        return view('security.activity', ['logs' => $logs]);
    }
}
