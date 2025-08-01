<?php
// app/views/admin/planes_estudio/index.php

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
            <h1 class="text-4xl font-bold text-gray-800">Gestión de Planes de Estudio</h1>
            <p class="text-lg text-gray-600 font-roboto">Administra los planes de estudio por país.</p>
        </div>
        <a href="/admin/planes-estudio/create" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Plan
        </a>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nombre del Plan</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">País</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Vigencia</th>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">Activo</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($planes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <p class="text-gray-500">No hay planes de estudio registrados.</p>
                                <p class="text-gray-500">¡Haz clic en "Crear Nuevo Plan" para comenzar!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($planes as $plan): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($plan['id']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($plan['nombre_plan']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($plan['nombre_pais'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4">
                                    <?= htmlspecialchars($plan['anio_vigencia_inicio']) ?>
                                    <?= $plan['anio_vigencia_fin'] ? ' - ' . htmlspecialchars($plan['anio_vigencia_fin']) : ' - Actual' ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?= $plan['activo'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= $plan['activo'] ? 'Sí' : 'No' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-4">
                                        <a href="/admin/planes-estudio/edit/<?= htmlspecialchars($plan['id']) ?>" class="text-blue-600 hover:text-blue-900 font-semibold" title="Editar Plan">Editar</a>
                                        <form action="/admin/planes-estudio/process-delete/<?= htmlspecialchars($plan['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este plan de estudio? Esto eliminará también todos los cursos asociados. Esta acción es irreversible.');">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold" title="Eliminar Plan">Eliminar</button>
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
