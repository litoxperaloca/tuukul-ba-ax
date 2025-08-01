<?php
// Este archivo genera la página de inicio (landing page) para el proyecto Planeación Educativa IA.
// Utiliza Tailwind CSS para un diseño moderno y responsivo.
// Asegúrate de que el archivo _partials/header.php incluya el CDN de Tailwind CSS y Font Awesome.
?>

<div class="bg-gray-100 min-h-screen flex flex-col font-inter">
    <!-- Sección Hero: La primera impresión de la página -->
    <section class="gradient-bg text-white py-20 px-6 text-center relative overflow-hidden">
        <!-- Patrón de fondo sutil para la sección hero -->
        <div class="absolute inset-0 z-0 opacity-20" style="background-image: url('/img/background2.png'); background-size: cover; background-position: center;"></div>
        <div class="container mx-auto z-10 relative">
            <!-- Título principal con animación -->
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6 animate-fade-in-down">
                Transforma tu planeación didáctica con <span class="text-yellow-300">Inteligencia Artificial</span>
            </h1>
            <!-- Subtítulo descriptivo con animación y retraso -->
            <p class="text-lg md:text-xl max-w-3xl mx-auto font-light mb-10 opacity-0 animate-fade-in-up" style="animation-delay: 0.3s;">
                Simplifica tu trabajo, ahorra tiempo y genera planeaciones contextualizadas y alineadas a la currícula oficial de la educación pública de tu país, de forma rápida e inteligente.
            </p>
            <!-- Botones de llamada a la acción con animación y retraso -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 opacity-0 animate-fade-in-up" style="animation-delay: 0.6s;">
                <a href="/register" class="bg-yellow-400 text-purple-800 font-bold py-3 px-8 rounded-full text-lg hover:bg-yellow-300 transition-all duration-300 shadow-lg transform hover:scale-105">
                    <i class="fas fa-rocket mr-2"></i> Empieza gratis
                </a>
                <a href="#features" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-full text-lg hover:bg-white hover:text-indigo-600 transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-info-circle mr-2"></i> Conoce más
                </a>
            </div>
        </div>
    </section>

    <!-- Sección de Video: Muestra el funcionamiento del producto en un loop corto -->
    <section class="py-16 px-6 bg-gray-200 text-center">
        <div class="container mx-auto">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">Mira cómo funciona en segundos</h2>
            <div class="relative w-full max-w-4xl mx-auto rounded-xl overflow-hidden shadow-2xl">
                <!-- El video se reproducirá en bucle, automáticamente y silenciado -->
                <video class="w-full h-auto" autoplay loop muted playsinline>
                    <!-- Puedes reemplazar esta URL con el video real generado por Gemini -->
                    <source src="/videos/landing.mp4" type="video/mp4">
                    Tu navegador no soporta la etiqueta de video.
                </video>
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 text-white text-xl font-semibold opacity-0 hover:opacity-100 transition-opacity duration-300">
                    <p>¡Planea en un instante!</p>
                </div>
            </div>
            <p class="text-lg text-gray-700 mt-8 max-w-2xl mx-auto">
                Observa la eficiencia y simplicidad con la que Planeación Educativa IA transforma tu proceso de creación de planeaciones didácticas, dándote una herramienta para optimizar tu tiempo y esfuerzo y enfocarte en la calidad de la enseñanza.
            </p>
        </div>
    </section>

    <!-- Sección de Características: Explica los beneficios clave del proyecto -->
    <section id="features" class="py-16 px-6 bg-white">
        <div class="container mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-12">¿Por qué elegir Tuukul Ba'ax?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Tarjeta de característica: Generación Inteligente -->
                <div class="feature-card p-8 rounded-lg card-shadow transition-all duration-300 hover:scale-105 hover:shadow-xl">
                    <div class="text-indigo-600 text-5xl mb-6"><i class="fas fa-robot"></i></div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900">Generación inteligente</h3>
                    <p class="text-gray-700">Crea planeaciones didácticas detalladas y personalizadas en segundos, utilizando la potencia de la Inteligencia Artificial.</p>
                </div>
                <!-- Tarjeta de característica: Currículo Mexicano Vigente -->
                <div class="feature-card p-8 rounded-lg card-shadow transition-all duration-300 hover:scale-105 hover:shadow-xl">
                    <div class="text-green-600 text-5xl mb-6"><i class="fas fa-file-alt"></i></div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900">Currícula oficial vigente</h3>
                    <p class="text-gray-700">Asegura que tus planeaciones estén 100% alineadas al marco curricular de la educación pública de tu país, incluyendo Fases, Contenidos y PDAs.</p>
                </div>
                <!-- Tarjeta de característica: Ahorra Tiempo Valioso -->
                <div class="feature-card p-8 rounded-lg card-shadow transition-all duration-300 hover:scale-105 hover:shadow-xl">
                    <div class="text-purple-600 text-5xl mb-6"><i class="fas fa-clock"></i></div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900">Optimiza tus tiempos, más educación menos papeleo</h3>
                    <p class="text-gray-700">Dedica menos tiempo a la y más tiempo a lo que realmente importa: enseñar y conectar con tus estudiantes.</p>
                </div>
                <!-- Tarjeta de característica: Personalización Total -->
                <div class="feature-card p-8 rounded-lg card-shadow transition-all duration-300 hover:scale-105 hover:shadow-xl">
                    <div class="text-red-600 text-5xl mb-6"><i class="fas fa-users-cog"></i></div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900">Personalización total</h3>
                    <p class="text-gray-700">Adapta cada planeación a las necesidades específicas de tu grupo, incluyendo materiales, número de sesiones y alumnos con NEE.</p>
                </div>
                <!-- Tarjeta de característica: Enfoque Didáctico -->
                <div class="feature-card p-8 rounded-lg card-shadow transition-all duration-300 hover:scale-105 hover:shadow-xl">
                    <div class="text-blue-600 text-5xl mb-6"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900">Enfoque didáctico</h3>
                    <p class="text-gray-700">Cada sesión incluye propósitos claros, actividades de inicio, desarrollo y cierre, y una evaluación específica.</p>
                </div>
                <!-- Tarjeta de característica: Soporte a la Inclusión -->
                <div class="feature-card p-8 rounded-lg card-shadow transition-all duration-300 hover:scale-105 hover:shadow-xl">
                    <div class="text-yellow-600 text-5xl mb-6"><i class="fas fa-handshake"></i></div>
                    <h3 class="text-xl font-semibold mb-4 text-gray-900">Soporte a la inclusión</h3>
                    <p class="text-gray-700">Considera adecuaciones para alumnos con necesidades educativas especiales, fomentando una educación equitativa.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Llamada a la Acción: Enlaces a Login y Registro -->
    <section class="bg-gray-100 py-16 px-6">
        <div class="container mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">¿Listo para empezar a planear de forma inteligente?</h2>
            <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto mb-10">
                Únete a cientos de docentes que ya están optimizando su tiempo y mejorando su proceso de planeación.
            </p>
            <div class="flex flex-col md:flex-row justify-center items-center gap-6">
                <!-- Tarjeta para Iniciar Sesión -->
                <div class="bg-white p-8 rounded-xl card-shadow w-full md:w-1/3 text-center">
                    <i class="fas fa-user-check text-4xl text-indigo-500 mb-4"></i>
                    <h2 class="text-2xl font-bold mb-2">¿Ya tienes una cuenta?</h2>
                    <p class="text-gray-600 mb-6 h-16">Ingresa a tu panel de control para continuar creando y gestionar tus planeaciones.</p>
                    <a href="/login" class="w-full inline-block bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-indigo-700 transition-colors duration-300 shadow-lg">
                        Iniciar Sesión
                    </a>
                </div>
                
                <!-- Tarjeta para Registrarse -->
                <div class="bg-white p-8 rounded-xl card-shadow w-full md:w-1/3 text-center">
                    <i class="fas fa-user-plus text-4xl text-purple-500 mb-4"></i>
                    <h2 class="text-2xl font-bold mb-2">¿Eres nuevo aquí?</h2>
                    <p class="text-gray-600 mb-6 h-16">Crea tu cuenta gratis en segundos y empieza a innovar en el aula.</p>
                    <a href="/register" class="w-full inline-block bg-purple-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-purple-700 transition-colors duration-300 shadow-lg">
                        Crear una Cuenta
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- Estilos CSS personalizados para animaciones y fondos -->
<style>
    /* Animación para que los elementos aparezcan desde arriba */
    @keyframes fade-in-down {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Animación para que los elementos aparezcan desde abajo */
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Aplicación de la animación de aparición desde arriba */
    .animate-fade-in-down {
        animation: fade-in-down 1s ease-out forwards;
    }
    /* Aplicación de la animación de aparición desde abajo */
    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out forwards;
    }

    /* Fondo degradado para la sección hero y otras secciones */
    .gradient-bg {
        background: linear-gradient(135deg, #132059 0%, #48167b 100%);
    }

    /* Sombra para las tarjetas y elementos destacados */
    .card-shadow {
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Estilos base para las tarjetas de características y testimonios */
    .feature-card, .testimonial-card {
        background-color: #ffffff; /* Fondo blanco para las tarjetas */
        transition: all 0.3s ease-in-out; /* Transición suave para efectos hover */
    }

    /* Efecto hover para las tarjetas de características */
    .feature-card:hover {
        transform: translateY(-5px); /* Pequeño desplazamiento hacia arriba */
        box-shadow: 0 15px 30px -5px rgba(0,0,0,0.15), 0 6px 10px -3px rgba(0,0,0,0.08); /* Sombra más pronunciada */
    }
</style>
