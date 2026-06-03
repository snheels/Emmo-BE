<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function monthlyProductivity()
    {
        $reports = Report::where('user_id', Auth::id())
            ->whereYear('date', now()->year)
            ->selectRaw('MONTHNAME(date) as month')
            ->selectRaw('COUNT(*) as reports')
            ->groupBy('month')
            ->get();

        return response()->json([
            'message' => 'success',
            'data' => $reports,
        ]);
    }

    public function index()
    {
        //
        $reports = Report::where('user_id', Auth::id())->latest()->get();

        return response()->json([
            'message' => 'Get data success',
            'data' => $reports,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'todays_work' => 'required|array|min:1',
            'todays_work.*' => 'required|string|min:3',
            'obstacle' => 'required|min:3',
        ],
            [
                'todays_work.required' => 'You have to fill what you have done',
                'todays_work.*.min' => 'Please fill at least 3 letters',
                'obstacle.required' => 'You have to fill what you have been struggle these days',
                'obstacle.min' => 'Please fill at least 3 letters',
            ]);

        $report = Report::create([
            'user_id' => Auth::id(),
            'date' => now()->toDateString(),
            'todays_work' => $request->todays_work,
            'obstacle' => $request->obstacle,
        ]);

        return response()->json([
            'message' => 'Reflection has succesfully added',
            'data' => $report,
        ], 201);
    }

}
