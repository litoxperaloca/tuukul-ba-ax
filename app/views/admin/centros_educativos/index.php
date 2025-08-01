<?php
// app/views/admin/centros_educativos/index.php

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
            <h1 class="text-4xl font-bold text-gray-800">Gestión de Centros Educativos</h1>
            <p class="text-lg text-gray-600 font-roboto">Administra los centros educativos asociados a los países.</p>
        </div>
        <a href="/admin/centros-educativos/create" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Centro
        </a>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nombre</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">País</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">CCT</th>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">Activo</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($centros)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <p class="text-gray-500">No hay centros educativos registrados.</p>
                                <p class="text-gray-500">¡Haz clic en "Crear Nuevo Centro" para comenzar!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($centros as $centro): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($centro['id']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($centro['nombre']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($centro['nombre_pais'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($centro['cct'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?= $centro['activo'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= $centro['activo'] ? 'Sí' : 'No' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-4">
                                        <a href="/admin/centros-educativos/edit/<?= htmlspecialchars($centro['id']) ?>" class="text-blue-600 hover:text-blue-900 font-semibold" title="Editar Centro">Editar</a>
                                        <form action="/admin/centros-educativos/process-delete/<?= htmlspecialchars($centro['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este centro educativo? Esto eliminará también los cursos asociados. Esta acción es irreversible.');">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold" title="Eliminar Centro">Eliminar</button>
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
