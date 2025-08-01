<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl card-shadow">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Crea tu cuenta de docente
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 font-roboto">
                Y empieza a transformar tu forma de planear y enseñar.
            </p>
        </div>
        <form class="mt-8 space-y-6" action="/process-register" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="nombre" class="sr-only">Nombre completo</label>
                    <input id="nombre" name="nombre" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Nombre completo">
                </div>
                <div>
                    <label for="email" class="sr-only">Correo electrónico</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Correo electrónico">
                </div>
                <div>
                    <label for="password" class="sr-only">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Contraseña">
                </div>
            </div>
            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Registrarse</button>
            </div>
        </form>
         <p class="mt-2 text-center text-sm text-gray-600 font-roboto">¿Ya tienes una cuenta? <a href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">Inicia sesión aquí</a></p>
    </div>
</div>
