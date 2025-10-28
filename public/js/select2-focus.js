// select2-focus.js

// Espera a que el documento esté listo

    // 🔍 Cuando se abre cualquier Select2, enfoca automáticamente el campo de búsqueda
    $(document).on('select2:open', function () {
        const searchInput = document.querySelector('.select2-container--open .select2-search__field');
        if (searchInput) {
            searchInput.focus();
        }
    });

