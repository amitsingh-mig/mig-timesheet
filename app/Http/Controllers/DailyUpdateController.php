<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DailyUpdateController extends Controller
{
    /**
     * Display the daily update form and existing summaries
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date', Carbon::today()->toDateString());
        
        // Get existing daily summaries for the user
        $summaries = WorkSummary::forUser($user->id)
            ->byType('daily')
            ->orderBy('date', 'desc')
            ->paginate(10);
        
        // Get today's summary if exists
        $todaySummary = WorkSummary::forUser($user->id)
            ->forDate($date)
            ->byType('daily')
            ->first();
        
        return view('daily-update.index', compact('summaries', 'todaySummary', 'date'));
    }
    
    /**
     * Store a new daily update
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'summary' => 'required|string|max:1000',
        ]);
        
        $user = Auth::user();
        
        // Check if daily summary already exists for this date
        $existing = WorkSummary::forUser($user->id)
            ->forDate($request->date)
            ->byType('daily')
            ->first();
        
        if ($existing) {
            // Update existing summary
            $existing->update(['summary' => $request->summary]);
            return redirect()->route('daily-update.index')
                ->with('success', 'Daily update updated successfully!');
        } else {
            // Create new daily summary
            WorkSummary::createDailySummary(
                $user->id,
                $request->date,
                $request->summary
            );
            return redirect()->route('daily-update.index')
                ->with('success', 'Daily update created successfully!');
        }
}
    
    /**
     * API endpoint for getting daily updates (JSON)
     */
    public function api(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date', Carbon::today()->toDateString());
        
        $summary = WorkSummary::forUser($user->id)
            ->forDate($date)
            ->byType('daily')
            ->first();
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'summary' => $summary ? $summary->summary : null,
            'has_summary' => $summary ? true : false,
        ]);
    }

    /**
     * Force refresh Daily Update data and return latest entries
     */
    public function refreshData(Request $request)
    {
        $user = $request->user();

        // Clear any cached daily summaries for this user
        Cache::forget("daily_summaries_{$user->id}");

        $summaries = WorkSummary::forUser($user->id)
            ->byType('daily')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get(['id','date','summary']);

        return response()->json([
            'success' => true,
            'data' => $summaries,
        ]);
    }
}
