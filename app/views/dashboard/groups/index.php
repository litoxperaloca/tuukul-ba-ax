<?php
// app/views/dashboard/groups/index.php

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
            <h1 class="text-4xl font-bold text-gray-800">Mis Grupos</h1>
            <p class="text-lg text-gray-600 font-roboto">Gestiona tus grupos de estudiantes y sus necesidades de inclusión.</p>
        </div>
        <a href="/dashboard/grupos/create" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Grupo
        </a>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nombre del Grupo</th>
                        <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Curso Asociado</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Fecha de Creación</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($grupos)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <p class="text-gray-500">Aún no has creado ningún grupo.</p>
                                <p class="text-gray-500">¡Haz clic en "Crear Nuevo Grupo" para comenzar!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grupos as $grupo): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($grupo['id']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($grupo['nombre_grupo']) ?></td>
                                <td class="py-3 px-4">
                                    <?= htmlspecialchars($grupo['nivel'] ?? 'N/A') ?> -
                                    <?= htmlspecialchars($grupo['grado'] ?? 'N/A') ?> -
                                    <?= htmlspecialchars($grupo['asignatura'] ?? 'N/A') ?>
                                </td>
                                <td class="py-3 px-4"><?= date('d/m/Y H:i', strtotime($grupo['fecha_creacion'])) ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-4">
                                        <a href="/dashboard/grupos/view/<?= htmlspecialchars($grupo['id']) ?>" class="text-indigo-600 hover:text-indigo-900 font-semibold" title="Ver Detalle">Ver</a>
                                        <a href="/dashboard/grupos/edit/<?= htmlspecialchars($grupo['id']) ?>" class="text-blue-600 hover:text-blue-900 font-semibold" title="Editar Grupo">Editar</a>
                                        <form action="/dashboard/grupos/process-delete/<?= htmlspecialchars($grupo['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este grupo? Esta acción es irreversible.');">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold" title="Eliminar Grupo">Eliminar</button>
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
