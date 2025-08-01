<?php
// Archivo: app/controllers/PdfController.php (Con integración Dompdf y corrección de getById)

// Incluir el autoloader de Dompdf
// Asegúrate de que esta ruta sea correcta según donde hayas colocado la carpeta dompdf
require_once dirname(__DIR__) . '/lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfController {

    private $planeacionModel;

    public function __construct() {
        // Asegúrate de que la clase Database esté disponible y configurada
        $db = Database::getInstance()->getConnection();
        $this->planeacionModel = new Planeacion($db);
    }

    /**
     * Genera un PDF a partir de la respuesta HTML de la IA.
     * @param int $planeacion_id El ID de la planeación a generar en PDF.
     */
    public function generatePdf($planeacion_id) {
        // Inicia la sesión si aún no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si el usuario está logueado y autorizado (docente o admin)
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['docente', 'admin'])) {
            // Redirigir o mostrar un error si no está autorizado
            header('Location: /auth/login'); // O a una página de error
            exit;
        }

        $user_id = $_SESSION['user_id']; // Obtener el ID del usuario de la sesión

        // Obtener la planeación de la base de datos usando getByIdAndUserId
        // Esta es la línea corregida.
        $planeacion = $this->planeacionModel->getByIdAndUserId($planeacion_id, $user_id);

        if (!$planeacion) {
            // Manejar caso donde la planeación no se encuentra o no pertenece al usuario
            error_log("Intento de generar PDF para planeación no encontrada o no autorizada: ID " . $planeacion_id . " por usuario " . $user_id);
            echo "Error: Planeación no encontrada o no tienes permisos para verla.";
            exit;
        }

        // La respuesta de la IA es el contenido HTML que queremos renderizar
        $html_content = $planeacion['respuesta_ia'];

        // --- Configuración y Creación de Dompdf ---
        $options = new Options();
        // Habilitar la carga de imágenes remotas y archivos CSS externos si es necesario
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true); // Habilitar el parser HTML5
        $options->set('defaultFont', 'Arial'); // Establecer una fuente por defecto

        $dompdf = new Dompdf($options);

        // Se recomienda envolver el HTML en una estructura básica si la respuesta de la IA
        // no es un documento HTML completo (<html><body>...</body></html>).
        // Esto ayuda a Dompdf a interpretarlo correctamente.
        // También puedes añadir estilos CSS directamente aquí o en un archivo CSS enlazado.
        $full_html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Planeación Didáctica</title>
            <style>
                /* Estilos CSS para el PDF */
                body {
                    font-family: "Arial", sans-serif;
                    font-size: 10pt;
                }
                h1, h2, h3, h4, h5, h6 {
                    font-family: "Arial", sans-serif;
                    color: #333;
                    margin-top: 1em;
                    margin-bottom: 0.5em;
                }
                h1 { font-size: 18pt; text-align: center; }
                h2 { font-size: 14pt; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
                h3 { font-size: 12pt; }
                p {
                    margin-bottom: 0.5em;
                    line-height: 1.4;
                }
                ul {
                    list-style-type: disc;
                    margin-left: 20px;
                    margin-bottom: 1em;
                }
                li {
                    margin-bottom: 0.3em;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 1em;
                    margin-bottom: 1em;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                }
                .header-info {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 1em;
                }
                .header-info td {
                    border: none; /* Eliminar bordes para la tabla de encabezado */
                    padding: 2px 5px;
                }
                .separator {
                    border-top: 1px solid #000;
                    margin-top: 10px;
                    margin-bottom: 10px;
                }
                .signature-line {
                    border-top: 1px solid #000;
                    width: 50%; /* Ajusta el ancho según necesites */
                    margin-top: 20px;
                    padding-top: 5px;
                    text-align: center;
                }
                  /* Tailwind-like utilities for Dompdf */
            .container { width: 100%; margin-left: auto; margin-right: auto; padding-left: 1.5rem; padding-right: 1.5rem; }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .py-10 { padding-top: 2.5rem; padding-bottom: 2.5rem; }
            .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
            .mb-8 { margin-bottom: 2rem; }
            .mt-6 { margin-top: 1.5rem; }
            .space-y-6 > *:not([hidden]) ~ *:not([hidden]) { margin-top: 1.5rem; margin-bottom: 0; }
            .space-y-4 > *:not([hidden]) ~ *:not([hidden]) { margin-top: 1rem; margin-bottom: 0; }
            .space-y-3 > *:not([hidden]) ~ *:not([hidden]) { margin-top: 0.75rem; margin-bottom: 0; }
            .mt-2 { margin-top: 0.5rem; }
            .mt-4 { margin-top: 1rem; }
            .mb-4 { margin-bottom: 1rem; }
            .pb-0 { padding-bottom: 0; }
            .p-8 { padding: 2rem; }
            .p-6 { padding: 1.5rem; }
            .p-4 { padding: 1rem; }
            .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .rounded-lg { border-radius: 0.5rem; }
            .border { border-width: 1px; border-style: solid; border-color: #e5e7eb; } /* gray-200 */
            .bg-white { background-color: #ffffff; }
            .bg-gray-100 { background-color: #f3f4f6; }
            .bg-gray-50 { background-color: #f9fafb; }
            .text-gray-800 { color: #1f2937; }
            .text-gray-700 { color: #374151; }
            .text-gray-600 { color: #4b5563; }
            .text-gray-500 { color: #6b7280; }
            .font-bold { font-weight: 700; }
            .font-semibold { font-weight: 600; }
            .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
            .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
            .text-xs { font-size: 0.75rem; line-height: 1rem; }
            .flex { display: flex; }
            .flex-col { flex-direction: column; }
            .items-center { align-items: center; }
            .justify-between { justify-content: space-between; }
            .justify-end { justify-content: flex-end; }
            .mr-4 { margin-right: 1rem; }
            .mr-3 { margin-right: 0.75rem; }
            .mr-2 { margin-right: 0.5rem; }
            .ml-2 { margin-left: 0.5rem; }
            .hidden { display: none !important; } /* Important to override other display properties */
            .overflow-x-auto { overflow-x: auto; }
            .min-w-full { min-width: 100%; }
            .border-b { border-bottom-width: 1px; }
            .pb-3 { padding-bottom: 0.75rem; }
            .text-left { text-align: left; }
            .text-center { text-align: center; }
            .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .uppercase { text-transform: uppercase; }
            .font-medium { font-weight: 500; }
            .text-indigo-600 { color: #4f46e5; }
            .hover:text-indigo-800:hover { color: #3730a3; }
            .text-red-500 { color: #ef4444; }
            .text-green-600 { color: #16a34a; }
            .text-yellow-600 { color: #d97706; }
            .text-purple-600 { color: #9333ea; }
            .text-teal-600 { color: #0d9488; }
            .text-blue-600 { color: #2563eb; }
            .border-indigo-200 { border-color: #e0e7ff; }
            .bg-red-100 { background-color: #fee2e2; }
            .border-red-400 { border-color: #f87171; }
            .text-red-700 { color: #b91c1c; }
            .bg-green-100 { background-color: #d1fae5; }
            .text-green-700 { color: #047857; }
            .underline { text-decoration: underline; }
            .cursor-not-allowed { cursor: not-allowed; }
            .input-style {
                width: 100%;
                margin-top: 0.25rem;
                display: block;
                border-radius: 0.375rem;
                border-color: #D1D5DB;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                padding: 0.625rem 0.75rem;
            }
            .label-style {
                display: block;
                font-size: 0.875rem;
                font-weight: 500;
                color: #374151;
                margin-bottom: 0.25rem;
            }
            .grid { display: grid; }
            .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
            .gap-8 { gap: 2rem; }
            .gap-4 { gap: 1rem; }

            /* Responsive classes for larger screens (md and lg) */
            @media (min-width: 768px) { /* md breakpoint */
                .md:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .md:flex-row { flex-direction: row; }
                .md:space-x-4 > *:not([hidden]) ~ *:not([hidden]) { margin-right: 1rem; margin-left: 0; }
                .md:space-y-0 > *:not([hidden]) ~ *:not([hidden]) { margin-top: 0; }
                .md:col-span-2 { grid-column: span 2 / span 2; }
            }

            @media (min-width: 1024px) { /* lg breakpoint */
                .lg:grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
                .lg:col-span-3 { grid-column: span 3 / span 3; }
                .lg:col-span-2 { grid-column: span 2 / span 2; }
            }

            /* Additional specific styles for tables within prose */
            .prose table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1em;
                margin-bottom: 1em;
            }
            .prose th, .prose td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .prose th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
                /* Puedes añadir más estilos para replicar tu documento de Word */
                @charset "utf-8";

                .table-cell {
                border: 1px solid rgb(226, 232, 240);
                padding: 0.75rem;
                vertical-align: top;
                }

                .header-cell {
                font-weight: 600;
                background-color: rgb(248, 250, 252);
                }

                .placeholder-input {
                width: 100%;
                border-radius: 0.375rem;
                border: 1px solid rgb(203, 213, 225);
                padding: 0.5rem 0.75rem;
                background-color: rgb(248, 250, 252);
                color: rgb(74, 85, 104);
                font-style: italic;
                }

                .section-title {
                font-weight: 600;
                color: rgb(45, 55, 72);
                font-size: 1.125rem;
                margin-bottom: 0.5rem;
                padding-bottom: 0.25rem;
                border-bottom: 2px solid rgb(226, 232, 240);
                }

                .session-block {
                border: 1px solid rgb(226, 232, 240);
                border-radius: 0.5rem;
                padding: 1rem;
                margin-top: 1rem;
                background-color: rgb(255, 255, 255);
                }

                @charset "utf-8";

                body {
                font-family: Poppins, sans-serif;
                }

                .font-roboto {
                font-family: Roboto, sans-serif;
                }

                .gradient-bg {
                background: linear-gradient(135deg, rgb(102, 126, 234) 0%, rgb(118, 75, 162) 100%);
                }

                .card-shadow {
                box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 25px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
                }

                .profile-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background-color: rgb(226, 232, 240);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                border: 2px solid rgb(167, 139, 250);
                }

                .profile-avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                }

                .profile-avatar i {
                color: rgb(100, 116, 139);
                font-size: 1.5rem;
                }

                .mobile-menu {
                transition: transform 0.3s ease-out;
                transform: translateX(100%);
                }

                .mobile-menu.active {
                transform: translateX(0px);
                }

                @charset "utf-8";

                @layer properties;

                @layer theme, base, components, utilities;

                @layer theme {
                :root, :host {
                    --font-sans: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
                    --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                    --color-red-500: oklch(63.7% 0.237 25.331);
                    --color-red-700: oklch(50.5% 0.213 27.518);
                    --color-indigo-600: oklch(51.1% 0.262 276.966);
                    --color-indigo-700: oklch(45.7% 0.24 277.023);
                    --color-indigo-800: oklch(39.8% 0.195 277.366);
                    --color-purple-600: oklch(55.8% 0.288 302.321);
                    --color-purple-700: oklch(49.6% 0.265 301.924);
                    --color-gray-50: oklch(98.5% 0.002 247.839);
                    --color-gray-300: oklch(87.2% 0.01 258.338);
                    --color-gray-400: oklch(70.7% 0.022 261.325);
                    --color-gray-600: oklch(44.6% 0.03 256.802);
                    --color-gray-700: oklch(37.3% 0.034 259.733);
                    --color-gray-800: oklch(27.8% 0.033 256.848);
                    --color-white: #fff;
                    --spacing: 0.25rem;
                    --container-4xl: 56rem;
                    --text-sm: 0.875rem;
                    --text-sm--line-height: calc(1.25 / 0.875);
                    --text-base: 1rem;
                    --text-base--line-height: calc(1.5 / 1);
                    --text-lg: 1.125rem;
                    --text-lg--line-height: calc(1.75 / 1.125);
                    --text-xl: 1.25rem;
                    --text-xl--line-height: calc(1.75 / 1.25);
                    --text-2xl: 1.5rem;
                    --text-2xl--line-height: calc(2 / 1.5);
                    --text-3xl: 1.875rem;
                    --text-3xl--line-height: calc(2.25 / 1.875);
                    --text-4xl: 2.25rem;
                    --text-4xl--line-height: calc(2.5 / 2.25);
                    --font-weight-normal: 400;
                    --font-weight-medium: 500;
                    --font-weight-semibold: 600;
                    --font-weight-bold: 700;
                    --radius-lg: 0.5rem;
                    --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
                    --default-transition-duration: 150ms;
                    --default-transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
                    --default-font-family: var(--font-sans);
                    --default-mono-font-family: var(--font-mono);
                }
                }

                @layer base {
                *, ::after, ::before, ::backdrop, ::file-selector-button {
                    box-sizing: border-box;
                    margin: 0px;
                    padding: 0px;
                    border: 0px solid;
                }
                html, :host {
                    line-height: 1.5;
                    text-size-adjust: 100%;
                    tab-size: 4;
                    font-family: var(--default-font-family, ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji");
                    font-feature-settings: var(--default-font-feature-settings, normal);
                    font-variation-settings: var(--default-font-variation-settings, normal);
                    -webkit-tap-highlight-color: transparent;
                }
                hr {
                    height: 0px;
                    color: inherit;
                    border-top-width: 1px;
                }
                abbr:where([title]) {
                    text-decoration: underline dotted;
                }
                h1, h2, h3, h4, h5, h6 {
                    font-size: inherit;
                    font-weight: inherit;
                }
                a {
                    color: inherit;
                    text-decoration: inherit;
                }
                b, strong {
                    font-weight: bolder;
                }
                code, kbd, samp, pre {
                    font-family: var(--default-mono-font-family, ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace);
                    font-feature-settings: var(--default-mono-font-feature-settings, normal);
                    font-variation-settings: var(--default-mono-font-variation-settings, normal);
                    font-size: 1em;
                }
                small {
                    font-size: 80%;
                }
                sub, sup {
                    font-size: 75%;
                    line-height: 0;
                    position: relative;
                    vertical-align: baseline;
                }
                sub {
                    bottom: -0.25em;
                }
                sup {
                    top: -0.5em;
                }
                table {
                    text-indent: 0px;
                    border-color: inherit;
                    border-collapse: collapse;
                }
                progress {
                    vertical-align: baseline;
                }
                summary {
                    display: list-item;
                }
                ol, ul, menu {
                    list-style: none;
                }
                img, svg, video, canvas, audio, iframe, embed, object {
                    display: block;
                    vertical-align: middle;
                }
                img, video {
                    max-width: 100%;
                    height: auto;
                }
                button, input, select, optgroup, textarea, ::file-selector-button {
                    font: inherit;
                    letter-spacing: inherit;
                    color: inherit;
                    border-radius: 0px;
                    background-color: transparent;
                    opacity: 1;
                }
                :where(select:is([multiple], [size])) optgroup {
                    font-weight: bolder;
                }
                :where(select:is([multiple], [size])) optgroup option {
                    padding-inline-start: 20px;
                }
                ::file-selector-button {
                    margin-inline-end: 4px;
                }
                ::placeholder {
                    opacity: 1;
                }
                @supports (not (-webkit-appearance: -apple-pay-button)) or (contain-intrinsic-size: 1px) {
                    ::placeholder {
                    color: currentcolor;
                    @supports (color: color-mix(in lab, red, red)) {
                        color: color-mix(in oklab, currentcolor, transparent);
                    }
                    }
                }
                textarea {
                    resize: vertical;
                }
                ::-webkit-search-decoration {
                    appearance: none;
                }
                ::-webkit-date-and-time-value {
                    min-height: 1lh;
                    text-align: inherit;
                }
                ::-webkit-datetime-edit {
                    display: inline-flex;
                }
                ::-webkit-datetime-edit-fields-wrapper {
                    padding: 0px;
                }
                ::-webkit-datetime-edit, ::-webkit-datetime-edit-year-field, ::-webkit-datetime-edit-month-field, ::-webkit-datetime-edit-day-field, ::-webkit-datetime-edit-hour-field, ::-webkit-datetime-edit-minute-field, ::-webkit-datetime-edit-second-field, ::-webkit-datetime-edit-millisecond-field, ::-webkit-datetime-edit-meridiem-field {
                    padding-block: 0px;
                }
                button, input:where([type="button"], [type="reset"], [type="submit"]), ::file-selector-button {
                    appearance: button;
                }
                ::-webkit-inner-spin-button, ::-webkit-outer-spin-button {
                    height: auto;
                }
                [hidden]:where(:not([hidden="until-found"])) {
                    display: none !important;
                }
                }

                @layer utilities {
                .fixed {
                    position: fixed;
                }
                .sticky {
                    position: sticky;
                }
                .inset-y-0 {
                    inset-block: calc(var(--spacing) * 0);
                }
                .top-0 {
                    top: calc(var(--spacing) * 0);
                }
                .right-0 {
                    right: calc(var(--spacing) * 0);
                }
                .z-50 {
                    z-index: 50;
                }
                .container {
                    width: 100%;
                }
                @media (width >= 40rem) {
                    .container {
                    max-width: 40rem;
                    }
                }
                @media (width >= 48rem) {
                    .container {
                    max-width: 48rem;
                    }
                }
                @media (width >= 64rem) {
                    .container {
                    max-width: 64rem;
                    }
                }
                @media (width >= 80rem) {
                    .container {
                    max-width: 80rem;
                    }
                }
                @media (width >= 96rem) {
                    .container {
                    max-width: 96rem;
                    }
                }
                .mx-auto {
                    margin-inline: auto;
                }
                .mt-1 {
                    margin-top: calc(var(--spacing) * 1);
                }
                .mt-2 {
                    margin-top: calc(var(--spacing) * 2);
                }
                .mt-4 {
                    margin-top: calc(var(--spacing) * 4);
                }
                .mt-8 {
                    margin-top: calc(var(--spacing) * 8);
                }
                .mt-16 {
                    margin-top: calc(var(--spacing) * 16);
                }
                .mr-1 {
                    margin-right: calc(var(--spacing) * 1);
                }
                .mr-2 {
                    margin-right: calc(var(--spacing) * 2);
                }
                .mr-3 {
                    margin-right: calc(var(--spacing) * 3);
                }
                .mr-4 {
                    margin-right: calc(var(--spacing) * 4);
                }
                .mb-1 {
                    margin-bottom: calc(var(--spacing) * 1);
                }
                .mb-6 {
                    margin-bottom: calc(var(--spacing) * 6);
                }
                .mb-8 {
                    margin-bottom: calc(var(--spacing) * 8);
                }
                .block {
                    display: block;
                }
                .flex {
                    display: flex;
                }
                .grid {
                    display: grid;
                }
                .hidden {
                    display: none;
                }
                .table-cell {
                    display: table-cell;
                }
                .h-24 {
                    height: calc(var(--spacing) * 24);
                }
                .h-28 {
                    height: calc(var(--spacing) * 28);
                }
                .h-32 {
                    height: calc(var(--spacing) * 32);
                }
                .h-\[80vh\] {
                    height: 80vh;
                }
                .w-64 {
                    width: calc(var(--spacing) * 64);
                }
                .max-w-4xl {
                    max-width: var(--container-4xl);
                }
                .max-w-none {
                    max-width: none;
                }
                .min-w-full {
                    min-width: 100%;
                }
                .transform {
                    transform: var(--tw-rotate-x,) var(--tw-rotate-y,) var(--tw-rotate-z,) var(--tw-skew-x,) var(--tw-skew-y,);
                }
                .list-inside {
                    list-style-position: inside;
                }
                .list-disc {
                    list-style-type: disc;
                }
                .grid-cols-1 {
                    grid-template-columns: repeat(1, minmax(0px, 1fr));
                }
                .flex-col {
                    flex-direction: column;
                }
                .items-center {
                    align-items: center;
                }
                .justify-between {
                    justify-content: space-between;
                }
                .justify-center {
                    justify-content: center;
                }
                .justify-end {
                    justify-content: flex-end;
                }
                .gap-4 {
                    gap: calc(var(--spacing) * 4);
                }
                .gap-8 {
                    gap: calc(var(--spacing) * 8);
                }
                .gap-12 {
                    gap: calc(var(--spacing) * 12);
                }
                .space-y-3 :where(& > :not(:last-child)) {
                    --tw-space-y-reverse: 0;
                    margin-block-start: calc(calc(var(--spacing) * 3) * var(--tw-space-y-reverse));
                    margin-block-end: calc(calc(var(--spacing) * 3) * calc(1 - var(--tw-space-y-reverse)));
                }
                .space-y-4 :where(& > :not(:last-child)) {
                    --tw-space-y-reverse: 0;
                    margin-block-start: calc(calc(var(--spacing) * 4) * var(--tw-space-y-reverse));
                    margin-block-end: calc(calc(var(--spacing) * 4) * calc(1 - var(--tw-space-y-reverse)));
                }
                .space-y-6 :where(& > :not(:last-child)) {
                    --tw-space-y-reverse: 0;
                    margin-block-start: calc(calc(var(--spacing) * 6) * var(--tw-space-y-reverse));
                    margin-block-end: calc(calc(var(--spacing) * 6) * calc(1 - var(--tw-space-y-reverse)));
                }
                .space-x-4 :where(& > :not(:last-child)) {
                    --tw-space-x-reverse: 0;
                    margin-inline-start: calc(calc(var(--spacing) * 4) * var(--tw-space-x-reverse));
                    margin-inline-end: calc(calc(var(--spacing) * 4) * calc(1 - var(--tw-space-x-reverse)));
                }
                .overflow-x-auto {
                    overflow-x: auto;
                }
                .overflow-y-auto {
                    overflow-y: auto;
                }
                .rounded-lg {
                    border-radius: var(--radius-lg);
                }
                .border {
                    border-style: var(--tw-border-style);
                    border-width: 1px;
                }
                .border-t-2 {
                    border-top-style: var(--tw-border-style);
                    border-top-width: 2px;
                }
                .border-b {
                    border-bottom-style: var(--tw-border-style);
                    border-bottom-width: 1px;
                }
                .border-gray-300 {
                    border-color: var(--color-gray-300);
                }
                .border-gray-400 {
                    border-color: var(--color-gray-400);
                }
                .bg-gray-50 {
                    background-color: var(--color-gray-50);
                }
                .bg-gray-800 {
                    background-color: var(--color-gray-800);
                }
                .bg-indigo-600 {
                    background-color: var(--color-indigo-600);
                }
                .bg-purple-600 {
                    background-color: var(--color-purple-600);
                }
                .bg-white {
                    background-color: var(--color-white);
                }
                .p-6 {
                    padding: calc(var(--spacing) * 6);
                }
                .p-8 {
                    padding: calc(var(--spacing) * 8);
                }
                .px-4 {
                    padding-inline: calc(var(--spacing) * 4);
                }
                .px-6 {
                    padding-inline: calc(var(--spacing) * 6);
                }
                .py-2 {
                    padding-block: calc(var(--spacing) * 2);
                }
                .py-3 {
                    padding-block: calc(var(--spacing) * 3);
                }
                .py-8 {
                    padding-block: calc(var(--spacing) * 8);
                }
                .py-10 {
                    padding-block: calc(var(--spacing) * 10);
                }
                .pt-2 {
                    padding-top: calc(var(--spacing) * 2);
                }
                .pt-8 {
                    padding-top: calc(var(--spacing) * 8);
                }
                .pb-4 {
                    padding-bottom: calc(var(--spacing) * 4);
                }
                .pl-4 {
                    padding-left: calc(var(--spacing) * 4);
                }
                .text-center {
                    text-align: center;
                }
                .text-2xl {
                    font-size: var(--text-2xl);
                    line-height: var(--tw-leading, var(--text-2xl--line-height));
                }
                .text-3xl {
                    font-size: var(--text-3xl);
                    line-height: var(--tw-leading, var(--text-3xl--line-height));
                }
                .text-base {
                    font-size: var(--text-base);
                    line-height: var(--tw-leading, var(--text-base--line-height));
                }
                .text-lg {
                    font-size: var(--text-lg);
                    line-height: var(--tw-leading, var(--text-lg--line-height));
                }
                .text-sm {
                    font-size: var(--text-sm);
                    line-height: var(--tw-leading, var(--text-sm--line-height));
                }
                .text-xl {
                    font-size: var(--text-xl);
                    line-height: var(--tw-leading, var(--text-xl--line-height));
                }
                .font-bold {
                    --tw-font-weight: var(--font-weight-bold);
                    font-weight: var(--font-weight-bold);
                }
                .font-medium {
                    --tw-font-weight: var(--font-weight-medium);
                    font-weight: var(--font-weight-medium);
                }
                .font-normal {
                    --tw-font-weight: var(--font-weight-normal);
                    font-weight: var(--font-weight-normal);
                }
                .font-semibold {
                    --tw-font-weight: var(--font-weight-semibold);
                    font-weight: var(--font-weight-semibold);
                }
                .text-gray-600 {
                    color: var(--color-gray-600);
                }
                .text-gray-700 {
                    color: var(--color-gray-700);
                }
                .text-gray-800 {
                    color: var(--color-gray-800);
                }
                .text-indigo-600 {
                    color: var(--color-indigo-600);
                }
                .text-purple-600 {
                    color: var(--color-purple-600);
                }
                .text-red-500 {
                    color: var(--color-red-500);
                }
                .text-white {
                    color: var(--color-white);
                }
                .italic {
                    font-style: italic;
                }
                .antialiased {
                    -webkit-font-smoothing: antialiased;
                }
                .shadow-lg {
                    --tw-shadow: 0 10px 15px -3px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 4px 6px -4px var(--tw-shadow-color, rgb(0 0 0 / 0.1));
                    box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
                }
                .shadow-md {
                    --tw-shadow: 0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1));
                    box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
                }
                .transition {
                    transition-property: color, background-color, border-color, outline-color, text-decoration-color, fill, stroke, --tw-gradient-from, --tw-gradient-via, --tw-gradient-to, opacity, box-shadow, transform, translate, scale, rotate, filter, -webkit-backdrop-filter, backdrop-filter, display, visibility, content-visibility, overlay, pointer-events;
                    transition-timing-function: var(--tw-ease, var(--default-transition-timing-function));
                    transition-duration: var(--tw-duration, var(--default-transition-duration));
                }
                .transition-colors {
                    transition-property: color, background-color, border-color, outline-color, text-decoration-color, fill, stroke, --tw-gradient-from, --tw-gradient-via, --tw-gradient-to;
                    transition-timing-function: var(--tw-ease, var(--default-transition-timing-function));
                    transition-duration: var(--tw-duration, var(--default-transition-duration));
                }
                .duration-300 {
                    --tw-duration: 300ms;
                    transition-duration: 300ms;
                }
                .ease-in-out {
                    --tw-ease: var(--ease-in-out);
                    transition-timing-function: var(--ease-in-out);
                }
                .hover:scale-105:hover {
                    @media (hover: hover) {
                    --tw-scale-x: 105%;
                    --tw-scale-y: 105%;
                    --tw-scale-z: 105%;
                    scale: var(--tw-scale-x) var(--tw-scale-y);
                    }
                }
                .hover:bg-indigo-700:hover {
                    @media (hover: hover) {
                    background-color: var(--color-indigo-700);
                    }
                }
                .hover:bg-purple-700:hover {
                    @media (hover: hover) {
                    background-color: var(--color-purple-700);
                    }
                }
                .hover:text-indigo-600:hover {
                    @media (hover: hover) {
                    color: var(--color-indigo-600);
                    }
                }
                .hover:text-indigo-800:hover {
                    @media (hover: hover) {
                    color: var(--color-indigo-800);
                    }
                }
                .hover:text-red-700:hover {
                    @media (hover: hover) {
                    color: var(--color-red-700);
                    }
                }
                .focus:text-indigo-600:focus {
                    color: var(--color-indigo-600);
                }
                .focus:outline-none:focus {
                    --tw-outline-style: none;
                    outline-style: none;
                }
                .sm:flex {
                    @media (width >= 40rem) {
                    display: flex;
                    }
                }
                .sm:hidden {
                    @media (width >= 40rem) {
                    display: none;
                    }
                }
                .md:grid-cols-2 {
                    @media (width >= 48rem) {
                    grid-template-columns: repeat(2, minmax(0px, 1fr));
                    }
                }
                .md:grid-cols-3 {
                    @media (width >= 48rem) {
                    grid-template-columns: repeat(3, minmax(0px, 1fr));
                    }
                }
                .md:text-4xl {
                    @media (width >= 48rem) {
                    font-size: var(--text-4xl);
                    line-height: var(--tw-leading, var(--text-4xl--line-height));
                    }
                }
                .lg:col-span-5 {
                    @media (width >= 64rem) {
                    grid-column: span 5 / span 5;
                    }
                }
                .lg:grid-cols-5 {
                    @media (width >= 64rem) {
                    grid-template-columns: repeat(5, minmax(0px, 1fr));
                    }
                }
                }

                @property --tw-rotate-x {
                syntax: "*";
                inherits: false;
                }

                @property --tw-rotate-y {
                syntax: "*";
                inherits: false;
                }

                @property --tw-rotate-z {
                syntax: "*";
                inherits: false;
                }

                @property --tw-skew-x {
                syntax: "*";
                inherits: false;
                }

                @property --tw-skew-y {
                syntax: "*";
                inherits: false;
                }

                @property --tw-space-y-reverse {
                syntax: "*";
                inherits: false;
                initial-value: 0;
                }

                @property --tw-space-x-reverse {
                syntax: "*";
                inherits: false;
                initial-value: 0;
                }

                @property --tw-border-style {
                syntax: "*";
                inherits: false;
                initial-value: solid;
                }

                @property --tw-font-weight {
                syntax: "*";
                inherits: false;
                }

                @property --tw-shadow {
                syntax: "*";
                inherits: false;
                initial-value: 0 0 #0000;
                }

                @property --tw-shadow-color {
                syntax: "*";
                inherits: false;
                }

                @property --tw-shadow-alpha {
                syntax: "<percentage>";
                inherits: false;
                initial-value: 100%;
                }

                @property --tw-inset-shadow {
                syntax: "*";
                inherits: false;
                initial-value: 0 0 #0000;
                }

                @property --tw-inset-shadow-color {
                syntax: "*";
                inherits: false;
                }

                @property --tw-inset-shadow-alpha {
                syntax: "<percentage>";
                inherits: false;
                initial-value: 100%;
                }

                @property --tw-ring-color {
                syntax: "*";
                inherits: false;
                }

                @property --tw-ring-shadow {
                syntax: "*";
                inherits: false;
                initial-value: 0 0 #0000;
                }

                @property --tw-inset-ring-color {
                syntax: "*";
                inherits: false;
                }

                @property --tw-inset-ring-shadow {
                syntax: "*";
                inherits: false;
                initial-value: 0 0 #0000;
                }

                @property --tw-ring-inset {
                syntax: "*";
                inherits: false;
                }

                @property --tw-ring-offset-width {
                syntax: "<length>";
                inherits: false;
                initial-value: 0px;
                }

                @property --tw-ring-offset-color {
                syntax: "*";
                inherits: false;
                initial-value: #fff;
                }

                @property --tw-ring-offset-shadow {
                syntax: "*";
                inherits: false;
                initial-value: 0 0 #0000;
                }

                @property --tw-duration {
                syntax: "*";
                inherits: false;
                }

                @property --tw-ease {
                syntax: "*";
                inherits: false;
                }

                @property --tw-scale-x {
                syntax: "*";
                inherits: false;
                initial-value: 1;
                }

                @property --tw-scale-y {
                syntax: "*";
                inherits: false;
                initial-value: 1;
                }

                @property --tw-scale-z {
                syntax: "*";
                inherits: false;
                initial-value: 1;
                }

                @layer properties {
                @supports ((-webkit-hyphens: none) and (not (margin-trim: inline))) or ((-moz-orient: inline) and (not (color:rgb(from red r g b)))) {
                    *, ::before, ::after, ::backdrop {
                    --tw-rotate-x: initial;
                    --tw-rotate-y: initial;
                    --tw-rotate-z: initial;
                    --tw-skew-x: initial;
                    --tw-skew-y: initial;
                    --tw-space-y-reverse: 0;
                    --tw-space-x-reverse: 0;
                    --tw-border-style: solid;
                    --tw-font-weight: initial;
                    --tw-shadow: 0 0 #0000;
                    --tw-shadow-color: initial;
                    --tw-shadow-alpha: 100%;
                    --tw-inset-shadow: 0 0 #0000;
                    --tw-inset-shadow-color: initial;
                    --tw-inset-shadow-alpha: 100%;
                    --tw-ring-color: initial;
                    --tw-ring-shadow: 0 0 #0000;
                    --tw-inset-ring-color: initial;
                    --tw-inset-ring-shadow: 0 0 #0000;
                    --tw-ring-inset: initial;
                    --tw-ring-offset-width: 0px;
                    --tw-ring-offset-color: #fff;
                    --tw-ring-offset-shadow: 0 0 #0000;
                    --tw-duration: initial;
                    --tw-ease: initial;
                    --tw-scale-x: 1;
                    --tw-scale-y: 1;
                    --tw-scale-z: 1;
                    }
                }
                }
             </style>
        </head>
        <body>
            ' . $html_content . '
        </body>
        </html>';

        $dompdf->loadHtml($full_html);

        // (Opcional) Configurar el tamaño y orientación del papel
        $dompdf->setPaper('A4', 'portrait'); // 'A4' y 'portrait' (vertical) o 'landscape' (horizontal)

        // Renderizar el HTML como PDF
        $dompdf->render();

        // Enviar el PDF al navegador para descarga
        $dompdf->stream("planeacion_" . $planeacion_id . ".pdf", array("Attachment" => true));
        exit(0);
    }
}
