document.addEventListener('DOMContentLoaded', function() {
    const planeacionForm = document.getElementById('planeacion-form');
    if (!planeacionForm) return;

    const formSections = document.getElementById('form-sections');
    const summarySection = document.getElementById('summary-section');
    const summaryContainer = document.getElementById('summary-container');
    const showSummaryBtnContainer = document.getElementById('show-summary-btn-container');
    const showSummaryBtn = document.getElementById('show-summary-btn');
    const backToEditBtn = document.getElementById('back-to-edit-btn');
    const generateBtn = document.getElementById('generate-btn');
    const loader = document.getElementById('loader');
    const generationMessageContainer = document.getElementById('generation-message');

    const docenteNombreCompletoInput = document.getElementById('docente_nombre_completo');
    const docentePaisInput = document.getElementById('docente_pais');

    const paisSelect = document.getElementById('pais_id');
    const centroEducativoSelect = document.getElementById('centro_educativo_id');
    const planEstudioSelect = document.getElementById('plan_estudio_id');
    const cursoSelect = document.getElementById('curso_id');
    const contenidosCheckboxContainer = document.getElementById('contenidos_checkbox_container');
    const pdaDisplayContainer = document.getElementById('pda_display_container');
    const pdaDisplay = document.getElementById('pda_display');
    const faseInput = document.getElementById('fase');
    const escuelaNombreInput = document.getElementById('escuela_nombre');
    const escuelaCctInput = document.getElementById('escuela_cct');
    const ejesArticuladoresContainer = document.getElementById('ejes_articuladores_container');

    const dynamicFormDetails = document.getElementById('dynamic_form_details');
    const dynamicFormFieldsContainer = document.getElementById('dynamic_form_container');
    const dynamicFormDataJsonInput = document.getElementById('dynamic_form_data_json');

    const addNeeStudentBtn = document.getElementById('add_nee_student_btn');
    const neeStudentModal = document.getElementById('nee_student_modal');
    const modalStudentName = document.getElementById('modal_student_name');
    const modalNeeType = document.getElementById('modal_nee_type');
    const modalOtherNeeContainer = document.getElementById('modal_other_nee_container');
    const modalOtherNeeText = document.getElementById('modal_other_nee_text');
    const modalCancelBtn = document.getElementById('modal_cancel_btn');
    const modalAddBtn = document.getElementById('modal_add_btn');
    const neeStudentsTbody = document.getElementById('nee_students_tbody');
    const alumnosNeeJsonInput = document.getElementById('alumnos_nee_json');

    let neeStudents = [];

    function resetAndDisableSelect(selectElement, defaultText) {
        selectElement.innerHTML = `<option value="" disabled selected>${defaultText}</option>`;
        selectElement.disabled = true;
        selectElement.classList.remove('border-red-500');
    }

    function showValidationMessage(inputElement, message) {
        let errorSpan = inputElement.nextElementSibling;
        if (errorSpan && errorSpan.classList.contains('error-message')) {
            errorSpan.textContent = message;
        } else {
            errorSpan = document.createElement('span');
            errorSpan.className = 'error-message text-red-500 text-sm mt-1';
            errorSpan.textContent = message;
            inputElement.parentNode.insertBefore(errorSpan, inputElement.nextSibling);
        }
        inputElement.classList.add('border-red-500');
    }

    function clearValidationMessage(inputElement) {
        const errorSpan = inputElement.nextElementSibling;
        if (errorSpan && errorSpan.classList.contains('error-message')) {
            errorSpan.remove();
        }
        inputElement.classList.remove('border-red-500');
    }

    function validateAllFields() {
        let allValid = true;
        const requiredInputs = planeacionForm.querySelectorAll('[required]');

        // Validate read-only teacher profile fields
        if (!docenteNombreCompletoInput.value.trim() || !docentePaisInput.value.trim() || docentePaisInput.value === 'No especificado') {
            alert('Por favor, completa tu Nombre Completo y País en tu perfil para poder generar planeaciones.');
            return false;
        }


        requiredInputs.forEach(input => {
            clearValidationMessage(input);

            if (input.type === 'checkbox') {
                // Checkbox validation is handled specifically for content below
            } else if (input.tagName === 'SELECT') {
                if (!input.value || input.value === "") {
                    showValidationMessage(input, 'Selección obligatoria.');
                    allValid = false;
                }
            } else if (!input.value.trim()) {
                showValidationMessage(input, 'Campo obligatorio.');
                allValid = false;
            }
        });

        // Specific validation for content checkboxes
        const selectedContents = contenidosCheckboxContainer.querySelectorAll('input[type="checkbox"][name="contenidos_curriculares_seleccionados[]"]:checked');
        if (selectedContents.length === 0) {
            showValidationMessage(contenidosCheckboxContainer, 'Debes seleccionar al menos un Contenido Curricular.');
            allValid = false;
        } else {
            clearValidationMessage(contenidosCheckboxContainer);
        }
        
        // Dynamic form fields validation
        if (dynamicFormDetails && !dynamicFormDetails.classList.contains('hidden')) { // Check if visible
            const dynamicRequiredInputs = dynamicFormFieldsContainer.querySelectorAll('[required]');
            dynamicRequiredInputs.forEach(input => {
                clearValidationMessage(input);
                if (input.type === 'checkbox') {
                    if (!input.checked) {
                        showValidationMessage(input, 'Campo obligatorio.');
                        allValid = false;
                    }
                } else if (!input.value.trim()) {
                    showValidationMessage(input, 'Campo obligatorio.');
                    allValid = false;
                }
            });
        }
        
        /*const confirmCheckbox = document.getElementById('confirm_generate');
        if (!confirmCheckbox.checked) {
            showValidationMessage(confirmCheckbox, 'Debes confirmar para generar la planeación.');
            allValid = false;
        } else {
            clearValidationMessage(confirmCheckbox);
        }*/

        return allValid;
    }

    async function handlePaisChangeLogic(paisId) {
        resetAndDisableSelect(centroEducativoSelect, 'Cargando centros educativos...');
        resetAndDisableSelect(planEstudioSelect, 'Primero selecciona un centro educativo');
        resetAndDisableSelect(cursoSelect, 'Primero selecciona un plan de estudio');
        contenidosCheckboxContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar contenidos.</p>';
        pdaDisplayContainer.classList.add('hidden');
        pdaDisplay.innerHTML = '';
        faseInput.value = '';
        escuelaNombreInput.value = '';
        escuelaCctInput.value = '';
        ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>';
        clearDynamicForm();

        if (paisId) {
            try {
                const response = await fetch(`/api/centros-educativos?pais_id=${paisId}`);
                const data = await response.json();
                centroEducativoSelect.innerHTML = '<option value="" disabled selected>Selecciona un centro educativo</option>';
                if (data.success && data.centros.length > 0) {
                    data.centros.forEach(centro => {
                        const option = document.createElement('option');
                        option.value = centro.id;
                        option.textContent = centro.nombre;
                        option.dataset.cct = centro.cct || '';
                        centroEducativoSelect.appendChild(option);
                    });
                    centroEducativoSelect.disabled = false;
                } else {
                    centroEducativoSelect.innerHTML = '<option value="" disabled selected>No hay centros para este país</option>';
                }
            } catch (error) {
                console.error('Error al cargar centros educativos:', error);
                centroEducativoSelect.innerHTML = '<option value="" disabled selected>Error al cargar centros</option>';
            }
        }
    }

    async function handleCentroEducativoChangeLogic(centroId) {
        resetAndDisableSelect(planEstudioSelect, 'Cargando planes de estudio...');
        resetAndDisableSelect(cursoSelect, 'Primero selecciona un plan de estudio');
        contenidosCheckboxContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar contenidos.</p>';
        pdaDisplayContainer.classList.add('hidden');
        pdaDisplay.innerHTML = '';
        faseInput.value = '';
        escuelaNombreInput.value = '';
        escuelaCctInput.value = '';
        ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>';
        clearDynamicForm();

        if (centroId) {
            const selectedCentroOption = centroEducativoSelect.options[centroEducativoSelect.selectedIndex];
            escuelaNombreInput.value = selectedCentroOption.textContent;
            escuelaCctInput.value = selectedCentroOption.dataset.cct || '';

            try {
                const response = await fetch(`/api/planes-estudio?centro_educativo_id=${centroId}`);
                const data = await response.json();
                planEstudioSelect.innerHTML = '<option value="" disabled selected>Selecciona un plan de estudio</option>';
                if (data.success && data.planes.length > 0) {
                    data.planes.forEach(plan => {
                        const option = document.createElement('option');
                        option.value = plan.id;
                        option.textContent = `${plan.nombre_plan} (${plan.anio_vigencia_inicio}${plan.anio_vigencia_fin ? '-' + plan.anio_vigencia_fin : ''})`;
                        planEstudioSelect.appendChild(option);
                    });
                    planEstudioSelect.disabled = false;
                } else {
                    planEstudioSelect.innerHTML = '<option value="" disabled selected>No hay planes para este centro</option>';
                }
            } catch (error) {
                console.error('Error al cargar planes de estudio:', error);
                planEstudioSelect.innerHTML = '<option value="" disabled selected>Error al cargar planes</option>';
            }
        }
    }

    async function handlePlanEstudioChangeLogic(planId) {
        resetAndDisableSelect(cursoSelect, 'Cargando cursos...');
        contenidosCheckboxContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar contenidos.</p>';
        pdaDisplayContainer.classList.add('hidden');
        pdaDisplay.innerHTML = '';
        faseInput.value = '';
        ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>';
        clearDynamicForm();

        if (planId) {
            try {
                const response = await fetch(`/api/cursos?plan_estudio_id=${planId}`);
                const data = await response.json();
                cursoSelect.innerHTML = '<option value="" disabled selected>Selecciona un curso</option>';
                if (data.success && data.cursos.length > 0) {
                    data.cursos.forEach(curso => {
                        const option = document.createElement('option');
                        option.value = curso.id;
                        option.textContent = `${curso.nivel} - ${curso.grado} - ${curso.asignatura}`;
                        option.dataset.fase = curso.fase || '';
                        cursoSelect.appendChild(option);
                    });
                    cursoSelect.disabled = false;
                } else {
                    cursoSelect.innerHTML = '<option value="" disabled selected>No hay cursos para este plan</option>';
                }
            } catch (error) {
                console.error('Error al cargar cursos:', error);
                cursoSelect.innerHTML = '<option value="" disabled selected>Error al cargar cursos</option>';
            }
        }
    }

    async function handleCursoChangeLogic(cursoId) {
        const selectedOption = cursoSelect.options[cursoSelect.selectedIndex];
        faseInput.value = selectedOption.dataset.fase || '';
        
        contenidosCheckboxContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Cargando contenidos y ejes...</p>';
        pdaDisplayContainer.classList.add('hidden');
        pdaDisplay.innerHTML = '';
        ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">Selecciona un curso para cargar los ejes articuladores.</p>';
        clearDynamicForm(); // Clear and hide dynamic form initially

        if (cursoId) {
            try {
                const contenidosResponse = await fetch(`/api/contenidos-curriculares?curso_id=${cursoId}&tipo=contenido`);
                const contenidosData = await contenidosResponse.json();
                contenidosCheckboxContainer.innerHTML = '';
                if (contenidosData.success && contenidosData.contenidos.length > 0) {
                    contenidosData.contenidos.forEach(item => {
                        const div = document.createElement('div');
                        div.innerHTML = `
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="contenidos_curriculares_seleccionados[]" value="${item.nombre_contenido}||${item.pda_descripcion}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 content-checkbox">
                                <span class="ml-2 text-sm text-gray-700">${item.nombre_contenido}</span>
                            </label>
                        `;
                        contenidosCheckboxContainer.appendChild(div);
                    });
                    contenidosCheckboxContainer.querySelectorAll('.content-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', updatePdaDisplay);
                    });
                } else {
                    contenidosCheckboxContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-full">No hay contenidos para este curso.</p>';
                }

                const ejesResponse = await fetch(`/api/contenidos-curriculares?curso_id=${cursoId}&tipo=eje_articulador`);
                const ejesData = await ejesResponse.json();
                if (ejesData.success && ejesData.contenidos.length > 0) {
                    ejesArticuladoresContainer.innerHTML = '';
                    ejesData.contenidos.forEach(eje => {
                        const div = document.createElement('div');
                        div.innerHTML = `
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="ejes_articuladores[]" value="${eje.nombre_contenido}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">${eje.nombre_contenido}</span>
                            </label>
                        `;
                        ejesArticuladoresContainer.appendChild(div);
                    });
                } else {
                    ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-gray-500">No hay ejes articuladores definidos para este curso.</p>';
                }

                const formResponse = await fetch(`/api/formulario-dinamico-by-curso?curso_id=${cursoId}`);
                const formData = await formResponse.json();
                if (formData.success && formData.formulario && formData.formulario.schema_json && formData.formulario.schema_json.fields) {
                    renderDynamicForm(formData.formulario.schema_json.fields);
                    dynamicFormDetails.classList.remove('hidden'); // Show the details block
                } else {
                    clearDynamicForm(); // Hide if no form
                }

            } catch (error) {
                console.error('Error al cargar datos del curso:', error);
                contenidosCheckboxContainer.innerHTML = '<p class="text-sm text-red-500 col-span-full">Error al cargar contenidos.</p>';
                ejesArticuladoresContainer.innerHTML = '<p class="text-sm text-red-500">Error al cargar ejes articuladores.</p>';
                clearDynamicForm();
            }
        }
    }

    function updatePdaDisplay() {
        const selectedContents = contenidosCheckboxContainer.querySelectorAll('input[type="checkbox"][name="contenidos_curriculares_seleccionados[]"]:checked');
        pdaDisplay.innerHTML = '';
        if (selectedContents.length > 0) {
            selectedContents.forEach(checkbox => {
                const parts = checkbox.value.split('||');
                if (parts.length > 1) {
                    const contentName = parts[0];
                    const pdaText = parts[1];
                    const pdaItem = document.createElement('p');
                    pdaItem.innerHTML = `<span class="font-semibold">${contentName}:</span> ${pdaText}`;
                    pdaDisplay.appendChild(pdaItem);
                }
            });
            pdaDisplayContainer.classList.remove('hidden');
        } else {
            pdaDisplayContainer.classList.add('hidden');
        }
    }

    planeacionForm.addEventListener('change', async function(event) {
        const targetId = event.target.id;
        const targetValue = event.target.value;

        switch (targetId) {
            case 'pais_id':
                await handlePaisChangeLogic(targetValue);
                break;
            case 'centro_educativo_id':
                await handleCentroEducativoChangeLogic(targetValue);
                break;
            case 'plan_estudio_id':
                await handlePlanEstudioChangeLogic(targetValue);
                break;
            case 'curso_id':
                await handleCursoChangeLogic(targetValue);
                break;
            case 'modal_nee_type':
                if (targetValue === 'otra') {
                    modalOtherNeeContainer.classList.remove('hidden');
                    modalOtherNeeText.setAttribute('required', 'required');
                } else {
                    modalOtherNeeContainer.classList.add('hidden');
                    modalOtherNeeText.removeAttribute('required');
                    modalOtherNeeText.value = '';
                }
                break;
        }
    });

    function renderDynamicForm(fields) {
        dynamicFormFieldsContainer.innerHTML = '';
        if (!fields || fields.length === 0) {
            dynamicFormDetails.classList.add('hidden'); // Hide the details block
            return;
        }
        dynamicFormDetails.classList.remove('hidden'); // Show the details block

        fields.forEach(field => {
            const fieldWrapper = document.createElement('div');
            fieldWrapper.className = 'mb-4';

            const label = document.createElement('label');
            label.htmlFor = `dynamic_field_${field.name}`;
            label.className = 'label-style';
            label.textContent = field.label;
            if (field.required) {
                label.textContent += ' *';
            }
            fieldWrapper.appendChild(label);

            let inputElement;
            switch (field.type) {
                case 'text':
                case 'number':
                    inputElement = document.createElement('input');
                    inputElement.type = field.type;
                    inputElement.id = `dynamic_field_${field.name}`;
                    inputElement.name = `dynamic_field_${field.name}`;
                    inputElement.className = 'input-style';
                    if (field.required) inputElement.required = true;
                    if (field.type === 'number') {
                        inputElement.step = 'any';
                    }
                    break;
                case 'textarea':
                    inputElement = document.createElement('textarea');
                    inputElement.id = `dynamic_field_${field.name}`;
                    inputElement.name = `dynamic_field_${field.name}`;
                    inputElement.className = 'input-style';
                    inputElement.rows = 3;
                    if (field.required) inputElement.required = true;
                    break;
                case 'checkbox':
                    inputElement = document.createElement('input');
                    inputElement.type = 'checkbox';
                    inputElement.id = `dynamic_field_${field.name}`;
                    inputElement.name = `dynamic_field_${field.name}`;
                    inputElement.className = 'rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50';
                    const checkboxLabel = document.createElement('label');
                    checkboxLabel.htmlFor = `dynamic_field_${field.name}`;
                    checkboxLabel.className = 'ml-2 text-sm text-gray-700 inline-flex items-center';
                    checkboxLabel.textContent = field.label; 
                    
                    const checkboxDiv = document.createElement('div');
                    checkboxDiv.className = 'flex items-center';
                    checkboxDiv.appendChild(inputElement);
                    checkboxDiv.appendChild(checkboxLabel);
                    fieldWrapper.removeChild(label); 
                    fieldWrapper.appendChild(checkboxDiv);
                    break;
                case 'select':
                    inputElement = document.createElement('select');
                    inputElement.id = `dynamic_field_${field.name}`;
                    inputElement.name = `dynamic_field_${field.name}`;
                    inputElement.className = 'input-style';
                    if (field.required) inputElement.required = true;
                    
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = `Selecciona ${field.label.toLowerCase()}`;
                    defaultOption.disabled = true;
                    defaultOption.selected = true;
                    inputElement.appendChild(defaultOption);

                    if (field.options && Array.isArray(field.options)) {
                        field.options.forEach(optionText => {
                            const option = document.createElement('option');
                            option.value = optionText;
                            option.textContent = optionText;
                            inputElement.appendChild(option);
                        });
                    }
                    break;
                default:
                    console.warn(`Tipo de campo desconocido: ${field.type}`);
                    return;
            }

            if (field.type !== 'checkbox') {
                fieldWrapper.appendChild(inputElement);
            }
            dynamicFormFieldsContainer.appendChild(fieldWrapper);
        });
    }

    function clearDynamicForm() {
        if (dynamicFormFieldsContainer) dynamicFormFieldsContainer.innerHTML = '<p class="text-gray-500 text-center">Selecciona un curso para cargar información específica.</p>';
        if (dynamicFormDetails) dynamicFormDetails.classList.add('hidden'); // Hide the details block
        if (dynamicFormDataJsonInput) dynamicFormDataJsonInput.value = '';
    }

    function updateSummary() {
        const summaryContent = document.getElementById('summary_content');
        let summaryHtml = '<h4 class="font-bold text-gray-800 mb-2">Resumen de tu Planeación:</h4>';
        const formData = new FormData(planeacionForm);

        const allFormData = {};
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('dynamic_field_')) {
                const originalName = key.replace('dynamic_field_', '');
                const inputElement = document.getElementById(key);
                if (inputElement && inputElement.type === 'checkbox') {
                    allFormData[originalName] = inputElement.checked ? 'Sí' : 'No';
                } else {
                    allFormData[originalName] = value;
                }
            } else if (key === 'contenidos_curriculares_seleccionados[]' || key === 'ejes_articuladores[]') {
                if (!allFormData[key]) {
                    allFormData[key] = [];
                }
                allFormData[key].push(value);
            } else {
                allFormData[key] = value;
            }
        }

        summaryHtml += `<p><span class="font-semibold">Docente:</span> ${docenteNombreCompletoInput.value || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">País Docente:</span> ${docentePaisInput.value || 'N/A'}</p>`;
        
        summaryHtml += `<p><span class="font-semibold">Escuela:</span> ${escuelaNombreInput.value || 'N/A'} (CCT: ${escuelaCctInput.value || 'N/A'})</p>`;

        summaryHtml += `<p class="mt-4"><span class="font-semibold">País Currículo:</span> ${paisSelect.options[paisSelect.selectedIndex]?.text || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Plan de Estudio:</span> ${planEstudioSelect.options[planEstudioSelect.selectedIndex]?.text || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Curso:</span> ${cursoSelect.options[cursoSelect.selectedIndex]?.text || 'N/A'} (Fase: ${faseInput.value || 'N/A'})</p>`;
        
        const selectedContentsForSummary = Array.from(contenidosCheckboxContainer.querySelectorAll('input[type="checkbox"][name="contenidos_curriculares_seleccionados[]"]:checked')).map(cb => cb.value.split('||')[0]);
        summaryHtml += `<p><span class="font-semibold">Contenidos Curriculares:</span> ${selectedContentsForSummary.join(', ') || 'No seleccionados'}</p>`;
        summaryHtml += `<p><span class="font-semibold">PDAs Seleccionados:</span></p><ul class="list-disc list-inside ml-4">${pdaDisplay.innerHTML}</ul>`;
        summaryHtml += `<p><span class="font-semibold">Ejes Articuladores:</span> ${allFormData['ejes_articuladores[]']?.join(', ') || 'No seleccionados'}</p>`;

        summaryHtml += `<p class="mt-4"><span class="font-semibold">Sugerencias:</span> ${allFormData['sugerencias_sesiones'] || 'Ninguna'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Observaciones del Docente:</span> ${allFormData['observaciones_docente'] || 'Ninguna'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Materiales:</span> ${allFormData['materiales'] || 'No especificados'}</p>`;

        summaryHtml += `<p class="mt-4"><span class="font-semibold">Estudiantes con NEE:</span></p>`;
        if (neeStudents.length > 0) {
            summaryHtml += `<ul class="list-disc list-inside ml-4">`;
            neeStudents.forEach(student => {
                summaryHtml += `<li>${student.name} (${student.neeType})</li>`;
            });
            summaryHtml += `</ul>`;
        } else {
            summaryHtml += `<p class="ml-4">Ninguno especificado.</p>`;
        }

        const dynamicFieldsDataForSummary = {};
        dynamicFormFieldsContainer.querySelectorAll('input, textarea, select').forEach(input => {
            if (input.name.startsWith('dynamic_field_')) {
                const originalName = input.name.replace('dynamic_field_', '');
                if (input.type === 'checkbox') {
                    dynamicFieldsDataForSummary[originalName] = input.checked ? 'Sí' : 'No';
                } else {
                    dynamicFieldsDataForSummary[originalName] = input.value;
                }
            }
        });

        if (Object.keys(dynamicFieldsDataForSummary).length > 0) {
            summaryHtml += `<p class="mt-4 font-semibold">Información Específica del Curso:</p>`;
            for (const key in dynamicFieldsDataForSummary) {
                summaryHtml += `<p class="ml-4"><span class="font-semibold">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</span> ${dynamicFieldsDataForSummary[key]}</p>`;
            }
        }

        summaryHtml += `<p class="mt-4"><span class="font-semibold">Número de Sesiones:</span> ${allFormData['num_sesiones'] || 'N/A'}</p>`;
        summaryHtml += `<p><span class="font-semibold">Duración por Sesión:</span> ${allFormData['duracion_sesion'] || 'N/A'} minutos</p>`;

        summaryContent.innerHTML = summaryHtml;
    }

    planeacionForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!validateAllFields()) {
            return;
        }
        const confirmCheckbox = document.getElementById('confirm_generate');
        if (!confirmCheckbox.checked) {
            showValidationMessage(confirmCheckbox, 'Debes confirmar para generar la planeación.');
            return;
        } else {
            clearValidationMessage(confirmCheckbox);
        }
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generando...';
        loader.classList.remove('hidden');
        generationMessageContainer.innerHTML = '<p class="text-gray-600 font-roboto">Iniciando generación de planeación...</p>';

        const formData = new FormData(planeacionForm);

        // Add hidden school name and CCT to formData
        formData.append('escuela_nombre', escuelaNombreInput.value);
        formData.append('escuela_cct', escuelaCctInput.value);

        const dynamicFieldsData = {};
        dynamicFormFieldsContainer.querySelectorAll('input, textarea, select').forEach(input => {
            if (input.name.startsWith('dynamic_field_')) {
                const originalName = input.name.replace('dynamic_field_', '');
                if (input.type === 'checkbox') {
                    dynamicFieldsData[originalName] = input.checked;
                } else {
                    dynamicFieldsData[originalName] = input.value;
                }
            }
        });
        formData.append('dynamic_form_data_json', JSON.stringify(dynamicFieldsData));

        const selectedCursoId = cursoSelect.value;
        if (selectedCursoId) {
            formData.append('id_curso', selectedCursoId);
        }

        formData.append('alumnos_nee_json', JSON.stringify(neeStudents));

        try {
            const response = await fetch('/api/generate', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const errorResult = await response.json().catch(() => ({ message: `Error del servidor: ${response.status}` }));
                throw new Error(errorResult.message);
            }

            const result = await response.json();

            if (result.success) {
                generationMessageContainer.innerHTML = '<p class="text-green-600 font-semibold">¡Planeación generada con éxito! Redirigiendo...</p>';
                window.location.href = `/dashboard/view/${result.planeacion_id}`;
            } else {
                throw new Error(result.message || 'Ocurrió un error desconocido al generar la planeación.');
            }
        } catch (error) {
            generationMessageContainer.innerHTML = `<p class="text-red-600 font-semibold">Error al generar: ${error.message}</p>`;
        } finally {
            loader.classList.add('hidden');
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> Entiendo, Genera la Planeación';
        }
    });

    // NEE Students Modal Logic
    addNeeStudentBtn.addEventListener('click', function() {
        neeStudentModal.classList.remove('hidden');
        modalStudentName.value = '';
        modalNeeType.value = '';
        modalOtherNeeContainer.classList.add('hidden');
        modalOtherNeeText.value = '';
        modalOtherNeeText.removeAttribute('required');
    });

    modalCancelBtn.addEventListener('click', function() {
        neeStudentModal.classList.add('hidden');
    });

    modalAddBtn.addEventListener('click', function() {
        const studentName = modalStudentName.value.trim();
        let neeType = modalNeeType.value;
        let isValid = true;

        if (!studentName) {
            showValidationMessage(modalStudentName, 'El nombre del estudiante es obligatorio.');
            isValid = false;
        } else {
            clearValidationMessage(modalStudentName);
        }

        if (!neeType) {
            showValidationMessage(modalNeeType, 'Debes seleccionar una necesidad especial.');
            isValid = false;
        } else {
            clearValidationMessage(modalNeeType);
        }

        if (neeType === 'otra') {
            if (!modalOtherNeeText.value.trim()) {
                showValidationMessage(modalOtherNeeText, 'Debes especificar la necesidad especial.');
                isValid = false;
            } else {
                clearValidationMessage(modalOtherNeeText);
                neeType = modalOtherNeeText.value.trim();
            }
        }

        if (!isValid) {
            return;
        }

        neeStudents.push({ name: studentName, neeType: neeType });
        renderNeeStudentsTable();
        neeStudentModal.classList.add('hidden');
    });

    function renderNeeStudentsTable() {
        neeStudentsTbody.innerHTML = '';
        if (neeStudents.length === 0) {
            neeStudentsTbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-gray-500">No hay estudiantes con NEE añadidos.</td></tr>';
            return;
        }
        neeStudents.forEach((student, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200';
            row.innerHTML = `
                <td class="py-2 px-4 text-sm text-gray-700">${student.name}</td>
                <td class="py-2 px-4 text-sm text-gray-700">${student.neeType}</td>
                <td class="py-2 px-4 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 text-sm delete-nee-student" data-index="${index}">Eliminar</button>
                </td>
            `;
            neeStudentsTbody.appendChild(row);
        });

        neeStudentsTbody.querySelectorAll('.delete-nee-student').forEach(button => {
            button.addEventListener('click', function() {
                const indexToDelete = parseInt(this.dataset.index);
                neeStudents.splice(indexToDelete, 1);
                renderNeeStudentsTable();
            });
        });
    }

    renderNeeStudentsTable();

    showSummaryBtn.addEventListener('click', function() {
        if (validateAllFields()) {
            updateSummary();
            formSections.classList.add('hidden');
            summarySection.classList.remove('hidden');
            showSummaryBtnContainer.classList.add('hidden');
        }
    });

    if (backToEditBtn) {
        backToEditBtn.addEventListener('click', function() {
            summarySection.classList.add('hidden');
            formSections.classList.remove('hidden');
            showSummaryBtnContainer.classList.remove('hidden');
        });
    }
});
