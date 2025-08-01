<?php
// app/views/dashboard/groups/edit.php

// Mensajes de sesión (éxito/error)
if (isset($_SESSION['message'])): ?>
    <div class="container mx-auto px-6 mt-8">
        <div class="p-4 rounded-md <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($_SESSION['message']['text']); ?>
        </div>
    </div>
    <?php unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
endif;
?>

<div class="container mx-auto py-10 px-6">
    <div class="flex items-center mb-8">
        <a href="/dashboard/grupos/view/<?= htmlspecialchars($grupo['id']) ?>" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Editar Grupo: <?= htmlspecialchars($grupo['nombre_grupo']) ?></h1>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow max-w-3xl mx-auto">
        <form action="/dashboard/grupos/process-update/<?= htmlspecialchars($grupo['id']) ?>" method="POST" class="space-y-6">
            <fieldset class="border p-4 rounded-md">
                <legend class="text-lg font-semibold px-2">Información del Grupo</legend>
                <div>
                    <label for="nombre_grupo" class="label-style">Nombre del Grupo</label>
                    <input type="text" name="nombre_grupo" id="nombre_grupo" value="<?= htmlspecialchars($grupo['nombre_grupo']) ?>" required class="input-style" placeholder="Ej: 5to Grado - Grupo A">
                </div>
                <div class="mt-4">
                    <label for="id_curso" class="label-style">Curso Asociado</label>
                    <select name="id_curso" id="id_curso" required class="input-style">
                        <option value="" disabled>Selecciona un curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= htmlspecialchars($curso['id']) ?>" <?= ($curso['id'] == $grupo['id_curso']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($curso['nivel']) ?> -
                                <?= htmlspecialchars($curso['grado']) ?> -
                                <?= htmlspecialchars($curso['asignatura']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mt-4">
                    <label for="descripcion" class="label-style">Descripción (Opcional)</label>
                    <textarea name="descripcion" id="descripcion" rows="3" class="input-style" placeholder="Notas sobre el grupo, horario, etc."><?= htmlspecialchars($grupo['descripcion'] ?? '') ?></textarea>
                </div>
            </fieldset>

            <fieldset class="border p-4 rounded-md">
                <legend class="text-lg font-semibold px-2">Asignar Estudiantes</legend>
                <p class="text-sm text-gray-600 mb-4">Selecciona los estudiantes para este grupo y añade observaciones de inclusión si es necesario.</p>
                <div class="space-y-3 max-h-96 overflow-y-auto border p-3 rounded-md bg-gray-50">
                    <?php if (empty($estudiantes_disponibles)): ?>
                        <p class="text-gray-500 text-center">No hay estudiantes registrados en el sistema.</p>
                    <?php else: ?>
                        <?php foreach ($estudiantes_disponibles as $estudiante): ?>
                            <?php
                                $is_checked = in_array($estudiante['id'], $estudiantes_en_grupo_ids);
                                $observacion_actual = '';
                                foreach ($estudiantes_en_grupo as $eg) {
                                    if ($eg['id_estudiante'] == $estudiante['id']) {
                                        $observacion_actual = $eg['observaciones_inclusion'];
                                        break;
                                    }
                                }
                            ?>
                            <div class="flex flex-col border-b pb-3 mb-3 last:border-b-0 last:pb-0">
                                <label class="inline-flex items-center mb-2">
                                    <input type="checkbox" name="estudiantes[]" value="<?= htmlspecialchars($estudiante['id']) ?>" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 inclusion-checkbox" <?= $is_checked ? 'checked' : '' ?>>
                                    <span class="ml-2 text-sm font-medium text-gray-800"><?= htmlspecialchars($estudiante['nombre']) ?> (<?= htmlspecialchars($estudiante['email']) ?>)</span>
                                </label>
                                <div id="observaciones_container_<?= htmlspecialchars($estudiante['id']) ?>" class="ml-6 mt-1 <?= $is_checked ? '' : 'hidden' ?>">
                                    <label for="observaciones_inclusion_<?= htmlspecialchars($estudiante['id']) ?>" class="label-style text-xs">Observaciones de Inclusión:</label>
                                    <textarea name="observaciones_inclusion_<?= htmlspecialchars($estudiante['id']) ?>" id="observaciones_inclusion_<?= htmlspecialchars($estudiante['id']) ?>" rows="2" class="input-style text-sm" placeholder="Ej: Requiere apoyo visual, TDAH, etc."><?= htmlspecialchars($observacion_actual) ?></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </fieldset>

            <div class="pt-4 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Grupo
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.inclusion-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const studentId = this.value;
                const observationsContainer = document.getElementById(`observaciones_container_${studentId}`);
                if (this.checked) {
                    observationsContainer.classList.remove('hidden');
                } else {
                    observationsContainer.classList.add('hidden');
                    // Opcional: limpiar el contenido del textarea si se desmarca
                    // observationsContainer.querySelector('textarea').value = '';
                }
            });
        });
    });
</script>
