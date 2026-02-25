<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MaterialDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $deliveries = $project->materialDeliveries()
            ->with(['supplyUser', 'siteManagerUser'])
            ->latest()
            ->paginate(15);

        return view('materials.index', compact('project', 'deliveries'));
    }

    public function create(Project $project)
    {
        $this->authorize('createMaterial', $project);

        return view('materials.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('createMaterial', $project);

        $request->validate([
            'material_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'delivery_date' => 'nullable|date',
        ]);

        MaterialDelivery::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'supply_user_id' => Auth::id(),
            'material_name' => $request->material_name,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'delivery_date' => $request->delivery_date,
            'status' => 'pending',
        ]);

        return redirect()->route('projects.materials.index', $project)
            ->with('success', 'Поставка добавлена');
    }

    public function confirm(Request $request, MaterialDelivery $delivery)
    {
        $this->authorize('confirmDelivery', $delivery);

        $request->validate([
            'photo' => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('deliveries/' . $delivery->id, 'public');
        }

        $delivery->update([
            'site_manager_user_id' => Auth::id(),
            'confirmed_date' => now(),
            'photo_path' => $photoPath,
            'status' => 'confirmed',
        ]);

        return back()->with('success', 'Поставка подтверждена');
    }
}