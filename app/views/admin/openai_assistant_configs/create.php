<?php
// app/views/admin/openai_assistant_configs/create.php

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
        <a href="/admin/asistentes-ia" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Crear Nueva Configuración de Asistente IA</h1>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow max-w-3xl mx-auto">
        <form action="/admin/asistentes-ia/process-create" method="POST" class="space-y-6">
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
                <label for="assistant_id" class="label-style">Assistant ID de OpenAI</label>
                <input type="text" name="assistant_id" id="assistant_id" required class="input-style font-mono" placeholder="Ej: asst_abc123DEF456">
                <p class="text-xs text-gray-500 mt-1">El ID del asistente que has creado en la plataforma de OpenAI.</p>
            </div>
            <div class="mt-4">
                <label for="vector_store_id" class="label-style">Vector Store ID de OpenAI (Opcional)</label>
                <input type="text" name="vector_store_id" id="vector_store_id" class="input-style font-mono" placeholder="Ej: vs_xyz789GHI012">
                <p class="text-xs text-gray-500 mt-1">El ID del Vector Store si este asistente usa la herramienta File Search para RAG.</p>
            </div>
            <div class="mt-4">
                <label for="instrucciones_adicionales" class="label-style">Instrucciones Adicionales para el Asistente (Opcional)</label>
                <textarea name="instrucciones_adicionales" id="instrucciones_adicionales" rows="5" class="input-style" placeholder="Ej: Asegúrate de siempre mencionar la importancia del calentamiento."></textarea>
                <p class="text-xs text-gray-500 mt-1">Instrucciones de sistema específicas que se añadirán al prompt del asistente.</p>
            </div>
            <div class="pt-4 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
</style>
