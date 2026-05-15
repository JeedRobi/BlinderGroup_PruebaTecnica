$(document).ready(function () {
    // Búsqueda en tiempo real
    $('#productbadges-search').on('input', function () {
        var search = $(this).val().toLowerCase();
        $('.productbadges-product-item').each(function () {
            var name = $(this).text().toLowerCase();
            $(this).toggle(name.indexOf(search) !== -1);
        });
    });

    // Sincronizar checkboxes con el input hidden
    function syncProductIds() {
        var ids = [];
        $('.productbadges-product-checkbox:checked').each(function () {
            ids.push($(this).val());
        });
        $('#product_ids_input').val(ids.join(','));
    }

    $(document).on('change', '.productbadges-product-checkbox', syncProductIds);

    // Ejecutar al cargar
    syncProductIds();
});