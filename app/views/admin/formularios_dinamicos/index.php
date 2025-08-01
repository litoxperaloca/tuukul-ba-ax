<?php
// app/views/admin/formularios_dinamicos/index.php

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
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Gestión de Formularios Dinámicos</h1>
            <p class="text-lg text-gray-600 font-roboto">Define los formularios personalizados para cada curso.</p>
        </div>
        <a href="/admin/formularios-dinamicos/create" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Formulario
        </a>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-4/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nombre del Formulario</th>
                        <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Curso Asociado</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Fecha Creación</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($formularios)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <p class="text-gray-500">No hay formularios dinámicos registrados.</p>
                                <p class="text-gray-500">¡Haz clic en "Crear Nuevo Formulario" para comenzar!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($formularios as $formulario): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($formulario['id']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($formulario['nombre']) ?></td>
                                <td class="py-3 px-4">
                                    <?= htmlspecialchars($formulario['nivel'] ?? 'N/A') ?> -
                                    <?= htmlspecialchars($formulario['grado'] ?? 'N/A') ?> -
                                    <?= htmlspecialchars($formulario['asignatura'] ?? 'N/A') ?>
                                </td>
                                <td class="py-3 px-4"><?= date('d/m/Y', strtotime($formulario['fecha_creacion'])) ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-4">
                                        <a href="/admin/formularios-dinamicos/edit/<?= htmlspecialchars($formulario['id']) ?>" class="text-blue-600 hover:text-blue-900 font-semibold" title="Editar Formulario">Editar</a>
                                        <form action="/admin/formularios-dinamicos/process-delete/<?= htmlspecialchars($formulario['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este formulario dinámico? Esta acción es irreversible.');">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold" title="Eliminar Formulario">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
