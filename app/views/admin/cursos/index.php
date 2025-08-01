<?php
// app/views/admin/cursos/index.php

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
            <h1 class="text-4xl font-bold text-gray-800">Gestión de Cursos</h1>
            <p class="text-lg text-gray-600 font-roboto">Administra los cursos, niveles, grados y asignaturas.</p>
        </div>
        <a href="/admin/cursos/create" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Crear Nuevo Curso
        </a>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Nivel</th>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">Grado</th>
                        <th class="w-3/12 text-left py-3 px-4 uppercase font-semibold text-sm">Asignatura</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Plan de Estudio</th>
                        <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">Activo</th>
                        <th class="w-2/12 text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($cursos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-10">
                                <p class="text-gray-500">No hay cursos registrados.</p>
                                <p class="text-gray-500">¡Haz clic en "Crear Nuevo Curso" para comenzar!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cursos as $curso): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($curso['id']) ?></td>
                                <td class="py-3 px-4 font-medium"><?= htmlspecialchars($curso['nivel']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($curso['grado']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($curso['asignatura']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($curso['nombre_plan'] ?? 'N/A') ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full <?= $curso['activo'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= $curso['activo'] ? 'Sí' : 'No' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-4">
                                        <a href="/admin/cursos/edit/<?= htmlspecialchars($curso['id']) ?>" class="text-blue-600 hover:text-blue-900 font-semibold" title="Editar Curso">Editar</a>
                                        <form action="/admin/cursos/process-delete/<?= htmlspecialchars($curso['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este curso? Esto eliminará también los contenidos, formularios, configuraciones de IA y desvinculará grupos y planeaciones. Esta acción es irreversible.');">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold" title="Eliminar Curso">Eliminar</button>
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
