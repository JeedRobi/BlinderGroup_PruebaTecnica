<div class="productbadges-wrapper">
    {foreach from=$badges item=badge}
        <span class="productbadges-badge productbadges-{$badge.position|escape:'html':'UTF-8'}"
              style="background-color:{$badge.bg_color|escape:'html':'UTF-8'};color:{$badge.text_color|escape:'html':'UTF-8'};">
            {$badge.label|escape:'html':'UTF-8'}
        </span>
    {/foreach}
</div>
<script>(function(){
    try {
        var s = document.currentScript;
        if (!s) return;
        var wrapper = s.previousElementSibling;
        if (!wrapper || !wrapper.classList.contains('productbadges-wrapper')) return;
        var product = wrapper.closest('.product-miniature, .product, .product-card, article, li');
        if (!product) return;
        var anchor = product.querySelector('a.thumbnail.product-thumbnail, .thumbnail.product-thumbnail, figure.product-media, .product-media');
            if (anchor) {
                // Prefer placing under the NEW flag if present
                var flags = product.querySelector('.product-flags');
                var newFlag = flags && flags.querySelector('li.product-flag.new');

                var clone = wrapper.cloneNode(true);
                clone.classList.add('productbadges-cloned');

                if (flags && newFlag) {
                    // create an li to host badges under the flags
                    var li = document.createElement('li');
                    li.className = 'productbadges-flag-item';

                    // determine position from badge class (default top-left)
                    var firstBadge = clone.querySelector('.productbadges-badge');
                    var positionClass = 'productbadges-top-left';
                    if (firstBadge && firstBadge.classList.contains('productbadges-top-right')) {
                        positionClass = 'productbadges-top-right';
                    }
                    li.classList.add('productbadges-flag-item--' + (positionClass === 'productbadges-top-right' ? 'top-right' : 'top-left'));

                    // move badge spans into the li
                    var spans = clone.querySelectorAll('.productbadges-badge');
                    spans.forEach(function(sp){ sp.style.pointerEvents='auto'; sp.style.display='inline-block'; li.appendChild(sp); });
                    // insert after the newFlag
                    if (newFlag.nextSibling) flags.insertBefore(li, newFlag.nextSibling);
                    else flags.appendChild(li);
                    wrapper.classList.add('productbadges-original-hidden');
                } else {
                    if (!anchor.style.position) anchor.style.position = 'relative';
                    if (!anchor.contains(clone)) anchor.insertBefore(clone, anchor.firstChild);
                    wrapper.classList.add('productbadges-original-hidden');
                }
            }
    } catch (e) {}
})();</script>