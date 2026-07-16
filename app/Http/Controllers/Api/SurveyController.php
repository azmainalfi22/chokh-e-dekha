<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q',''));
        $surveys = Survey::query()
            ->where('status', 'published')
            ->when($q !== '', fn($qb) => $qb->where('title','like',"%$q%"))
            ->latest()->paginate($request->integer('per_page', 10));
        return response()->json($surveys);
    }

    public function show(Survey $survey)
    {
        abort_unless($survey->status === 'published', 404);
        return response()->json($survey);
    }

    public function submit(Request $request, Survey $survey)
    {
        abort_unless($survey->status === 'published', 404);
        $data = $request->validate([
            'answers' => ['required','array'],
            'city_corporation' => ['nullable','string','max:255'],
            'ward' => ['nullable','string','max:255'],
        ]);
        $resp = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $request->user()->id ?? null,
            'city_corporation' => $data['city_corporation'] ?? null,
            'ward' => $data['ward'] ?? null,
            'answers' => $data['answers'],
        ]);
        return response()->json(['ok' => true, 'id' => $resp->id], 201);
    }
}


