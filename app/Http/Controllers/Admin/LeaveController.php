<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LeaveController extends Controller
{
    public function index()
    {
        if (!Auth::user() || !optional(Auth::user()->role)->name === 'admin') {
            abort(403);
        }
        return view('admin.leave.index');
    }

    public function list(Request $request)
    {
        if (!Auth::user() || optional(Auth::user()->role)->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            if (!Schema::hasTable('leaves')) {
                return response()->json([
                    'success' => true,
                    'leaves' => [],
                    'message' => 'No leave table found; returning empty list'
                ]);
            }

            $query = DB::table('leaves')->join('users', 'users.id', '=', 'leaves.user_id')
                ->select('leaves.*', 'users.name as user_name', 'users.email as user_email')
                ->orderByDesc('leaves.created_at');

            if ($status = $request->get('status')) {
                $query->where('leaves.status', $status);
            }
            if ($uid = $request->get('user_id')) {
                $query->where('leaves.user_id', $uid);
            }

            $data = $query->paginate(10);

            return response()->json([
                'success' => true,
                'leaves' => $data->items(),
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'total' => $data->total(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Leave list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load leave requests'
            ], 500);
        }
    }

    public function approve(Request $request, int $id)
    {
        if (!Auth::user() || optional(Auth::user()->role)->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        if (!Schema::hasTable('leaves')) {
            return response()->json(['success' => false, 'message' => 'Leaves table missing'], 400);
        }
        DB::table('leaves')->where('id', $id)->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Leave approved']);
    }

    public function reject(Request $request, int $id)
    {
        if (!Auth::user() || optional(Auth::user()->role)->name !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        if (!Schema::hasTable('leaves')) {
            return response()->json(['success' => false, 'message' => 'Leaves table missing'], 400);
        }
        $reason = (string) $request->get('reason', '');
        DB::table('leaves')->where('id', $id)->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Leave rejected']);
    }
}


