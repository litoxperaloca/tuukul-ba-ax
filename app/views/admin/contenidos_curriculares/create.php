<?php
// app/views/admin/contenidos_curriculares/create.php

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
        <a href="/admin/contenidos-curriculares" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Crear Nuevo Contenido Curricular</h1>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow max-w-xl mx-auto">
        <form action="/admin/contenidos-curriculares/process-create" method="POST" class="space-y-6">
            <div>
                <label for="id_curso" class="label-style">Curso Asociado</label>
                <select name="id_curso" id="id_curso" required class="input-style">
                    <option value="" disabled selected>Selecciona un curso</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?= htmlspecialchars($curso['id']) ?>">
                            <?= htmlspecialchars($curso['nivel']) ?> -
                            <?= htmlspecialchars($curso['grado']) ?> -
                            <?= htmlspecialchars($curso['asignatura']) ?>
                            (Plan: <?= htmlspecialchars($curso['nombre_plan'] ?? 'N/A') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mt-4">
                <label for="tipo" class="label-style">Tipo</label>
                <select name="tipo" id="tipo" required class="input-style">
                    <option value="contenido">Contenido Curricular</option>
                    <option value="eje_articulador">Eje Articulador</option>
                </select>
            </div>
            <div class="mt-4">
                <label for="nombre_contenido" class="label-style">Nombre del Contenido / Eje</label>
                <input type="text" name="nombre_contenido" id="nombre_contenido" required class="input-style" placeholder="Ej: Capacidades y habilidades motrices">
            </div>
            <div class="mt-4" id="pda_field_container">
                <label for="pda_descripcion" class="label-style">Proceso de Desarrollo de Aprendizaje (PDA) - Opcional</label>
                <textarea name="pda_descripcion" id="pda_descripcion" rows="3" class="input-style" placeholder="Descripción del PDA"></textarea>
            </div>
            <div class="mt-4">
                <label for="orden" class="label-style">Orden de Visualización (Opcional)</label>
                <input type="number" name="orden" id="orden" value="0" class="input-style" min="0">
            </div>
            <div class="flex items-center mt-4">
                <input type="checkbox" name="activo" id="activo" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <label for="activo" class="ml-2 text-sm text-gray-700">Activo</label>
            </div>
            <div class="pt-4 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Contenido
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
        const tipoSelect = document.getElementById('tipo');
        const pdaFieldContainer = document.getElementById('pda_field_container');

        // Función para mostrar/ocultar el campo PDA
        function togglePdaField() {
            if (tipoSelect.value === 'contenido') {
                pdaFieldContainer.classList.remove('hidden');
            } else {
                pdaFieldContainer.classList.add('hidden');
                pdaFieldContainer.querySelector('textarea').value = ''; // Limpiar si se oculta
            }
        }

        // Ejecutar al cargar la página
        togglePdaField();

        // Ejecutar al cambiar el tipo
        tipoSelect.addEventListener('change', togglePdaField);
    });
</script>
