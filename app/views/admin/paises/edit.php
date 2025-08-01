<?php
// app/views/admin/paises/edit.php

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
        <a href="/admin/paises" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Editar País: <?= htmlspecialchars($pais['nombre']) ?></h1>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow max-w-xl mx-auto">
        <form action="/admin/paises/process-update/<?= htmlspecialchars($pais['id']) ?>" method="POST" class="space-y-6">
            <div>
                <label for="nombre" class="label-style">Nombre del País</label>
                <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($pais['nombre']) ?>" required class="input-style" placeholder="Ej: México">
            </div>
            <div>
                <label for="codigo_iso" class="label-style">Código ISO (2 letras)</label>
                <input type="text" name="codigo_iso" id="codigo_iso" maxlength="2" value="<?= htmlspecialchars($pais['codigo_iso']) ?>" required class="input-style uppercase" placeholder="Ej: MX">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="activo" id="activo" <?= $pais['activo'] ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <label for="activo" class="ml-2 text-sm text-gray-700">Activo</label>
            </div>
            <div class="pt-4 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar País
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
</style>
