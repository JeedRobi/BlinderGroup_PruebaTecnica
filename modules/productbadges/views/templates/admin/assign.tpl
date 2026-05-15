<div class="panel">
    <div class="panel-heading">
        <i class="icon-shopping-cart"></i>
        {l s='Productos asignados' mod='productbadges'}
    </div>
    <div class="panel-body">
        {* Este es el input que lee el controlador en postProcess *}
        <input type="hidden" name="product_ids" id="product_ids_input"
               value="{if isset($assignedIds)}{implode(',', $assignedIds)|escape:'html':'UTF-8'}{/if}">

        <div class="productbadges-search-wrap" style="margin-bottom:10px;">
            <input type="text" id="productbadges-search"
                   class="form-control"
                   placeholder="{l s='Buscar producto...' mod='productbadges'}">
        </div>

        <div class="productbadges-product-list" style="max-height:300px;overflow-y:auto;border:1px solid #ddd;padding:8px;">
            {if $products}
                {foreach from=$products item=product}
                    <div class="productbadges-product-item" style="padding:4px 0;">
                        <label style="cursor:pointer;font-weight:normal;">
                            <input type="checkbox"
                                   class="productbadges-product-checkbox"
                                   value="{$product.id_product|intval}"
                                   {if in_array($product.id_product, $assignedIds)}checked{/if}>
                            {$product.name|escape:'html':'UTF-8'} 
                            {if !empty($product.reference)}<small class="text-muted">({$product.reference|escape:'html':'UTF-8'})</small>{/if}
                        </label>
                    </div>
                {/foreach}
            {else}
                <p class="text-muted">{l s='No hay productos disponibles.' mod='productbadges'}</p>
            {/if}
        </div>
    </div>
</div>