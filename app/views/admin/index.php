<?php
// app/views/admin/index.php (Actualización Final de Navegación)

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
    <h1 class="text-4xl font-bold text-gray-800 mb-8">Panel de Administración</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <!-- Tarjeta de Navegación: Gestión de Países -->
        <a href="/admin/paises" class="block bg-white p-6 rounded-lg card-shadow hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-globe-americas text-4xl text-indigo-500"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Gestión de Países</h2>
                    <p class="text-gray-600 text-sm">Añadir, editar y eliminar países.</p>
                </div>
            </div>
        </a>

        <!-- Tarjeta de Navegación: Gestión de Centros Educativos -->
        <a href="/admin/centros-educativos" class="block bg-white p-6 rounded-lg card-shadow hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-school text-4xl text-purple-500"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Gestión de Centros</h2>
                    <p class="text-gray-600 text-sm">Administrar centros educativos por país.</p>
                </div>
            </div>
        </a>

        <!-- Tarjeta de Navegación: Gestión de Planes de Estudio -->
        <a href="/admin/planes-estudio" class="block bg-white p-6 rounded-lg card-shadow hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-book-open text-4xl text-teal-500"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Gestión de Planes</h2>
                    <p class="text-gray-600 text-sm">Definir planes de estudio por país.</p>
                </div>
            </div>
        </a>

        <!-- Tarjeta de Navegación: Gestión de Cursos -->
        <a href="/admin/cursos" class="block bg-white p-6 rounded-lg card-shadow hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-chalkboard-teacher text-4xl text-orange-500"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Gestión de Cursos</h2>
                    <p class="text-gray-600 text-sm">Administrar niveles, grados y asignaturas.</p>
                </div>
            </div>
        </a>

        <!-- Tarjeta de Navegación: Gestión de Contenidos Curriculares -->
        <a href="/admin/contenidos-curriculares" class="block bg-white p-6 rounded-lg card-shadow hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-file-alt text-4xl text-green-500"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Gestión de Contenidos</h2>
                    <p class="text-gray-600 text-sm">Definir contenidos y ejes articuladores.</p>
                </div>
            </div>
        </a>

        <!-- Tarjeta de Navegación: Gestión de Formularios Dinámicos -->
        <a href="/admin/formularios-dinamicos" class="block bg-white p-6 rounded-lg card-shadow hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-wpforms text-4xl text-red-500"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Formularios Dinámicos</h2>
                    <p class="text-gray-600 text-sm">Crear formularios personalizados por curso.</p>
                </div>
            </div>
        </a>

        <!-- Tarjeta de Navegación: Configuración de Asistentes IA -->
        <a href="/admin/asistentes-ia" class="block bg-white p-6 rounded-lg card-shadow hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-robot text-4xl text-blue-500"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Asistentes IA</h2>
                    <p class="text-gray-600 text-sm">Vincular cursos con asistentes de OpenAI.</p>
                </div>
            </div>
        </a>

        <!-- Tarjeta de Navegación: Gestión de Créditos (Próximamente) -->
        <div class="block bg-white p-6 rounded-lg card-shadow opacity-60 cursor-not-allowed">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-coins text-4xl text-yellow-500"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Gestión de Créditos</h2>
                    <p class="text-gray-600 text-sm">Administrar créditos de usuario.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Sección de Gestión de Usuarios (existente) -->
    <div class="bg-white p-8 rounded-lg card-shadow">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Gestión de Usuarios</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="w-1/6 text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="w-2/6 text-left py-3 px-4 uppercase font-semibold text-sm">Nombre</th>
                        <th class="w-2/6 text-left py-3 px-4 uppercase font-semibold text-sm">Email</th>
                        <th class="w-1/6 text-left py-3 px-4 uppercase font-semibold text-sm">Rol</th>
                        <th class="w-1/6 text-left py-3 px-4 uppercase font-semibold text-sm">Créditos</th>
                        <th class="w-1/6 text-left py-3 px-4 uppercase font-semibold text-sm">Registro</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No hay usuarios registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-4"><?= htmlspecialchars($user['id']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($user['nombre']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                        <?php
                                            if ($user['role'] === 'admin') echo 'bg-green-100 text-green-700';
                                            elseif ($user['role'] === 'docente') echo 'bg-blue-100 text-blue-700';
                                            else echo 'bg-gray-100 text-gray-700';
                                        ?>">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4"><?= htmlspecialchars($user['creditos']) ?></td>
                                <td class="py-3 px-4"><?= date('d/m/Y', strtotime($user['fecha_registro'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
