<?php
// Archivo: app/views/dashboard/view.php (Actualizado para mostrar solo la planeación generada)
?>
<div class="container mx-auto py-10 px-6">
    <div class="flex items-center mb-8">
        <a href="/dashboard" class="text-indigo-600 hover:text-indigo-800 mr-4" title="Volver al listado">
            <i class="fas fa-arrow-left fa-lg"></i>
        </a>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Detalle de la Planeación</h1>
    </div>

    <?php
        // Aunque no se muestren directamente, los datos decodificados aún podrían ser útiles
        // para depuración o futuras funcionalidades.
        $prompt_data_decoded = json_decode($planeacion['prompt_data'], true);
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Columna de Respuesta de la IA (ahora ocupa todo el ancho) -->
        <div class="lg:col-span-5 bg-white rounded-lg card-shadow">
             <h2 class="text-2xl font-bold text-gray-800 p-8 pb-4 border-b">Planeación Generada</h2>
            <div class="prose prose-indigo max-w-none p-8 h-[80vh] overflow-y-auto">
                <div id='planeacionPDFwrapper'>
                    <?php
                        // Renderizar el HTML directamente.
                        // Asegúrate de que el HTML generado por la IA sea seguro y confiable.
                        echo $planeacion['respuesta_ia'];
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Post-Generación -->
    <div class="mt-8 flex justify-center space-x-4">
        <!--<a href="/dashboard/pdf/<?= htmlspecialchars($planeacion['id']) ?>" target="_blank" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition duration-300 ease-in-out transform hover:scale-105">
            <i class="fas fa-file-pdf mr-2"></i> Exportar a PDF
        </a>-->
         <button id="print-plan-btn" class="btn btn-secondary bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center transition duration-300 ease-in-out transform hover:scale-105">
            <i class="fas fa-print mr-2"></i> Imprimir
        </button>
        <!--<button id="share-plan-btn" class="btn btn-secondary bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg flex items-center transition duration-300 ease-in-out transform hover:scale-105">
            <i class="fas fa-share-alt mr-2"></i> Compartir Planeación
        </button>-->
    </div>
</div>

<!-- Script para la funcionalidad de compartir (mantener si es necesario) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
 const printPlanBtn = document.getElementById('print-plan-btn');
    const planContent = <?= json_encode($planeacion['respuesta_ia']) ?>; // Obtener el contenido HTML de la planeación

    if (printPlanBtn) {
        printPlanBtn.addEventListener('click', function() {
            // Crear un iframe oculto
            const printFrame = document.createElement('iframe');
            printFrame.style.display = 'none';
            document.body.appendChild(printFrame);

            // Obtener el documento del iframe
            const frameDoc = printFrame.contentWindow.document;
            frameDoc.open();
            
            // Construir el HTML completo para el iframe, incluyendo los estilos del documento principal
            // Esto es crucial para que el PDF se vea igual que la página web.
            let htmlToPrint = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Imprimir Planeación</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <!-- Incluir todos los estilos del documento principal -->
                    ${Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
                        .map(tag => tag.outerHTML)
                        .join('')}
                    <style>
                        /* Estilos específicos para impresión si es necesario */
                        body { font-size: 8pt; width: 100%; }
                        /* Asegurar que el contenido de la planeación ocupe el ancho completo */
                        .prose { max-width: none !important; }
                        /* Ocultar elementos no deseados en la impresión */
                        
                        .mt-1{    
                            height: 3rem !important;
                             margin-top: 0.25rem !important;
                            font-size: 7pt !important;
                        }   
                            .h-24 {
                                height: 6rem !important;
                            }
                    </style>
                </head>
                <body>
                                ${planContent}
                </body>
                </html>
            `;

            frameDoc.write(htmlToPrint);
            frameDoc.close();

            // Esperar a que el contenido del iframe cargue completamente antes de imprimir
            printFrame.contentWindow.onload = function() {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                // Eliminar el iframe después de un breve retraso para permitir que el diálogo de impresión se abra
                setTimeout(() => {
                    document.body.removeChild(printFrame);
                }, 1000); // Pequeño retraso
            };
        });
    }

    const sharePlanBtn = document.getElementById('share-plan-btn');
    if (sharePlanBtn) {
        sharePlanBtn.addEventListener('click', function() {
            const currentPlanId = <?= json_encode(htmlspecialchars($planeacion['id'])) ?>;
            if (!currentPlanId) {
                alert('No hay una planeación reciente para compartir.');
                return;
            }
            const shareUrl = window.location.origin + `/dashboard/view/${currentPlanId}`;
            const emailSubject = encodeURIComponent('Planeación Didáctica de PlaneaIA');
            const emailBody = encodeURIComponent('Hola,\n\nTe comparto esta planeación didáctica generada con PlaneaIA:\n' + shareUrl + '\n\nSaludos.');
            const whatsappText = encodeURIComponent('Mira esta planeación didáctica que generé con PlaneaIA: ' + shareUrl);

            const shareOptions = `
                <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-8 rounded-lg shadow-xl w-96">
                        <h3 class="text-xl font-bold mb-4">Compartir Planeación</h3>
                        <p class="mb-4">Elige cómo quieres compartir esta planeación:</p>
                        <div class="space-y-3">
                            <a href="mailto:?subject=${emailSubject}&body=${emailBody}" target="_blank" class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                                <i class="fas fa-envelope mr-2"></i> Compartir por Email
                            </a>
                            <a href="https://wa.me/?text=${whatsappText}" target="_blank" class="flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">
                                <i class="fab fa-whatsapp mr-2"></i> Compartir por WhatsApp
                            </a>
                            <button class="flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition-colors w-full" onclick="document.execCommand('copy'); alert('Enlace copiado al portapapeles!'); this.closest('.fixed').remove();">
                                <i class="fas fa-copy mr-2"></i> Copiar Enlace
                            </button>
                            <button class="w-full px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors mt-4" onclick="this.closest('.fixed').remove()">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', shareOptions);
        });
    }
});
</script>
