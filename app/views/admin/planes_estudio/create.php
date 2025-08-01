<?php
// app/views/admin/planes_estudio/create.php

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
        <a href="/admin/planes-estudio" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Crear Nuevo Plan de Estudio</h1>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow max-w-xl mx-auto">
        <form action="/admin/planes-estudio/process-create" method="POST" class="space-y-6">
            <div>
                <label for="id_pais" class="label-style">País</label>
                <select name="id_pais" id="id_pais" required class="input-style">
                    <option value="" disabled selected>Selecciona un país</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?= htmlspecialchars($pais['id']) ?>"><?= htmlspecialchars($pais['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mt-4">
                <label for="nombre_plan" class="label-style">Nombre del Plan de Estudio</label>
                <input type="text" name="nombre_plan" id="nombre_plan" required class="input-style" placeholder="Ej: Plan de Estudios 2022">
            </div>
            <div class="mt-4">
                <label for="descripcion" class="label-style">Descripción (Opcional)</label>
                <textarea name="descripcion" id="descripcion" rows="2" class="input-style" placeholder="Breve descripción del plan de estudio"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="anio_vigencia_inicio" class="label-style">Año de Inicio de Vigencia</label>
                    <input type="number" name="anio_vigencia_inicio" id="anio_vigencia_inicio" required class="input-style" value="<?= date('Y') ?>" min="1900" max="2100">
                </div>
                <div>
                    <label for="anio_vigencia_fin" class="label-style">Año de Fin de Vigencia (Opcional)</label>
                    <input type="number" name="anio_vigencia_fin" id="anio_vigencia_fin" class="input-style" placeholder="Ej: 2030" min="1900" max="2100">
                </div>
            </div>
            <div class="flex items-center mt-4">
                <input type="checkbox" name="activo" id="activo" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <label for="activo" class="ml-2 text-sm text-gray-700">Activo</label>
            </div>
            <div class="pt-4 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Plan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
</style>
