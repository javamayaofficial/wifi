<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->when($request->model, fn ($q, $s) => $q->where('model_type', $s))
            ->when($request->event, fn ($q, $s) => $q->where('event', $s))
            ->when($request->user, fn ($q, $s) => $q->where('user_id', $s))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $models = AuditLog::distinct()->pluck('model_type');

        return view('audit.index', compact('logs', 'models'));
    }
}
