<?php

namespace App\Http\Controllers;

use App\Models\MOU;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Country;
use Carbon\Carbon;

class MOUController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $MOUs = MOU::all();
        return view('mou.index', compact('MOUs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::pluck('dep_name', 'dep_id');
        $countries = Country::pluck('ct_nameENG', 'ct_code');
        $currentYear = Carbon::now()->year;
        return view('mou.create', compact('departments', 'countries', 'currentYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mou_no' => 'required',
            'subject' => 'required',
            'ext_department' => 'required',
            'status' => 'required',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);

        // Handle file upload
        // if ($request->hasFile('file')) {
        //     $file = $request->file('file');
        //     $filePath = $file->store('uploads', 'public');
        // }
        if ($file = $request->file('file')) {
            $file_name =time().$file->getClientOriginalName();
            $file->move('uploads', $file_name);
        }

        // MOU::create($request->all());
        MOU::create([
            'mou_no' => $request->input('mou_no'),
            'mou_year' => $request->input('mou_year'),
            'subject' => $request->input('subject'),
            'ext_department' => $request->input('ext_department'),
            'dep_id' => $request->input('dep_id'),
            'country' => $request->input('ct_code'),
            'status' => $request->input('status'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'file_path' => $file_name ?? null,
        ]);

        session()->flash('success', 'MOU created successfully.');

        return redirect()->route('mous.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $MOU = MOU::findOrFail($id);
        $activities1 = Activity::Where('mou_id', $id)->where('round', 1)->get();
        $activities2 = Activity::Where('mou_id', $id)->where('round', 2)->get();
        // $activity2 = Activity::findOrFail($id)->where('round', 2);

        return view('mou.show', compact('MOU', 'activities1', 'activities2'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $MOU = MOU::findOrFail($id);
        $departments = Department::pluck('dep_name', 'dep_id');
        $countries = Country::pluck('ct_nameENG', 'ct_code');
        return view('mou.edit', compact('MOU', 'departments', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $MOU = MOU::findOrFail($id);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name =time().$file->getClientOriginalName();
            $fileToDelete = public_path('uploads/' . $MOU->file_path);
            unlink($fileToDelete);
            $file->move('uploads', $file_name);
        }
        $file_name = $MOU->file_path;

        $MOU->update(
            [
                'mou_no' => $request->input('mou_no'),
                'mou_year' => $request->input('mou_year'),
                'subject' => $request->input('subject'),
                'ext_department' => $request->input('ext_department'),
                'dep_id' => $request->input('dep_id'),
                'country' => $request->input('ct_code'),
                'status' => $request->input('status'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'file_path' => $file_name,
            ]
        );

        session()->flash('success', 'MOU updated successfully.');

        return redirect()->route('mous.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MOU $mOU)
    {
        //
    }
}
