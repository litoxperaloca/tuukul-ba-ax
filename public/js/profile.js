// public/js/profile.js (Fase 2)

document.addEventListener('DOMContentLoaded', function() {
    // Lógica para mostrar/ocultar formularios de edición de bloques
    const editButtons = document.querySelectorAll('.edit-block-btn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const blockId = this.dataset.block;
            const form = document.getElementById(`form-${blockId}`);
            const displayDiv = document.getElementById(`display-${blockId}`);

            if (form && displayDiv) {
                form.classList.toggle('hidden');
                displayDiv.classList.toggle('hidden');
                // Cambiar texto del botón
                if (form.classList.contains('hidden')) {
                    this.innerHTML = '<i class="fas fa-edit mr-1"></i> Editar';
                } else {
                    this.innerHTML = '<i class="fas fa-times mr-1"></i> Cancelar';
                }
            }
        });
    });

    // Lógica para el botón de cancelar en los formularios de edición
    const cancelButtons = document.querySelectorAll('.btn-cancel-edit');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const blockId = this.dataset.block;
            const form = document.getElementById(`form-${blockId}`);
            const displayDiv = document.getElementById(`display-${blockId}`);
            const editBtn = document.querySelector(`.edit-block-btn[data-block="${blockId}"]`);

            if (form && displayDiv && editBtn) {
                form.classList.add('hidden');
                displayDiv.classList.remove('hidden');
                editBtn.innerHTML = '<i class="fas fa-edit mr-1"></i> Editar';
                // Opcional: resetear el formulario a sus valores iniciales si se cancela
                form.reset();
            }
        });
    });

    // Lógica para la subida de foto de perfil
    const profilePictureInput = document.getElementById('profile_picture');
    const saveProfilePictureBtn = document.getElementById('save_profile_picture_btn');
    const profilePictureForm = document.getElementById('form-profile-picture');

    if (profilePictureInput && saveProfilePictureBtn && profilePictureForm) {
        profilePictureInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                saveProfilePictureBtn.classList.remove('hidden'); // Mostrar botón de guardar
            } else {
                saveProfilePictureBtn.classList.add('hidden'); // Ocultar si no hay archivo
            }
        });

        // Opcional: Previsualización de la imagen antes de subir
        // const profileAvatarImg = document.querySelector('.profile-avatar img');
        // if (profileAvatarImg) {
        //     profilePictureInput.addEventListener('change', function() {
        //         if (this.files && this.files[0]) {
        //             const reader = new FileReader();
        //             reader.onload = function(e) {
        //                 profileAvatarImg.src = e.target.result;
        //             };
        //             reader.readAsDataURL(this.files[0]);
        //         }
        //     });
        // }
    }
});
