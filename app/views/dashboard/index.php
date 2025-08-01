<?php
// Archivo: app/views/dashboard/index.php (Actualizado con campos detallados de planeación)
?>
<div class="container mx-auto py-10 px-6">
    <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">Bienvenido, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>

    <!-- Tarjeta de Progreso del Perfil -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Progreso de tu Perfil</h2>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: <?= $profile_completion_percentage ?>%"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2"><?= $profile_completion_percentage ?>% completado</p>
        </div>
        <a href="/dashboard/profile" class="btn btn-secondary bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out">
            Completar Perfil
        </a>
    </div>

    <!-- Bloque Mis Créditos -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Mis Créditos</h2>
            <p class="text-lg text-gray-800">Tienes <span class="font-bold text-indigo-600"><?= htmlspecialchars($user_data['creditos'] ?? 0) ?></span> créditos disponibles.</p>
            <p class="text-sm text-gray-600 mt-1">Cada planeación generada consume 1 crédito.</p>
        </div>
        <!-- Puedes añadir un botón para "Comprar Créditos" si tienes esa funcionalidad -->
        <!-- <a href="/dashboard/buy-credits" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out">
            Comprar Créditos
        </a> -->
    </div>

    <!-- Botón para Crear Nueva Planeación -->
    <div class="mb-8 text-right">
        <a href="/dashboard/create" class="btn btn-primary bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg flex items-center justify-center space-x-2 inline-flex transition duration-300 ease-in-out transform hover:scale-105">
            <i class="fas fa-plus-circle text-lg"></i>
            <span>Crear Nueva Planeación</span>
        </a>
    </div>

    <!-- Listado de Planeaciones -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Mis Planeaciones</h2>
        <?php if (empty($planeaciones)): ?>
            <p class="text-gray-600 text-center py-8">Aún no has creado ninguna planeación. ¡Anímate a crear la primera!</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Centro Educativo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PDA</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eje Articulador</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Creación</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($planeaciones as $planeacion): ?>
                            <?php
                                // $planeacion['prompt_data_decoded'] ya está disponible gracias al controlador
                                $prompt_data_decoded = $planeacion['prompt_data_decoded'];

                                $contenido_pda_parts = explode('||', $prompt_data_decoded['contenido_pda_id'] ?? '||');
                                $pda_display = trim($contenido_pda_parts[1] ?? 'N/A');
                                $ejes_display = !empty($prompt_data_decoded['ejes_articuladores']) ? implode(', ', $prompt_data_decoded['ejes_articuladores']) : 'N/A';
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($planeacion['id']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= htmlspecialchars($planeacion['centro_educativo_nombre'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= htmlspecialchars($planeacion['nivel'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= htmlspecialchars($planeacion['grado'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= htmlspecialchars($planeacion['asignatura'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" title="<?= htmlspecialchars($pda_display) ?>">
                                    <?= htmlspecialchars($pda_display) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" title="<?= htmlspecialchars($ejes_display) ?>">
                                    <?= htmlspecialchars($ejes_display) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= date('d/m/Y H:i', strtotime($planeacion['fecha_creacion'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="/dashboard/view/<?= htmlspecialchars($planeacion['id']) ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/dashboard/pdf/<?= htmlspecialchars($planeacion['id']) ?>" target="_blank" class="text-red-600 hover:text-red-900" title="Exportar a PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    <!-- Aquí podrías añadir un botón de eliminar si lo deseas -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
