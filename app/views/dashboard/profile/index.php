<?php
// app/views/dashboard/profile/index.php (Fase 2)

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
        <a href="/dashboard" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Mi Perfil</h1>
    </div>

    <!-- Barra de Progreso del Perfil -->
    <div class="bg-white p-6 rounded-lg card-shadow mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-tasks mr-3 text-indigo-600"></i> Estado de Completitud del Perfil
        </h2>
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
            <div class="bg-indigo-600 h-4 rounded-full" style="width: <?= htmlspecialchars($profile_completion_percentage) ?>%;"></div>
        </div>
        <p class="text-lg text-gray-700 text-center">
            Perfil Completado: <span class="font-semibold"><?= htmlspecialchars($profile_completion_percentage) ?>%</span>
        </p>
        <?php if ($profile_completion_percentage < 100): ?>
            <p class="text-center text-gray-600 mt-2">
                ¡Completa tu perfil para aprovechar al máximo todas las funcionalidades!
            </p>
        <?php else: ?>
            <p class="text-center text-green-600 font-semibold mt-2">
                ¡Tu perfil está 100% completo!
            </p>
        <?php endif; ?>
    </div>

    <!-- Bloque 1: Datos Personales -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-circle mr-3 text-blue-600"></i> Datos Personales
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="personal">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-personal" action="/dashboard/profile/update-personal" method="POST" class="space-y-4 mt-4 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombre" class="label-style">Nombre(s)</label>
                    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($user_data['nombre'] ?? '') ?>" required class="input-style">
                </div>
                <div>
                    <label for="apellidos" class="label-style">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" value="<?= htmlspecialchars($user_data['apellidos'] ?? '') ?>" required class="input-style">
                </div>
            </div>
            <div>
                <label for="documento_dni" class="label-style">Documento / DNI (Opcional)</label>
                <input type="text" name="documento_dni" id="documento_dni" value="<?= htmlspecialchars($user_data['documento_dni'] ?? '') ?>" class="input-style">
            </div>
            <div>
                <label for="id_pais" class="label-style">País</label>
                <select name="id_pais" id="id_pais" class="input-style">
                    <option value="">Selecciona tu país</option>
                    <?php foreach ($paises as $pais): ?>
                        <option value="<?= htmlspecialchars($pais['id']) ?>" <?= (isset($user_data['id_pais']) && $user_data['id_pais'] == $pais['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pais['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fecha_nacimiento" class="label-style">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?= htmlspecialchars($user_data['fecha_nacimiento'] ?? '') ?>" class="input-style">
            </div>
            <div>
                <label for="genero" class="label-style">Género</label>
                <select name="genero" id="genero" class="input-style">
                    <option value="">Selecciona tu género</option>
                    <option value="masculino" <?= (isset($user_data['genero']) && $user_data['genero'] == 'masculino') ? 'selected' : '' ?>>Masculino</option>
                    <option value="femenino" <?= (isset($user_data['genero']) && $user_data['genero'] == 'femenino') ? 'selected' : '' ?>>Femenino</option>
                    <option value="otro" <?= (isset($user_data['genero']) && $user_data['genero'] == 'otro') ? 'selected' : '' ?>>Otro</option>
                    <option value="prefiero_no_decir" <?= (isset($user_data['genero']) && $user_data['genero'] == 'prefiero_no_decir') ? 'selected' : '' ?>>Prefiero no decir</option>
                </select>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="personal">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-blue-600 text-white hover:bg-blue-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-personal" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Nombre Completo:</span> <?= htmlspecialchars($user_data['nombre'] ?? '') ?> <?= htmlspecialchars($user_data['apellidos'] ?? '') ?></p>
            <p><span class="font-semibold">Documento/DNI:</span> <?= htmlspecialchars($user_data['documento_dni'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">País:</span> <?= htmlspecialchars($user_data['nombre_pais'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Fecha de Nacimiento:</span> <?= htmlspecialchars($user_data['fecha_nacimiento'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Género:</span> <?= htmlspecialchars(ucfirst($user_data['genero'] ?? 'No especificado')) ?></p>
        </div>
    </div>

    <!-- Bloque 2: Datos Académicos -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-graduation-cap mr-3 text-green-600"></i> Datos Académicos
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="academic">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-academic" action="/dashboard/profile/update-academic" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label class="label-style">Asignaturas que enseñas:</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2 max-h-48 overflow-y-auto border p-3 rounded-md bg-gray-50">
                    <?php if (empty($all_asignaturas)): ?>
                        <p class="text-gray-500 col-span-full">No hay asignaturas disponibles para seleccionar.</p>
                    <?php else: ?>
                        <?php foreach ($all_asignaturas as $asignatura): ?>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="asignaturas[]" value="<?= htmlspecialchars($asignatura['id']) ?>"
                                    <?= in_array($asignatura['id'], $user_asignaturas_ids) ? 'checked' : '' ?>
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($asignatura['nombre']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="academic">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-green-600 text-white hover:bg-green-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-academic" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Asignaturas:</span>
                <?php if (empty($user_asignaturas_ids)): ?>
                    No especificadas
                <?php else: ?>
                    <?= htmlspecialchars(implode(', ', array_column($user_asignaturas, 'nombre'))) ?>
                <?php endif; ?>
            </p>
            <p class="mt-4">
                <span class="font-semibold">Administrar Grupos y Estudiantes:</span>
                <a href="/dashboard/grupos" class="text-indigo-600 hover:text-indigo-800 ml-2">Ir a Grupos <i class="fas fa-arrow-right ml-1"></i></a>
            </p>
        </div>
    </div>

    <!-- Bloque 3: Cuenta en la Plataforma -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-id-card-alt mr-3 text-purple-600"></i> Cuenta en la Plataforma
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="account">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-account" action="/dashboard/profile/update-account" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label for="email" class="label-style">Correo Electrónico</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required class="input-style">
            </div>
            <div>
                <label for="telefono_movil" class="label-style">Número de Móvil (Opcional)</label>
                <input type="tel" name="telefono_movil" id="telefono_movil" value="<?= htmlspecialchars($user_data['telefono_movil'] ?? '') ?>" class="input-style" placeholder="Ej: +52 123 456 7890">
            </div>
            <div class="mt-4">
                <label for="new_password" class="label-style">Nueva Contraseña (Dejar vacío para no cambiar)</label>
                <input type="password" name="new_password" id="new_password" class="input-style" placeholder="********">
            </div>
            <div>
                <label for="confirm_password" class="label-style">Confirmar Nueva Contraseña</label>
                <input type="password" name="confirm_password" id="confirm_password" class="input-style" placeholder="********">
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="account">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-purple-600 text-white hover:bg-purple-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-account" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Correo Electrónico:</span> <?= htmlspecialchars($user_data['email'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Número de Móvil:</span> <?= htmlspecialchars($user_data['telefono_movil'] ?? 'No especificado') ?></p>
            <p><span class="font-semibold">Contraseña:</span> ********</p>
            <div class="mt-4 flex items-center">
                <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden mr-4">
                    <?php if (!empty($user_data['foto_perfil_url'])): ?>
                        <img src="<?= htmlspecialchars($user_data['foto_perfil_url']) ?>" alt="Foto de Perfil" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas fa-user text-4xl text-gray-400"></i>
                    <?php endif; ?>
                </div>
                <form id="form-profile-picture" action="/dashboard/profile/upload-photo" method="POST" enctype="multipart/form-data" class="flex items-center">
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="hidden">
                    <label for="profile_picture" class="bg-indigo-500 text-white px-4 py-2 rounded-md cursor-pointer hover:bg-indigo-600 transition-colors text-sm font-semibold">
                        <i class="fas fa-upload mr-2"></i> Subir Foto
                    </label>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md ml-2 hover:bg-blue-600 transition-colors text-sm font-semibold hidden" id="save_profile_picture_btn">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bloque 4: Notificaciones de la Plataforma -->
    <div class="bg-white p-8 rounded-lg card-shadow mb-8">
        <div class="flex justify-between items-center section-header">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-bell mr-3 text-orange-600"></i> Notificaciones de la Plataforma
            </h2>
            <button type="button" class="text-blue-600 hover:text-blue-800 font-semibold edit-block-btn" data-block="notifications">
                <i class="fas fa-edit mr-1"></i> Editar
            </button>
        </div>
        <form id="form-notifications" action="/dashboard/profile/update-notifications" method="POST" class="space-y-4 mt-4 hidden">
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_email_notificaciones" value="1" <?= ($user_data['recibir_email_notificaciones'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir notificaciones por correo electrónico</span>
                </label>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_sms_notificaciones" value="1" <?= ($user_data['recibir_sms_notificaciones'] ?? false) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir notificaciones por SMS</span>
                </label>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="recibir_novedades_promociones" value="1" <?= ($user_data['recibir_novedades_promociones'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Recibir novedades, noticias y promociones de la plataforma</span>
                </label>
            </div>
            <div class="text-right">
                <button type="button" class="btn-cancel-edit text-gray-600 hover:text-gray-800 mr-4" data-block="notifications">Cancelar</button>
                <button type="submit" class="btn-save-edit bg-orange-600 text-white hover:bg-orange-700">Guardar Cambios</button>
            </div>
        </form>
        <div id="display-notifications" class="space-y-2 mt-4 text-gray-700">
            <p><span class="font-semibold">Correo Electrónico:</span> <?= ($user_data['recibir_email_notificaciones'] ?? true) ? 'Sí' : 'No' ?></p>
            <p><span class="font-semibold">SMS:</span> <?= ($user_data['recibir_sms_notificaciones'] ?? false) ? 'Sí' : 'No' ?></p>
            <p><span class="font-semibold">Novedades/Promociones:</span> <?= ($user_data['recibir_novedades_promociones'] ?? true) ? 'Sí' : 'No' ?></p>
        </div>
    </div>

</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
    .btn-save-edit { padding: 0.625rem 1.25rem; border-radius: 0.375rem; font-weight: 600; transition: background-color 0.3s ease; }
</style>

<script src="/js/profile.js"></script>
