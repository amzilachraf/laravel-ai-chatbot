<?php

namespace App\Http\Controllers;

use App\Models\Knowledge;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function index(){
        $knowledge = Knowledge::first();
        return view('knowledge.index', compact('knowledge'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'knowledge' => 'required|string|max:1000'
        ]);

        Knowledge::updateOrCreate(
            ['id' => 1], // Assuming you want to keep only one record
            ['information' => $request->knowledge]
        );

        return redirect()->route('knowledge.index')->with('success', 'Knowledge updated successfully.');
    }
}
