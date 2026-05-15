;(function () {
  'use strict'

  function processProducts() {
    var productSelectors = ['.product-miniature', '.product', '.product-card', 'article', '.product-container', '.product-single', '#main']
    document.querySelectorAll('.productbadges-wrapper').forEach(function (wrapper) {
      try {
        if (!wrapper || wrapper.classList.contains('productbadges-processed')) return

        // find closest product/container for context
        var product = null
        for (var i = 0; i < productSelectors.length; i++) {
          var p = wrapper.closest(productSelectors[i])
          if (p) { product = p; break }
        }

        // find flags within product context, or fallback to any .product-flags near the page
        var flags = null
        if (product) flags = product.querySelector('.product-flags')
        if (!flags) flags = document.querySelector('.product-flags')

        var newFlag = flags && flags.querySelector('li.product-flag.new')
        var firstBadge = wrapper.querySelector('.productbadges-badge')
        var pos = (firstBadge && firstBadge.classList.contains('productbadges-top-right')) ? 'top-right' : 'top-left'

        // If we're on a product page and badge is top-right, prefer to place it over the image (thumbnail)
        var isProductDetail = product && (product.querySelector('.thumbnail-container') && product.querySelector('.product-description'))
        if (isProductDetail && pos === 'top-right') {
          var thumbAnchor = product.querySelector('a.thumbnail.product-thumbnail, .thumbnail.product-thumbnail, .thumbnail-top a')
          if (thumbAnchor) {
            var cloneForThumb = wrapper.cloneNode(true)
            cloneForThumb.classList.add('productbadges-cloned', 'productbadges-cloned--top-right')
            if (!thumbAnchor.style.position) thumbAnchor.style.position = 'relative'
            if (!thumbAnchor.contains(cloneForThumb)) thumbAnchor.insertBefore(cloneForThumb, thumbAnchor.firstChild)

            // try to align vertically with the existing New flag if present
            try {
              var flagsInProduct = product.querySelector('.product-flags')
              var newFlagInProduct = flagsInProduct && flagsInProduct.querySelector('li.product-flag.new')
              if (newFlagInProduct) {
                var flagRect = newFlagInProduct.getBoundingClientRect()
                var thumbRect = thumbAnchor.getBoundingClientRect()
                var topOffset = Math.max(0, flagRect.top - thumbRect.top)
                cloneForThumb.style.top = Math.round(topOffset) + 'px'
              }
            } catch (e) {
              // ignore
            }

            wrapper.classList.add('productbadges-original-hidden')
            wrapper.classList.add('productbadges-processed')
            return
          }
        }

        if (flags && newFlag) {
          var li = document.createElement('li')
          li.className = 'productbadges-flag-item productbadges-flag-item--' + (pos === 'top-right' ? 'top-right' : 'top-left')

          wrapper.querySelectorAll('.productbadges-badge').forEach(function (sp) {
            sp.style.display = 'inline-block'
            sp.style.pointerEvents = 'auto'
            li.appendChild(sp)
          })

          if (newFlag.nextSibling) flags.insertBefore(li, newFlag.nextSibling)
          else flags.appendChild(li)

          wrapper.classList.add('productbadges-original-hidden')
          wrapper.classList.add('productbadges-processed')
          return
        }

        // fallback: move inside thumbnail anchor inside context product, or anywhere sensible
        var anchor = null
        if (product) anchor = product.querySelector('a.thumbnail.product-thumbnail, .thumbnail.product-thumbnail')
        if (!anchor) anchor = document.querySelector('a.thumbnail.product-thumbnail, .thumbnail.product-thumbnail')
        if (anchor) {
          if (!anchor.style.position) anchor.style.position = 'relative'
          if (!anchor.contains(wrapper)) anchor.insertBefore(wrapper, anchor.firstChild)
          wrapper.classList.add('productbadges-processed')
        }
      } catch (e) {
        // ignore per-wrapper errors
      }
    })
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', processProducts)
  } else {
    processProducts()
  }

  // Observe DOM changes to handle AJAX / lazy-loaded products
  var observer = new MutationObserver(function () {
    processProducts()
  })
  observer.observe(document.body, { childList: true, subtree: true })
})()
