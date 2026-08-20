<?php
namespace App\Controllers;

use App\Models\Equipos;

class EquipoController {
    public function alta($serialNumber, $hostname, $mac, $cpu, $ram, $diskType, $diskTotal, $laboratorio): bool {
        return Equipos::altaEquipo($serialNumber, $hostname, $mac, $cpu, $ram, $diskType, $diskTotal, $laboratorio);
    }

    public function actualizarSerial($id, $serialNumber): bool {
        return Equipos::actualizarSerial($id, $serialNumber);
    }

    public function listar($laboratorio): array {
        return Equipos::listarPorLaboratorio($laboratorio);
    }
    <?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentType;
use Illuminate\Http\Request;
use App\Imports\EquipmentImport;
use Maatwebsite\Excel\Facades\Excel;

class EquipmentController extends Controller
{
    public function index()
    {
        $types = EquipmentType::all();
        return view('equipments.index', compact('types'));
    }
    
    public function create()
    {
        $types = EquipmentType::all();
        return view('equipments.create', compact('types'));
    }
    
    public function show($id)
    {
        $equipment = Equipment::with(['type', 'creator', 'updater'])
            ->with(['maintenanceHistory' => function($query) {
                $query->orderBy('date', 'desc')->limit(10);
            }])
            ->findOrFail($id);
            
        return view('equipments.show', compact('equipment'));
    }
    
    public function edit($id)
    {
        $equipment = Equipment::findOrFail($id);
        $types = EquipmentType::all();
        return view('equipments.edit', compact('equipment', 'types'));
    }
    
    public function importForm()
    {
        return view('equipments.import');
    }
    
    // Métodos para la API
    public function apiIndex(Request $request)
    {
        $query = Equipment::with('type');
        
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('serial_number', 'like', "%{$request->search}%")
                  ->orWhere('model', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('type_id') && !empty($request->type_id)) {
            $query->where('type_id', $request->type_id);
        }
        
        return datatables()->eloquent($query)->toJson();
    }
    
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|unique:equipment',
            'model' => 'required',
            'type_id' => 'required|exists:equipment_types,id',
            'brand' => 'nullable',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'location' => 'required',
            'specifications' => 'nullable',
            'status' => 'required|in:operational,maintenance,retired',
            'notes' => 'nullable'
        ]);
        
        $validated['created_by'] = auth()->id();
        
        $equipment = Equipment::create($validated);
        
        return response()->json($equipment, 201);
    }
    
    public function apiUpdate(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);
        
        $validated = $request->validate([
            'serial_number' => 'required|unique:equipment,serial_number,' . $id,
            'model' => 'required',
            'type_id' => 'required|exists:equipment_types,id',
            'brand' => 'nullable',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'location' => 'required',
            'specifications' => 'nullable',
            'status' => 'required|in:operational,maintenance,retired',
            'notes' => 'nullable'
        ]);
        
        $validated['updated_by'] = auth()->id();
        
        $equipment->update($validated);
        
        return response()->json($equipment);
    }
    
    public function apiDestroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();
        
        return response()->json(null, 204);
    }
    
    public function apiImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);
        
        try {
            $import = new EquipmentImport($request->update_existing ?? false);
            Excel::import($import, $request->file('file'));
            
            return response()->json([
                'success_count' => $import->getSuccessCount(),
                'updated_count' => $import->getUpdatedCount(),
                'error_count' => $import->getErrorCount(),
                'errors' => $import->getErrors(),
                'total_count' => $import->getTotalCount()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al importar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function apiMaintenance(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);
        
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:preventive,corrective,calibration',
            'technician' => 'required',
            'description' => 'required'
        ]);
        
        $maintenance = $equipment->maintenanceHistory()->create($validated);
        
        // Si el equipo estaba en mantenimiento y se registra un mantenimiento correctivo,
        // cambiar el estado a operativo
        if ($equipment->status === 'maintenance' && $request->type === 'corrective') {
            $equipment->update(['status' => 'operational']);
        }
        
        return response()->json($maintenance, 201);
    }
    
    public function apiHistory($id)
    {
        $equipment = Equipment::findOrFail($id);
        $history = $equipment->maintenanceHistory()->orderBy('date', 'desc')->get();
        
        return response()->json($history);
    }
}
}
