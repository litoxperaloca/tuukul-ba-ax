<?php
// app/views/admin/contenidos_curriculares/index.php

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
            <h1 class="text-4xl font-bold text-gray-800">Gestión de Contenidos Curriculares</h1>
            <p class="text-lg text-gray-600 font-roboto">Administra los contenidos y ejes articuladores por curso.</p>
        </div>
        <a href="/admin/contenidos-curriculares/create" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Contenido
        </a>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-4/12 text-left py-3 px-4 uppercase font-semibold text-sm">Contenido / Eje</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Curso</th>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">Tipo</th>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">Activo</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($contenidos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <p class="text-gray-500">No hay contenidos curriculares registrados.</p>
                                <p class="text-gray-500">¡Haz clic en "Crear Nuevo Contenido" para comenzar!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contenidos as $contenido): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($contenido['id']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($contenido['nombre_contenido']) ?></td>
                                <td class="py-3 px-4">
                                    <?= htmlspecialchars($contenido['nivel'] ?? 'N/A') ?> -
                                    <?= htmlspecialchars($contenido['grado'] ?? 'N/A') ?> -
                                    <?= htmlspecialchars($contenido['asignatura'] ?? 'N/A') ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                        <?php
                                            if ($contenido['tipo'] === 'contenido') echo 'bg-blue-100 text-blue-700';
                                            else echo 'bg-purple-100 text-purple-700';
                                        ?>">
                                        <?= htmlspecialchars($contenido['tipo']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?= $contenido['activo'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= $contenido['activo'] ? 'Sí' : 'No' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-4">
                                        <a href="/admin/contenidos-curriculares/edit/<?= htmlspecialchars($contenido['id']) ?>" class="text-blue-600 hover:text-blue-900 font-semibold" title="Editar Contenido">Editar</a>
                                        <form action="/admin/contenidos-curriculares/process-delete/<?= htmlspecialchars($contenido['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este contenido curricular? Esta acción es irreversible.');">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold" title="Eliminar Contenido">Eliminar</button>
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
