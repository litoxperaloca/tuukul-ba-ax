<?php
// app/views/admin/formularios_dinamicos/edit.php (Con Constructor de Formularios)

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
        <a href="/admin/formularios-dinamicos" class="text-indigo-600 hover:text-indigo-800 mr-4">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-4xl font-bold text-gray-800">Editar Formulario Dinámico: <?= htmlspecialchars($formulario['nombre']) ?></h1>
    </div>

    <div class="bg-white p-8 rounded-lg card-shadow max-w-5xl mx-auto">
        <form id="dynamic-form-builder-form" action="/admin/formularios-dinamicos/process-update/<?= htmlspecialchars($formulario['id']) ?>" method="POST" class="space-y-6">
            <fieldset class="border p-4 rounded-md">
                <legend class="text-lg font-semibold px-2">Información del Formulario</legend>
                <div>
                    <label for="id_curso" class="label-style">Curso Asociado</label>
                    <select name="id_curso" id="id_curso" required class="input-style">
                        <option value="" disabled>Selecciona un curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= htmlspecialchars($curso['id']) ?>" <?= ($curso['id'] == $formulario['id_curso']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($curso['nivel']) ?> -
                                <?= htmlspecialchars($curso['grado']) ?> -
                                <?= htmlspecialchars($curso['asignatura']) ?>
                                (Plan: <?= htmlspecialchars($curso['nombre_plan'] ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mt-4">
                    <label for="nombre" class="label-style">Nombre del Formulario</label>
                    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($formulario['nombre']) ?>" required class="input-style" placeholder="Ej: Formulario Planeación EF Primaria 3°">
                </div>
            </fieldset>

            <fieldset class="border p-4 rounded-md">
                <legend class="text-lg font-semibold px-2">Constructor de Campos del Formulario</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Columna para añadir nuevos campos -->
                    <div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-700">Añadir Nuevo Campo</h3>
                        <div class="space-y-4 p-4 border rounded-md bg-gray-50">
                            <div>
                                <label for="field_type" class="label-style">Tipo de Campo</label>
                                <select id="field_type" class="input-style">
                                    <option value="text">Texto Corto</option>
                                    <option value="textarea">Texto Largo</option>
                                    <option value="number">Número</option>
                                    <option value="checkbox">Casilla de Verificación</option>
                                    <option value="select">Selección (Dropdown)</option>
                                </select>
                            </div>
                            <div>
                                <label for="field_label" class="label-style">Etiqueta del Campo</label>
                                <input type="text" id="field_label" class="input-style" placeholder="Ej: Objetivo de la sesión">
                            </div>
                            <div>
                                <label for="field_name" class="label-style">Nombre del Campo (único, sin espacios)</label>
                                <input type="text" id="field_name" class="input-style" placeholder="Ej: objetivo_sesion">
                            </div>
                            <div id="field_options_container" class="hidden">
                                <label for="field_options" class="label-style">Opciones (separadas por coma)</label>
                                <input type="text" id="field_options" class="input-style" placeholder="Ej: Opción 1, Opción 2">
                                <p class="text-xs text-gray-500 mt-1">Solo para campos de tipo Selección.</p>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="field_required" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-200">
                                <label for="field_required" class="ml-2 text-sm text-gray-700">Obligatorio</label>
                            </div>
                            <button type="button" id="add_field_btn" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i> Añadir Campo
                            </button>
                        </div>
                    </div>

                    <!-- Columna para previsualizar y gestionar campos añadidos -->
                    <div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-700">Campos del Formulario</h3>
                        <div id="form_fields_preview" class="space-y-4 p-4 border rounded-md bg-gray-50 min-h-[300px]">
                            <p class="text-gray-500 text-center">Añade campos para empezar a construir tu formulario.</p>
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- Campo oculto para el JSON final -->
            <input type="hidden" name="schema_json_output" id="schema_json_output">

            <div class="pt-4 text-right">
                <button type="submit" id="save_form_btn" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Formulario Dinámico
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .input-style { width: 100%; margin-top: 0.25rem; display: block; border-radius: 0.375rem; border-color: #D1D5DB; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); padding: 0.625rem 0.75rem; }
    .label-style { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fieldTypeSelect = document.getElementById('field_type');
        const fieldLabelInput = document.getElementById('field_label');
        const fieldNameInput = document.getElementById('field_name');
        const fieldRequiredCheckbox = document.getElementById('field_required');
        const fieldOptionsContainer = document.getElementById('field_options_container');
        const fieldOptionsInput = document.getElementById('field_options');
        const addFieldBtn = document.getElementById('add_field_btn');
        const formFieldsPreview = document.getElementById('form_fields_preview');
        const schemaJsonOutput = document.getElementById('schema_json_output');
        const dynamicFormBuilderForm = document.getElementById('dynamic-form-builder-form');

        let formSchema = { fields: [] }; // Estructura del JSON del formulario

        // Función para renderizar los campos en la previsualización
        function renderFieldsPreview() {
            formFieldsPreview.innerHTML = ''; // Limpiar previsualización
            if (formSchema.fields.length === 0) {
                formFieldsPreview.innerHTML = '<p class="text-gray-500 text-center py-4">Añade campos para empezar a construir tu formulario.</p>';
                return;
            }

            formSchema.fields.forEach((field, index) => {
                const fieldDiv = document.createElement('div');
                fieldDiv.className = 'p-3 border rounded-md bg-white flex items-center justify-between shadow-sm';
                fieldDiv.innerHTML = `
                    <div>
                        <p class="font-semibold text-gray-800">${field.label} <span class="text-sm text-gray-500">(${field.type})</span></p>
                        <p class="text-xs text-gray-600">Nombre: ${field.name} ${field.required ? '(Obligatorio)' : ''}</p>
                        ${field.options ? `<p class="text-xs text-gray-600">Opciones: ${field.options.join(', ')}</p>` : ''}
                    </div>
                    <div>
                        <button type="button" class="edit-field-btn text-blue-500 hover:text-blue-700 mr-2" data-index="${index}" title="Editar Campo">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="delete-field-btn text-red-500 hover:text-red-700" data-index="${index}" title="Eliminar Campo">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
                formFieldsPreview.appendChild(fieldDiv);
            });

            // Añadir event listeners a los nuevos botones de eliminar
            document.querySelectorAll('.delete-field-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    formSchema.fields.splice(index, 1); // Eliminar el campo del array
                    renderFieldsPreview(); // Volver a renderizar
                });
            });

            // Añadir event listeners a los nuevos botones de editar
            document.querySelectorAll('.edit-field-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    const fieldToEdit = formSchema.fields[index];

                    // Cargar datos del campo a los inputs del constructor
                    fieldTypeSelect.value = fieldToEdit.type;
                    fieldLabelInput.value = fieldToEdit.label;
                    fieldNameInput.value = fieldToEdit.name;
                    fieldRequiredCheckbox.checked = fieldToEdit.required || false;
                    if (fieldToEdit.options) {
                        fieldOptionsInput.value = fieldToEdit.options.join(', ');
                        fieldOptionsContainer.classList.remove('hidden');
                    } else {
                        fieldOptionsInput.value = '';
                        fieldOptionsContainer.classList.add('hidden');
                    }

                    // Cambiar el botón de "Añadir" a "Actualizar"
                    addFieldBtn.textContent = 'Actualizar Campo';
                    addFieldBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    addFieldBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    addFieldBtn.dataset.editingIndex = index; // Guardar el índice del campo que se está editando
                });
            });

            // Actualizar el campo oculto con el JSON
            schemaJsonOutput.value = JSON.stringify(formSchema, null, 2);
        }

        // Mostrar/ocultar opciones para campos de selección
        fieldTypeSelect.addEventListener('change', function() {
            if (this.value === 'select') {
                fieldOptionsContainer.classList.remove('hidden');
            } else {
                fieldOptionsContainer.classList.add('hidden');
                fieldOptionsInput.value = ''; // Limpiar opciones si no es un select
            }
        });

        // Lógica para añadir/actualizar campo
        addFieldBtn.addEventListener('click', function() {
            const type = fieldTypeSelect.value;
            const label = fieldLabelInput.value.trim();
            const name = fieldNameInput.value.trim().replace(/\s+/g, '_').toLowerCase(); // Limpiar nombre para usar como 'name'
            const required = fieldRequiredCheckbox.checked;
            let options = null;

            if (type === 'select') {
                options = fieldOptionsInput.value.split(',').map(opt => opt.trim()).filter(opt => opt !== '');
                if (options.length === 0) {
                    alert('Por favor, introduce al menos una opción para el campo de selección.');
                    return;
                }
            }

            if (!label || !name) {
                alert('La etiqueta y el nombre del campo son obligatorios.');
                return;
            }

            const newField = { type, label, name, required };
            if (options) {
                newField.options = options;
            }

            const editingIndex = addFieldBtn.dataset.editingIndex;
            if (editingIndex !== undefined) {
                // Actualizar campo existente
                formSchema.fields[parseInt(editingIndex)] = newField;
                addFieldBtn.textContent = 'Añadir Campo';
                addFieldBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                addFieldBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                delete addFieldBtn.dataset.editingIndex;
            } else {
                // Añadir nuevo campo
                // Verificar si el nombre del campo ya existe
                const nameExists = formSchema.fields.some(field => field.name === name);
                if (nameExists) {
                    alert('Ya existe un campo con este nombre. Por favor, usa un nombre único.');
                    return;
                }
                formSchema.fields.push(newField);
            }

            // Limpiar formulario de añadir campo
            fieldLabelInput.value = '';
            fieldNameInput.value = '';
            fieldRequiredCheckbox.checked = false;
            fieldOptionsInput.value = '';
            fieldOptionsContainer.classList.add('hidden');
            fieldTypeSelect.value = 'text'; // Resetear a tipo de texto por defecto

            renderFieldsPreview();
        });

        // Al enviar el formulario principal, asegúrate de que el JSON esté actualizado
        dynamicFormBuilderForm.addEventListener('submit', function() {
            schemaJsonOutput.value = JSON.stringify(formSchema, null, 2);
        });

        // Cargar esquema inicial si existe (para edición)
        const initialSchemaJson = `<?= $schema_json_initial ?? '{"fields": []}' ?>`;
        try {
            formSchema = JSON.parse(initialSchemaJson);
            renderFieldsPreview();
        } catch (e) {
            console.error("Error al parsear el esquema JSON inicial:", e);
            alert("Error al cargar el esquema JSON existente. Por favor, verifica el formato.");
        }
    });
</script>
