<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Planeación Educativa IA' ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .font-roboto { font-family: 'Roboto', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-shadow { box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        /* Estilos para el avatar de perfil */
        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0; /* bg-gray-200 */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #a78bfa; /* purple-400 */
        }
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-avatar i {
            color: #64748b; /* gray-500 */
            font-size: 1.5rem;
        }

        /* Mobile menu specific styles */
        .mobile-menu {
            transition: transform 0.3s ease-out;
            transform: translateX(100%); /* Start off-screen */
        }
        .mobile-menu.active {
            transform: translateX(0); /* Slide in */
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold text-indigo-600">
                <i class="fas fa-brain mr-2"></i>Tuukul Ba'ax<span class="text-purple-600"> Pensamiento en acción</span>
            </a>

            <!-- Hamburger menu icon for mobile -->
            <div class="sm:hidden">
                <button id="mobile-menu-button" class="text-gray-600 hover:text-indigo-600 focus:outline-none focus:text-indigo-600">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden sm:flex space-x-4 items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="/admin/dashboard" class="text-red-500 hover:text-red-700 font-semibold">
                            <i class="fas fa-user-shield mr-1"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="/dashboard" class="text-gray-600 hover:text-indigo-600 font-medium">Dashboard</a>
                    <!-- Enlace a Mi Perfil con icono de avatar -->
                    <a href="/dashboard/profile" class="flex items-center text-gray-600 hover:text-indigo-600 font-medium">
                        <div class="profile-avatar mr-2">
                            <?php if (isset($_SESSION['user_photo_url']) && !empty($_SESSION['user_photo_url'])): ?>
                                <img src="<?= htmlspecialchars($_SESSION['user_photo_url']) ?>" alt="Foto de Perfil">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        Mi Perfil
                    </a>
                    <a href="/logout" class="bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors duration-300 text-sm font-semibold">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="/login" class="text-gray-600 hover:text-indigo-600 font-medium">Iniciar Sesión</a>
                    <a href="/register" class="bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors duration-300 text-sm font-semibold">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Mobile Navigation (hidden by default) -->
        <div id="mobile-menu" class="mobile-menu fixed inset-y-0 right-0 w-64 bg-white shadow-lg z-50 p-6 sm:hidden">
            <div class="flex justify-end mb-6">
                <button id="close-mobile-menu-button" class="text-gray-600 hover:text-indigo-600 focus:outline-none focus:text-indigo-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <nav class="flex flex-col space-y-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="/admin/dashboard" class="text-red-500 hover:text-red-700 font-semibold text-lg py-2">
                            <i class="fas fa-user-shield mr-2"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="/dashboard" class="text-gray-700 hover:text-indigo-600 font-medium text-lg py-2">Dashboard</a>
                    <a href="/dashboard/profile" class="flex items-center text-gray-700 hover:text-indigo-600 font-medium text-lg py-2">
                        <div class="profile-avatar mr-3">
                            <?php if (isset($_SESSION['user_photo_url']) && !empty($_SESSION['user_photo_url'])): ?>
                                <img src="<?= htmlspecialchars($_SESSION['user_photo_url']) ?>" alt="Foto de Perfil">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        Mi Perfil
                    </a>
                    <a href="/logout" class="bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors duration-300 text-lg font-semibold text-center">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="/login" class="text-gray-700 hover:text-indigo-600 font-medium text-lg py-2">Iniciar Sesión</a>
                    <a href="/register" class="bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors duration-300 text-lg font-semibold text-center">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>
        <?php
        // Mensajes de sesión (éxito/error) - Se muestran en el header para todas las páginas
        if (isset($_SESSION['message'])): ?>
            <div class="container mx-auto px-6 mt-8">
                <div class="p-4 rounded-md <?php echo $_SESSION['message']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo htmlspecialchars($_SESSION['message']['text']); ?>
                </div>
            </div>
            <?php unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
        endif;
        ?>
    <!-- Script para la lógica del menú móvil -->
    <script src="/js/header.js"></script>