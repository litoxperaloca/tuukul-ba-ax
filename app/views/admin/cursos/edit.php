<?php
// app/views/admin/cursos/edit.php

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
        <a href="/admin/cursos" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Editar Curso: <?= htmlspecialchars($curso['nivel']) ?> - <?= htmlspecialchars($curso['grado']) ?> - <?= htmlspecialchars($curso['asignatura']) ?></h1>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow max-w-3xl mx-auto">
        <form action="/admin/cursos/process-update/<?= htmlspecialchars($curso['id']) ?>" method="POST" class="space-y-6">
            <fieldset class="border p-4 rounded-md">
                <legend class="text-lg font-semibold px-2">Detalles del Curso</legend>
                <div>
                    <label for="id_plan_estudio" class="label-style">Plan de Estudio</label>
                    <select name="id_plan_estudio" id="id_plan_estudio" required class="input-style">
                        <option value="" disabled>Selecciona un plan de estudio</option>
                        <?php foreach ($planes_estudio as $plan): ?>
                            <option value="<?= htmlspecialchars($plan['id']) ?>" <?= ($plan['id'] == $curso['id_plan_estudio']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($plan['nombre_plan']) ?> (<?= htmlspecialchars($plan['nombre_pais'] ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label for="nivel" class="label-style">Nivel</label>
                        <input type="text" name="nivel" id="nivel" value="<?= htmlspecialchars($curso['nivel']) ?>" required class="input-style" placeholder="Ej: Primaria">
                    </div>
                    <div>
                        <label for="grado" class="label-style">Grado</label>
                        <input type="text" name="grado" id="grado" value="<?= htmlspecialchars($curso['grado']) ?>" required class="input-style" placeholder="Ej: 3°">
                    </div>
                    <div>
                        <label for="asignatura" class="label-style">Asignatura</label>
                        <input type="text" name="asignatura" id="asignatura" value="<?= htmlspecialchars($curso['asignatura']) ?>" required class="input-style" placeholder="Ej: Educación Física">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="fase" class="label-style">Fase (Opcional)</label>
                        <input type="text" name="fase" id="fase" value="<?= htmlspecialchars($curso['fase'] ?? '') ?>" class="input-style" placeholder="Ej: Fase 4">
                    </div>
                    <div>
                        <label for="descripcion" class="label-style">Descripción (Opcional)</label>
                        <textarea name="descripcion" id="descripcion" rows="2" class="input-style" placeholder="Breve descripción del curso"><?= htmlspecialchars($curso['descripcion'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="flex items-center mt-4">
                    <input type="checkbox" name="activo" id="activo" <?= $curso['activo'] ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="activo" class="ml-2 text-sm text-gray-700">Activo</label>
                </div>
            </fieldset>

            <fieldset class="border p-4 rounded-md">
                <legend class="text-lg font-semibold px-2">Asociar a Centros Educativos</legend>
                <p class="text-sm text-gray-600 mb-4">Selecciona los centros educativos donde se imparte este curso.</p>
                <div class="space-y-2 max-h-60 overflow-y-auto border p-3 rounded-md bg-gray-50">
                    <?php if (empty($centros_educativos)): ?>
                        <p class="text-gray-500 text-center">No hay centros educativos activos para asociar.</p>
                    <?php else: ?>
                        <?php foreach ($centros_educativos as $centro): ?>
                            <div class="flex items-center">
                                <input type="checkbox" name="centros_educativos[]" value="<?= htmlspecialchars($centro['id']) ?>" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200" <?= in_array($centro['id'], $centros_asociados_ids) ? 'checked' : '' ?>>
                                <span class="ml-2 text-sm font-medium text-gray-800">
                                    <?= htmlspecialchars($centro['nombre']) ?> (<?= htmlspecialchars($centro['nombre_pais'] ?? 'N/A') ?>)
                                    <?php if (!empty($centro['cct'])): ?> - CCT: <?= htmlspecialchars($centro['cct']) ?><?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </fieldset>

            <div class="pt-4 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Curso
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
</style>
