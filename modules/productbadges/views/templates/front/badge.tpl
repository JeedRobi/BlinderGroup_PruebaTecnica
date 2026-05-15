{**
 * Plantilla para mostrar las badges sobre la imagen del producto.
 * Se usa tanto en listado de categoría como en ficha de producto.
 *}

<div class="productbadges-wrapper">
    {foreach from=$badges item=badge}
        <span class="productbadges-badge productbadges-{$badge.position|escape:'html':'UTF-8'}"
              style="background-color:{$badge.bg_color|escape:'html':'UTF-8'};color:{$badge.text_color|escape:'html':'UTF-8'};">
            {$badge.label|escape:'html':'UTF-8'}
        </span>
    {/foreach}
</div>