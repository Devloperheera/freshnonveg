(function () {
    'use strict';

    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';
    var cartPage = document.getElementById('cartPage');

    function formatInr(amount) {
        var n = Number(amount);
        if (Number.isNaN(n)) {
            return '₹0.00';
        }
        return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showToast(message, type) {
        if (typeof window.fnvToast === 'function') {
            window.fnvToast(message, type || 'success');
            return;
        }
        var el = document.getElementById('commerceToast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'commerceToast';
            el.className = 'commerce-toast';
            el.setAttribute('role', 'status');
            document.body.appendChild(el);
        }
        el.textContent = message;
        el.className = 'commerce-toast commerce-toast--' + (type || 'success') + ' commerce-toast--show';
        clearTimeout(el._hideTimer);
        el._hideTimer = setTimeout(function () {
            el.classList.remove('commerce-toast--show');
        }, 3200);
    }

    function setBadges(cartCount, wishlistCount) {
        function apply(selector, count) {
            document.querySelectorAll(selector).forEach(function (link) {
                var em = link.querySelector('em');
                if (count <= 0) {
                    if (em) {
                        em.remove();
                    }
                    return;
                }
                var text = count > 99 ? '99+' : String(count);
                if (!em) {
                    em = document.createElement('em');
                    link.appendChild(em);
                }
                em.textContent = text;
            });
        }

        apply('a.header-icon-cart, a.cart', cartCount);
        apply('a.header-icon-wishlist, a.wishlist-icon', wishlistCount);
    }

    function commerceFetch(url, options) {
        options = options || {};
        var headers = Object.assign(
            {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            options.headers || {}
        );

        return fetch(url, Object.assign({}, options, { headers: headers }))
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) {
                        var err = new Error(data.message || 'Request failed');
                        err.status = res.status;
                        err.payload = data;
                        throw err;
                    }
                    return data;
                });
            });
    }

    function applyCartState(data) {
        if (typeof data.cart_count === 'number' || typeof data.wishlist_count === 'number') {
            setBadges(data.cart_count || 0, data.wishlist_count || 0);
        }

        var summarySub = document.querySelector('[data-cart-subtotal]');
        var summaryTax = document.querySelector('[data-cart-tax]');
        var summaryTotal = document.querySelector('[data-cart-total]');
        if (summarySub && data.subtotal != null) {
            summarySub.textContent = formatInr(data.subtotal);
        }
        if (summaryTax && data.tax != null) {
            summaryTax.textContent = formatInr(data.tax);
        }
        if (summaryTotal && data.total != null) {
            summaryTotal.textContent = formatInr(data.total);
        }

        if (Array.isArray(data.items)) {
            data.items.forEach(function (item) {
                var row = document.querySelector('[data-cart-row-id="' + item.id + '"]');
                if (!row) {
                    return;
                }
                var qtyEl = row.querySelector('[data-cart-qty-display]');
                var lineEl = row.querySelector('[data-cart-line-total]');
                if (qtyEl) {
                    qtyEl.textContent = item.quantity;
                }
                if (lineEl) {
                    lineEl.textContent = formatInr(item.line_total);
                }
                row.setAttribute('data-cart-qty', String(item.quantity));
                var decBtn = row.querySelector('[data-cart-qty="dec"]');
                var incBtn = row.querySelector('[data-cart-qty="inc"]');
                if (decBtn) {
                    decBtn.disabled = item.quantity <= 1;
                }
                if (incBtn) {
                    incBtn.disabled = item.quantity >= 99;
                }
            });
        }

        if (data.is_empty) {
            window.location.reload();
        }
    }

    function cartItemUrl(itemId) {
        var base = cartPage ? cartPage.getAttribute('data-cart-base') || '/cart' : '/cart';
        return base.replace(/\/$/, '') + '/' + itemId;
    }

    function patchCartQuantity(itemId, quantity) {
        return commerceFetch(cartItemUrl(itemId), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity: quantity }),
        });
    }

    function deleteCartItem(itemId) {
        return commerceFetch(cartItemUrl(itemId), { method: 'DELETE' });
    }

    function clearCart(url) {
        return commerceFetch(url, { method: 'POST' });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target.closest('[data-ajax-add-cart], [data-ajax-wishlist]');
        if (!form) {
            return;
        }
        e.preventDefault();

        var isCart = form.hasAttribute('data-ajax-add-cart');
        var fd = new FormData(form);
        var btn = form.querySelector('[type="submit"]');
        if (btn) {
            btn.disabled = true;
        }

        commerceFetch(form.action, { method: 'POST', body: fd })
            .then(function (data) {
                if (isCart) {
                    window.location.assign(data.cart_url || '/cart');
                    return;
                }
                showToast(data.message || 'Done');
                setBadges(data.cart_count || 0, data.wishlist_count || 0);
            })
            .catch(function (err) {
                var msg =
                    (err.payload && err.payload.message) ||
                    (err.payload && err.payload.errors && Object.values(err.payload.errors)[0][0]) ||
                    'Kuch galat ho gaya. Dobara try karein.';
                showToast(msg, 'error');
            })
            .finally(function () {
                if (btn) {
                    btn.disabled = false;
                }
            });
    });

    if (cartPage) {
        var clearUrl = cartPage.getAttribute('data-clear-url') || '';

        cartPage.addEventListener('click', function (e) {
            var removeBtn = e.target.closest('[data-cart-remove]');
            if (removeBtn) {
                e.preventDefault();
                var removeId = removeBtn.getAttribute('data-cart-remove');
                if (!removeId || !window.confirm('Is item ko cart se hataein?')) {
                    return;
                }
                removeBtn.disabled = true;
                deleteCartItem(removeId)
                    .then(function (data) {
                        var row = document.querySelector('[data-cart-row-id="' + removeId + '"]');
                        if (row) {
                            row.remove();
                        }
                        applyCartState(data);
                        showToast(data.message || 'Removed');
                    })
                    .catch(function () {
                        showToast('Remove fail', 'error');
                        removeBtn.disabled = false;
                    });
                return;
            }

            var qtyBtn = e.target.closest('button[data-cart-qty]');
            if (qtyBtn) {
                var row = qtyBtn.closest('[data-cart-row-id]');
                if (!row) {
                    return;
                }
                var itemId = row.getAttribute('data-cart-row-id');
                var qty = parseInt(row.getAttribute('data-cart-qty') || '1', 10);
                var action = qtyBtn.getAttribute('data-cart-qty');
                if (action === 'dec') {
                    qty = Math.max(1, qty - 1);
                } else if (action === 'inc') {
                    qty = Math.min(99, qty + 1);
                } else {
                    return;
                }
                if (qty === parseInt(row.getAttribute('data-cart-qty') || '1', 10)) {
                    return;
                }
                qtyBtn.disabled = true;
                patchCartQuantity(itemId, qty)
                    .then(function (data) {
                        applyCartState(data);
                        showToast(data.message || 'Updated');
                    })
                    .catch(function () {
                        showToast('Quantity update fail', 'error');
                    })
                    .finally(function () {
                        qtyBtn.disabled = false;
                    });
                return;
            }

            var clearBtn = e.target.closest('[data-cart-clear]');
            if (clearBtn && clearUrl) {
                if (!window.confirm('Poora cart clear karein?')) {
                    return;
                }
                clearBtn.disabled = true;
                clearCart(clearUrl)
                    .then(function (data) {
                        applyCartState(data);
                        showToast(data.message || 'Cart cleared');
                    })
                    .catch(function () {
                        showToast('Clear cart fail', 'error');
                        clearBtn.disabled = false;
                    });
            }
        });
    }
})();
