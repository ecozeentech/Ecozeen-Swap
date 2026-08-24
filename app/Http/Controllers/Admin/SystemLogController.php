<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->input('action'), fn ($q, $action) => $q->where('action', 'like', "%{$action}%"))
            ->when($request->input('user_id'), fn ($q, $userId) => $q->where('user_id', $userId))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.logs.index', ['logs' => $logs]);
    }
}
