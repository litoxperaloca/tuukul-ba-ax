<?php
// app/views/dashboard/create.php

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
        <h1 class="text-4xl font-bold text-gray-800">Crear Nueva Planeación</h1>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div class="lg:col-span-3 bg-white p-8 rounded-lg card-shadow">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Generador de Planeación Didáctica</h2>
            <form id="planeacion-form" class="space-y-6">
                <div id="form-sections" class="space-y-6">
                    <!-- Bloque: Datos del Docente y Escuela -->
                    <details class="group border rounded-md" open>
                        <summary class="flex justify-between items-center bg-gray-100 p-4 cursor-pointer font-semibold text-lg text-gray-700">
                            <span class="flex items-center"><i class="fas fa-user-edit mr-3 text-blue-600"></i>Datos del Docente y Escuela</span>
                            <i class="fas fa-chevron-down transform transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="p-4 space-y-4">
                            <p class="text-sm text-gray-600">Información de tu perfil, pre-cargada.</p>
                            <?php
                            $docente_nombre_completo = ($user_data['nombre'] ?? '') . ' ' . ($user_data['apellidos'] ?? '');
                            $docente_pais_nombre = $user_data['nombre_pais'] ?? '';
                            $profile_incomplete = empty(trim($docente_nombre_completo)) || empty(trim($docente_pais_nombre));
                            ?>

                            <?php if ($profile_incomplete): ?>
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">¡Perfil Incompleto!</strong>
                                    <span class="block sm:inline">Por favor, completa tu <a href="/dashboard/profile" class="font-semibold underline">perfil</a> para asegurar una planeación precisa.</span>
                                </div>
                            <?php endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                <div>
                                    <label for="docente_nombre_completo" class="label-style">Nombre Completo del Docente</label>
                                    <input type="text" name="docente_nombre_completo" id="docente_nombre_completo"
                                        value="<?= htmlspecialchars($docente_nombre_completo) ?>"
                                        class="input-style bg-gray-100" readonly>
                                </div>
                                <div>
                                    <label for="docente_pais" class="label-style">País del Docente</label>
                                    <input type="text" name="docente_pais" id="docente_pais"
                                        value="<?= htmlspecialchars($docente_pais_nombre) ?>"
                                        class="input-style bg-gray-100" readonly>
                                    <input type="hidden" name="docente_pais_id" value="<?= htmlspecialchars($user_data['id_pais'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </details>

                    <!-- Bloque: Marco Curricular -->
                    <details class="group border rounded-md" open>
                        <summary class="flex justify-between items-center bg-gray-100 p-4 cursor-pointer font-semibold text-lg text-gray-700">
                            <span class="flex items-center"><i class="fas fa-book-open mr-3 text-green-600"></i> Marco Curricular de la Planeación</span>
                            <i class="fas fa-chevron-down transform transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="p-4 space-y-4">
                            <p class="text-sm text-gray-600 mb-4">Selecciona el contexto educativo de tu planeación. Estos datos son cruciales para la IA.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                <div>
                                    <label for="pais_id" class="label-style">País del Currículo <span class="text-red-500">*</span></label>
                                    <select id="pais_id" name="pais_id" class="input-style" required>
                                        <option value="" disabled selected>Selecciona un país</option>
                                        <?php foreach ($paises as $pais): ?>
                                            <option value="<?= htmlspecialchars($pais['id']) ?>">
                                                <?= htmlspecialchars($pais['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="centro_educativo_id" class="label-style">Centro de Estudio <span class="text-red-500">*</span></label>
                                    <select id="centro_educativo_id" name="centro_educativo_id" class="input-style" disabled required>
                                        <option value="" disabled selected>Primero selecciona un país</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="plan_estudio_id" class="label-style">Plan de Estudio <span class="text-red-500">*</span></label>
                                    <select id="plan_estudio_id" name="plan_estudio_id" class="input-style" disabled required>
                                        <option value="" disabled selected>Primero selecciona un centro educativo</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="curso_id" class="label-style">Curso (Nivel, Grado, Asignatura) <span class="text-red-500">*</span></label>
                                    <select id="curso_id" name="curso_id" class="input-style" disabled required>
                                        <option value="" disabled selected>Primero selecciona un plan de estudio</option>
                                    </select>
                                    <input type="hidden" name="fase" id="fase">
                                    <input type="hidden" name="escuela_nombre" id="escuela_nombre">
                                    <input type="hidden" name="escuela_cct" id="escuela_cct">
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="label-style">Contenido Curricular <span class="text-red-500">*</span></label>
                                <div id="contenidos_checkbox_container" class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2 p-3 bg-gray-50 rounded-md border max-h-60 overflow-y-auto">
                                    <p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar contenidos.</p>
                                </div>
                            </div>
                            <div id="pda_display_container" class="hidden mt-4 p-3 bg-gray-50 rounded-md border">
                                <label class="label-style">Proceso(s) de Desarrollo de Aprendizaje (PDA)</label>
                                <div id="pda_display" class="text-sm text-gray-700 space-y-2"></div>
                            </div>
                            <div class="mt-4">
                                <label class="label-style">Ejes Articuladores</label>
                                <div id="ejes_articuladores_container" class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2 p-3 bg-gray-50 rounded-md border">
                                    <p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>
                                </div>
                            </div>
                        </div>
                    </details>

                    <!-- Bloque: Sugerencias y Observaciones -->
                    <details class="group border rounded-md" open>
                        <summary class="flex justify-between items-center bg-gray-100 p-4 cursor-pointer font-semibold text-lg text-gray-700">
                            <span class="flex items-center"><i class="fas fa-lightbulb mr-3 text-yellow-600"></i> Sugerencias y Observaciones</span>
                            <i class="fas fa-chevron-down transform transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="p-4 space-y-4">
                            <p class="text-sm text-gray-600 mb-4">Añade cualquier sugerencia o detalle adicional relevante para la IA.</p>
                            <div class="mt-2">
                                <label for="sugerencias_sesiones" class="label-style">Sugerencias a tomar en cuenta en la planeación (Opcional)</label>
                                <textarea name="sugerencias_sesiones" id="sugerencias_sesiones" rows="3" class="input-style" placeholder="Ej: Enfocarse en actividades al aire libre, incluir un componente musical, etc."></textarea>
                            </div>
                            <div class="mt-4">
                                <label for="observaciones_docente" class="label-style">Observaciones del Docente (Opcional)</label>
                                <textarea name="observaciones_docente" id="observaciones_docente" rows="3" class="input-style" placeholder="Cualquier otra observación relevante para la planeación."></textarea>
                            </div>
                        </div>
                    </details>

                    <!-- Nuevo Bloque: Materiales y Recursos Disponibles -->
                    <details class="group border rounded-md" open>
                        <summary class="flex justify-between items-center bg-gray-100 p-4 cursor-pointer font-semibold text-lg text-gray-700">
                            <span class="flex items-center"><i class="fas fa-boxes mr-3 text-orange-600"></i> Materiales y Recursos Disponibles</span>
                            <i class="fas fa-chevron-down transform transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="p-4 space-y-4">
                            <p class="text-sm text-gray-600 mb-4">Lista los materiales y recursos que tienes disponibles para las sesiones.</p>
                            <div class="mt-2">
                                <label for="materiales" class="label-style">Materiales Disponibles (Opcional)</label>
                                <textarea name="materiales" id="materiales" rows="2" class="input-style" placeholder="Ej: Conos, aros, pelotas, cuerdas..."></textarea>
                            </div>
                        </div>
                    </details>

                    <!-- Bloque: Inclusión -->
                    <details class="group border rounded-md" open>
                        <summary class="flex justify-between items-center bg-gray-100 p-4 cursor-pointer font-semibold text-lg text-gray-700">
                            <span class="flex items-center"><i class="fas fa-universal-access mr-3 text-purple-600"></i> Inclusión</span>
                            <i class="fas fa-chevron-down transform transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="p-4 space-y-4">
                            <p class="text-sm text-gray-600 mb-4">Indica los estudiantes con necesidades educativas especiales para que la IA adapte la planeación.</p>
                            <div id="nee_students_table_container" class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-md">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Nombre del Estudiante</th>
                                            <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600">Necesidad Especial</th>
                                            <th class="py-2 px-4 border-b text-center text-sm font-semibold text-gray-600">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="nee_students_tbody">
                                        <!-- Students will be added here by JavaScript -->
                                        <tr><td colspan="3" class="text-center py-4 text-gray-500">No hay estudiantes con NEE añadidos.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="add_nee_student_btn" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i> Añadir Estudiante con NEE
                            </button>
                        </div>
                    </details>

                    <!-- Bloque: Información Específica del Curso (Formulario Dinámico) -->
                    <details class="group border rounded-md hidden" id="dynamic_form_details">
                        <summary class="flex justify-between items-center bg-gray-100 p-4 cursor-pointer font-semibold text-lg text-gray-700">
                            <span class="flex items-center"><i class="fas fa-info-circle mr-3 text-teal-600"></i> Información Específica del Curso</span>
                            <i class="fas fa-chevron-down transform transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="p-4 space-y-4">
                            <p class="text-sm text-gray-600 mb-4">Estos campos son específicos para el curso seleccionado y ayudarán a la IA a generar una planeación más precisa.</p>
                            <div id="dynamic_form_container" class="space-y-4 mt-2">
                                <p class="text-gray-500 text-center">Selecciona un curso para cargar información específica.</p>
                            </div>
                        </div>
                    </details>

                    <!-- Bloque: Detalles de la Sesión -->
                    <details class="group border rounded-md" open>
                        <summary class="flex justify-between items-center bg-gray-100 p-4 cursor-pointer font-semibold text-lg text-gray-700">
                            <span class="flex items-center"><i class="fas fa-cogs mr-3 text-blue-600"></i> Detalles de la Sesión</span>
                            <i class="fas fa-chevron-down transform transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="p-4 space-y-4">
                            <p class="text-sm text-gray-600 mb-4">Define la cantidad y duración de las sesiones a planear.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                                <div>
                                    <label for="num_sesiones" class="label-style">Número de Sesiones <span class="text-red-500">*</span></label>
                                    <input type="number" name="num_sesiones" id="num_sesiones" value="1" min="1" max="10" class="input-style" required>
                                </div>
                                <div>
                                    <label for="duracion_sesion" class="label-style">Duración por Sesión (min) <span class="text-red-500">*</span></label>
                                    <input type="number" name="duracion_sesion" id="duracion_sesion" value="50" min="20" max="60" step="5" class="input-style" required>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Botón de Siguiente para mostrar resumen -->
                <div id="show-summary-btn-container" class="flex justify-end mt-6">
                    <button type="button" id="show-summary-btn" class="px-6 py-3 bg-indigo-600 text-white font-bold text-base rounded-lg shadow-md hover:bg-indigo-700 transition-colors">Siguiente (Ver Resumen) <i class="fas fa-arrow-right ml-2"></i></button>
                </div>

                <!-- Bloque de Resumen y Botones Finales (Inicialmente oculto) -->
                <div id="summary-section" class="hidden bg-gray-50 p-6 rounded-lg border-2 border-indigo-200">
                    <div id="summary-container">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-check-circle mr-3 text-indigo-600"></i> Resumen de la Planeación
                        </h3>
                        <div id="summary_content" class="space-y-3 text-gray-700 mb-6">
                            <p class="text-gray-500 text-center">No hay datos para mostrar. Por favor, completa los campos.</p>
                        </div>
                    </div>
                    <div class="flex items-center mb-4">
                        <input type="checkbox" name="confirm_generate" id="confirm_generate" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="confirm_generate" class="ml-2 text-sm text-gray-700">Confirmo que los datos ingresados son correctos y deseo generar la planeación.</label>
                    </div>
                    <p class="text-sm text-red-600 font-semibold mb-6">
                        Al generar la planeación, se descontará 1 crédito de tu cuenta. Créditos actuales: <?= htmlspecialchars($_SESSION['user_credits'] ?? 0) ?>.
                    </p>
                    <div class="flex justify-between flex-wrap gap-4">
                        <button type="button" id="back-to-edit-btn" class="px-6 py-3 bg-gray-300 text-gray-800 font-bold text-base rounded-lg shadow-md hover:bg-gray-400 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i> Atrás (Editar)
                        </button>
                        <button type="submit" id="generate-btn" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                            <i class="fas fa-magic mr-2"></i> Entiendo, Genera la Planeación
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="lg:col-span-2 bg-white rounded-lg card-shadow flex flex-col">
             <h2 class="text-2xl font-bold text-gray-800 p-8 pb-0">Estado de Generación</h2>
            <div id="loader" class="hidden text-center py-10 flex-grow flex flex-col justify-center items-center">
                <i class="fas fa-spinner fa-spin fa-3x text-indigo-600"></i>
                <p class="mt-4 text-gray-600 font-roboto">Diseñando sesiones... por favor, espera.</p>
            </div>
            <div id="generation-message" class="prose max-w-none text-gray-700 h-[80vh] overflow-y-auto p-8 font-roboto">
                <p class="text-gray-500">Aquí se mostrarán mensajes de estado durante la generación.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Añadir Estudiante con NEE -->
<div id="nee_student_modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Añadir Estudiante con Necesidad Especial</h3>
        <div class="space-y-4">
            <div>
                <label for="modal_student_name" class="label-style">Nombre del Estudiante</label>
                <input type="text" id="modal_student_name" class="input-style" placeholder="Ej: Juan Pérez">
            </div>
            <div>
                <label for="modal_nee_type" class="label-style">Necesidad Especial</label>
                <select id="modal_nee_type" class="input-style">
                    <option value="" disabled selected>Selecciona una necesidad</option>
                    <option value="TDAH">TDAH</option>
                    <option value="Dislexia">Dislexia</option>
                    <option value="Autismo">Autismo</option>
                    <option value="Discapacidad Visual">Discapacidad Visual</option>
                    <option value="Discapacidad Auditiva">Discapacidad Auditiva</option>
                    <option value="Discapacidad Motriz">Discapacidad Motriz</option>
                    <option value="otra">Otra (especificar)</option>
                </select>
            </div>
            <div id="modal_other_nee_container" class="hidden">
                <label for="modal_other_nee_text" class="label-style">Especifica la Necesidad Especial</label>
                <textarea id="modal_other_nee_text" rows="2" class="input-style" placeholder="Ej: Dificultad de aprendizaje en matemáticas"></textarea>
            </div>
        </div>
        <div class="flex justify-end space-x-4 mt-6">
            <button type="button" id="modal_cancel_btn" class="px-4 py-2 bg-gray-300 text-gray-800 font-medium rounded-md hover:bg-gray-400 transition-colors">Cancelar</button>
            <button type="button" id="modal_add_btn" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">Añadir</button>
        </div>
    </div>
</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
    details > summary {
        list-style: none;
    }
    details > summary::-webkit-details-marker {
        display: none;
    }
    /* Mejora para móviles */
    @media (max-width: 767px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .grid-cols-1.md\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
        .md\:col-span-2, .lg\:col-span-3 {
            grid-column: span 1 / span 1;
        }
        .chart-container {
            height: 250px; /* Ajustar altura de gráficos en móviles */
            max-height: 300px;
        }
        .text-4xl {
            font-size: 2.25rem; /* Ajustar tamaño de encabezado principal */
        }
        .text-2xl {
            font-size: 1.5rem; /* Ajustar tamaño de encabezados de sección */
        }
    }
</style>
<script src="/js/main.js"></script>
