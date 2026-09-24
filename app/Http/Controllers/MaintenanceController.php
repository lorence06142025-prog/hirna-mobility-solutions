<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $records = MaintenanceRecord::with('vehicle')->latest()->get();
        $vehicles = Vehicle::all();

        return view('maintenance.index', compact('records', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_type' => 'required|string',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|in:scheduled,in_progress,completed',
            'scheduled_date' => 'required|date',
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completion_date'] = date('Y-m-d');
        }

        $record = MaintenanceRecord::create($validated);

        // Bi-directional synchronization with Fleet Vehicle Management
        $vehicle = Vehicle::find($validated['vehicle_id']);
        if ($vehicle) {
            if ($validated['status'] === 'in_progress') {
                $vehicle->update(['status' => 'maintenance']);
            } elseif ($validated['status'] === 'completed') {
                $vehicle->update(['status' => 'active']);
            }
        }

        return redirect()->back()->with('success', 'Preventive Maintenance Service (PMS) record created and vehicle availability synchronized.');
    }

    public function updateStatus(Request $request, MaintenanceRecord $record)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed',
            'completion_date' => 'nullable|date',
            'cost' => 'required|numeric|min:0',
        ]);

        if ($validated['status'] === 'completed' && empty($validated['completion_date'])) {
            $validated['completion_date'] = date('Y-m-d');
        }

        $record->update($validated);

        // Bi-directional synchronization with Fleet Vehicle Management
        if ($record->vehicle) {
            if ($validated['status'] === 'in_progress') {
                $record->vehicle->update(['status' => 'maintenance']);
            } elseif ($validated['status'] === 'completed') {
                $record->vehicle->update(['status' => 'active']);
            } elseif ($validated['status'] === 'scheduled' && $record->vehicle->status === 'maintenance') {
                $record->vehicle->update(['status' => 'active']);
            }
        }

        $statusLabel = ucfirst(str_replace('_', ' ', $validated['status']));
        return redirect()->back()->with('success', "Maintenance status updated to '{$statusLabel}'. Vehicle availability in Fleet Management updated accordingly.");
    }

    public function destroy(MaintenanceRecord $record)
    {
        $vehicle = $record->vehicle;
        $serviceType = $record->service_type;
        $record->delete();

        if ($vehicle && $vehicle->status === 'maintenance') {
            $hasOtherMaintenance = MaintenanceRecord::where('vehicle_id', $vehicle->id)
                ->where('status', 'in_progress')
                ->exists();
            if (!$hasOtherMaintenance) {
                $vehicle->update(['status' => 'active']);
            }
        }

        return redirect()->back()->with('success', "Maintenance record for '{$serviceType}' deleted successfully.");
    }
}
