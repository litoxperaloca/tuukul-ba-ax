<?php
// app/views/dashboard/groups/view.php

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
        <a href="/dashboard/grupos" class="text-indigo-600 hover:text-indigo-800 mr-4" title="Volver a Mis Grupos">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Detalle del Grupo: <?= htmlspecialchars($grupo['nombre_grupo']) ?></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Información General del Grupo -->
        <div class="lg:col-span-1 bg-white p-8 rounded-lg card-shadow self-start">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-3">Información del Grupo</h2>
            <div class="space-y-4 text-gray-700 font-roboto">
                <div>
                    <h3 class="font-semibold text-gray-500">Nombre:</h3>
                    <p class="mt-1"><?= htmlspecialchars($grupo['nombre_grupo']) ?></p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-500">Docente Propietario:</h3>
                    <p class="mt-1"><?= htmlspecialchars($grupo['nombre_docente'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-500">Curso Asociado:</h3>
                    <p class="mt-1">
                        <?= htmlspecialchars($grupo['nivel'] ?? 'N/A') ?> -
                        <?= htmlspecialchars($grupo['grado'] ?? 'N/A') ?> -
                        <?= htmlspecialchars($grupo['asignatura'] ?? 'N/A') ?>
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-500">Descripción:</h3>
                    <p class="mt-1"><?= htmlspecialchars($grupo['descripcion'] ?? 'Sin descripción') ?></p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-500">Fecha de Creación:</h3>
                    <p class="mt-1"><?= date('d/m/Y H:i', strtotime($grupo['fecha_creacion'])) ?></p>
                </div>
            </div>
            <div class="mt-8 flex justify-end space-x-4">
                <a href="/dashboard/grupos/edit/<?= htmlspecialchars($grupo['id']) ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i> Editar Grupo
                </a>
                <form action="/dashboard/grupos/process-delete/<?= htmlspecialchars($grupo['id']) ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este grupo? Esta acción es irreversible y desvinculará a todos los estudiantes.');">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash-alt mr-2"></i> Eliminar Grupo
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Estudiantes en el Grupo -->
        <div class="lg:col-span-2 bg-white p-8 rounded-lg card-shadow">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-3">Estudiantes en el Grupo</h2>
            <?php if (empty($estudiantes_en_grupo)): ?>
                <p class="text-gray-500 text-center py-10">No hay estudiantes asignados a este grupo.</p>
                <p class="text-gray-500 text-center">Puedes añadirlos desde la opción "Editar Grupo".</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($estudiantes_en_grupo as $estudiante): ?>
                        <div class="border p-4 rounded-md bg-gray-50">
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($estudiante['nombre_estudiante']) ?> (<?= htmlspecialchars($estudiante['email_estudiante']) ?>)</p>
                            <?php if (!empty($estudiante['observaciones_inclusion'])): ?>
                                <p class="text-sm text-gray-700 mt-2">
                                    <span class="font-medium text-indigo-600">Observaciones de Inclusión:</span>
                                    <?= htmlspecialchars($estudiante['observaciones_inclusion']) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-sm text-gray-500 mt-2">Sin observaciones de inclusión específicas.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
