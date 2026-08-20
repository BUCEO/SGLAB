@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalles del Equipo</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('equipments.edit', $equipment->id) }}" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('equipments.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Número de Serie:</strong> {{ $equipment->serial_number }}</p>
                        <p><strong>Modelo:</strong> {{ $equipment->model }}</p>
                        <p><strong>Marca:</strong> {{ $equipment->brand ?? 'N/A' }}</p>
                        <p><strong>Tipo:</strong> {{ $equipment->type->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Estado:</strong> 
                            <span class="badge bg-{{ $equipment->status === 'operational' ? 'success' : ($equipment->status === 'maintenance' ? 'warning' : 'danger') }}">
                                {{ $equipment->status }}
                            </span>
                        </p>
                        <p><strong>Ubicación:</strong> {{ $equipment->location }}</p>
                        <p><strong>Fecha de Compra:</strong> {{ $equipment->purchase_date ? $equipment->purchase_date->format('d/m/Y') : 'N/A' }}</p>
                        <p><strong>Fin de Garantía:</strong> {{ $equipment->warranty_expiry ? $equipment->warranty_expiry->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                </div>
                
                @if($equipment->specifications)
                <div class="mt-3">
                    <h6>Especificaciones:</h6>
                    <p>{{ $equipment->specifications }}</p>
                </div>
                @endif
                
                @if($equipment->notes)
                <div class="mt-3">
                    <h6>Notas:</h6>
                    <p>{{ $equipment->notes }}</p>
                </div>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Historial de Mantenimiento</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Técnico</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenanceHistory as $record)
                            <tr>
                                <td>{{ $record->date->format('d/m/Y') }}</td>
                                <td>{{ $record->type }}</td>
                                <td>{{ $record->description }}</td>
                                <td>{{ $record->technician }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay registros de mantenimiento</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                        <i class="fas fa-tools"></i> Registrar Mantenimiento
                    </button>
                    <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#statusModal">
                        <i class="fas fa-exchange-alt"></i> Cambiar Estado
                    </button>
                    <button class="btn btn-outline-danger" id="deleteEquipment">
                        <i class="fas fa-trash"></i> Eliminar Equipo
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Información del Sistema</h5>
            </div>
            <div class="card-body">
                <p><strong>Registrado por:</strong> {{ $equipment->creator->name }}</p>
                <p><strong>Fecha de registro:</strong> {{ $equipment->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Última actualización:</strong> {{ $equipment->updated_at->format('d/m/Y H:i') }}</p>
                
                @if($equipment->updater)
                <p><strong>Actualizado por:</strong> {{ $equipment->updater->name }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal para mantenimiento -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="maintenanceForm" action="{{ route('api.equipments.maintenance', $equipment->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="maintenance_date" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="maintenance_date" name="date" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label for="maintenance_type" class="form-label">Tipo</label>
                        <select class="form-select" id="maintenance_type" name="type" required>
                            <option value="preventive">Preventivo</option>
                            <option value="corrective">Correctivo</option>
                            <option value="calibration">Calibración</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="technician" class="form-label">Técnico</label>
                        <input type="text" class="form-control" id="technician" name="technician" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado del Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm" action="{{ route('api.equipments.update', $equipment->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_status" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="new_status" name="status" required>
                            <option value="operational" {{ $equipment->status === 'operational' ? 'selected' : '' }}>Operativo</option>
                            <option value="maintenance" {{ $equipment->status === 'maintenance' ? 'selected' : '' }}>En Mantenimiento</option>
                            <option value="retired" {{ $equipment->status === 'retired' ? 'selected' : '' }}>Retirado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Notas (opcional)</label>
                        <textarea class="form-control" id="status_notes" name="notes" rows="2">{{ $equipment->notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Eliminar equipo
    $('#deleteEquipment').click(function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/equipments/{{ $equipment->id }}',
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        Swal.fire(
                            '¡Eliminado!',
                            'El equipo ha sido eliminado.',
                            'success'
                        ).then(() => {
                            window.location.href = '/equipments';
                        });
                    },
                    error: function() {
                        Swal.fire(
                            'Error',
                            'Hubo un problema al eliminar el equipo.',
                            'error'
                        );
                    }
                });
            }
        });
    });
    
    // Formulario de mantenimiento
    $('#maintenanceForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Mantenimiento registrado correctamente',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    $('#maintenanceModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Error al registrar el mantenimiento';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = '';
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errorMessage += value[0] + '\n';
                    });
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });
    
    // Formulario de estado
    $('#statusForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'PUT',
            data: formData,
            success: function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Estado actualizado correctamente',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    $('#statusModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Error al actualizar el estado';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = '';
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errorMessage += value[0] + '\n';
                    });
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });
});
</script>
@endpush