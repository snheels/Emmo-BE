<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function teamMood()
    {
        $teamMood = User::with(['latestMood'])
            ->where('role', 'employee')
            ->take(5)
            ->get();

        return response()->json([
            'message' => 'success',
            'data' => $teamMood,
        ]);
    }

    public function weeklyMood()
    {
        $moods = Mood::where('user_id', Auth::id())
            ->whereBetween('date', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->selectRaw('mood')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('mood')
            ->get();

        return response()->json([
            'message' => 'success',
            'data' => $moods,
        ]);
    }

    public function index()
    {
        //
        $moods = Mood::where('user_id', Auth::id())->latest()->get();

        return response()->json([
            'message' => 'Get data success',
            'data' => $moods,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mood' => 'required|in:happy,sad,neutral,frustrated,excited',
        ], [
            'mood.required' => 'Please choose at least 1 mood',
        ]);

        $mood = Mood::create([
            'user_id' => Auth::id(),
            'mood' => strtolower($request->mood), // karna mood di fe nya depannya huruf kapital
            'date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Mood succesfully added',
            'data' => $mood,
        ], 201);
    }
}
