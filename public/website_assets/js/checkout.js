(function () {
    'use strict';

    var form = document.getElementById('checkoutForm');
    if (!form) {
        return;
    }

    var subtotal = parseFloat(form.dataset.subtotal || '0');
    var instantFee = parseFloat(form.dataset.instantFee || '100');
    var pincodeUrlTemplate = form.dataset.pincodeUrl || '';
    var addressSuggestUrl = form.dataset.addressSuggestUrl || '';
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';

    var pincodeInput = document.getElementById('checkoutPincode');
    var stateInput = document.getElementById('checkoutState');
    var cityInput = document.getElementById('checkoutCity');
    var pincodeStatus = document.getElementById('pincodeStatus');
    var addressInput = document.getElementById('checkoutAddress');
    var comingSoonBox = document.getElementById('deliveryComingSoon');
    var placeOrderBtn = document.getElementById('checkoutPlaceOrderBtn');
    var outOfZone = comingSoonBox ? !comingSoonBox.hidden : false;

    var scheduleInstant = document.getElementById('scheduleInstant');
    var scheduleScheduled = document.getElementById('scheduleScheduled');
    var scheduledFields = document.getElementById('scheduledFields');
    var deliveryDate = document.getElementById('deliveryDate');
    var deliveryTime = document.getElementById('deliveryTime');
    var deliveryFeeRow = document.getElementById('checkoutDeliveryFeeRow');
    var deliveryFeeDisplay = document.getElementById('checkoutDeliveryFee');
    var totalDisplay = document.getElementById('checkoutGrandTotal');

    function formatInr(n) {
        return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function currentDeliveryFee() {
        if (scheduleInstant && scheduleInstant.checked) {
            return instantFee;
        }
        return 0;
    }

    var appliedCoupon = null;
    var appliedCode = '';
    var couponInput = document.getElementById('checkoutCoupon');
    var couponCodeInput = couponInput;
    var couponApplyBtn = document.getElementById('checkoutCouponApply');
    var couponMsg = document.getElementById('checkoutCouponMsg');
    var discountRow = document.getElementById('checkoutDiscountRow');
    var discountDisplay = document.getElementById('checkoutDiscount');
    var walletCheckbox = document.getElementById('checkoutUseWallet');
    var walletRow = document.getElementById('checkoutWalletRow');
    var walletDisplay = document.getElementById('checkoutWallet');
    if (walletCheckbox) {
        walletCheckbox.addEventListener('change', function () {
            updateTotals();
        });
    }

    function currentDiscount() {
        if (!appliedCoupon) {
            return 0;
        }
        var amount = appliedCoupon.type === 'percent'
            ? subtotal * (appliedCoupon.value / 100)
            : appliedCoupon.value;
        if (appliedCoupon.type === 'percent' && appliedCoupon.max) {
            amount = Math.min(amount, appliedCoupon.max);
        }
        amount = Math.min(Math.max(amount, 0), subtotal);
        return Math.round(amount * 100) / 100;
    }

    function flashToast(message, type) {
        if (message && typeof window.fnvToast === 'function') {
            window.fnvToast(message, type || 'success');
        }
    }

    function setCouponMessage(text, ok) {
        if (!couponMsg) {
            return;
        }
        couponMsg.textContent = text || '';
        couponMsg.className = 'small mt-1 ' + (ok ? 'text-success' : text ? 'text-danger' : 'text-muted');
    }

    function updateTotals() {
        var fee = currentDeliveryFee();
        var discount = currentDiscount();
        var tax = 0;
        var total = Math.max(0, subtotal - discount + tax + fee);
        var walletUsed = 0;
        if (walletCheckbox && walletCheckbox.checked) {
            var balance = parseFloat(walletCheckbox.dataset.walletBalance || '0');
            walletUsed = Math.round(Math.max(0, Math.min(balance, total - 1)) * 100) / 100;
        }
        total = Math.round((total - walletUsed) * 100) / 100;
        if (walletRow) {
            walletRow.hidden = walletUsed <= 0;
        }
        if (walletDisplay) {
            walletDisplay.textContent = '-' + formatInr(walletUsed);
        }
        if (deliveryFeeRow) {
            deliveryFeeRow.hidden = fee <= 0;
        }
        if (deliveryFeeDisplay) {
            deliveryFeeDisplay.textContent = formatInr(fee);
        }
        if (discountRow) {
            discountRow.hidden = discount <= 0;
        }
        if (discountDisplay) {
            discountDisplay.textContent = '-' + formatInr(discount);
        }
        if (totalDisplay) {
            totalDisplay.textContent = formatInr(total);
        }
    }

    function clearCoupon(message) {
        appliedCoupon = null;
        appliedCode = '';
        if (couponCodeInput && message) {
            couponCodeInput.value = couponInput ? couponInput.value : '';
        }
        if (couponApplyBtn) {
            couponApplyBtn.textContent = 'Apply';
        }
        setCouponMessage(message || '', false);
        updateTotals();
    }

    function applyCouponCode(code) {
        code = (code || '').trim().toUpperCase();
        if (!code) {
            clearCoupon('Coupon code daalein.');
            flashToast('Coupon code daalein.', 'error');
            return;
        }
        if (couponApplyBtn) {
            couponApplyBtn.disabled = true;
        }
        setCouponMessage('Coupon check ho raha hai…', true);
        fetch(form.dataset.couponUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ code: code }),
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Coupon valid nahi hai.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                appliedCoupon = {
                    type: data.discount_type,
                    value: parseFloat(data.discount_value || '0'),
                    max: data.max_discount ? parseFloat(data.max_discount) : null,
                };
                appliedCode = data.code;
                if (couponInput) {
                    couponInput.value = data.code;
                }
                if (couponCodeInput) {
                    couponCodeInput.value = data.code;
                }
                if (couponApplyBtn) {
                    couponApplyBtn.textContent = 'Applied';
                }
                setCouponMessage(data.message || 'Coupon lag gaya.', true);
                flashToast(data.message || 'Coupon lag gaya.', 'success');
                updateTotals();
            })
            .catch(function (err) {
                var failMsg = err.message || 'Coupon valid nahi hai.';
                clearCoupon(failMsg);
                flashToast(failMsg, 'error');
            })
            .finally(function () {
                if (couponApplyBtn) {
                    couponApplyBtn.disabled = false;
                }
            });
    }

    function toggleScheduleFields() {
        var isScheduled = scheduleScheduled && scheduleScheduled.checked;
        if (scheduledFields) {
            scheduledFields.hidden = !isScheduled;
        }
        if (deliveryDate) {
            deliveryDate.required = isScheduled;
        }
        if (deliveryTime) {
            deliveryTime.required = isScheduled;
        }
        updateTotals();
    }

    function setPincodeStatus(msg, type) {
        if (!pincodeStatus) {
            return;
        }
        pincodeStatus.textContent = msg || '';
        pincodeStatus.className = 'small mt-1 ' + (type === 'error' ? 'text-danger' : type === 'ok' ? 'text-success' : 'text-muted');
    }

    function setComingSoon(show, hi, en) {
        var wasOut = outOfZone;
        outOfZone = !!show;
        if (comingSoonBox) {
            var hiEl = comingSoonBox.querySelector('.checkout-coming-soon__hi');
            var enEl = comingSoonBox.querySelector('.checkout-coming-soon__en');
            if (hiEl && hi) {
                hiEl.textContent = hi;
            }
            if (enEl && en) {
                enEl.textContent = en;
            }
            comingSoonBox.hidden = !show;
        }
        if (pincodeInput) {
            pincodeInput.classList.toggle('is-invalid', !!show);
        }
        if (placeOrderBtn) {
            placeOrderBtn.disabled = !!show;
        }
        if (show && !wasOut) {
            var toastMsg = [hi, en].filter(Boolean).join('\n');
            if (!toastMsg && comingSoonBox) {
                var hiText = comingSoonBox.querySelector('.checkout-coming-soon__hi');
                var enText = comingSoonBox.querySelector('.checkout-coming-soon__en');
                toastMsg = [hiText && hiText.textContent, enText && enText.textContent].filter(Boolean).join('\n');
            }
            flashToast(toastMsg, 'warning');
        }
    }

    function lookupPincode(code) {
        if (!/^\d{6}$/.test(code)) {
            return;
        }
        var url = pincodeUrlTemplate.replace('__PINCODE__', encodeURIComponent(code));
        setPincodeStatus('Pincode lookup ho raha hai…', 'muted');
        fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) {
                        var err = new Error(data.message || 'Pincode not found');
                        err.delivery = data.delivery;
                        throw err;
                    }
                    return data;
                });
            })
            .then(function (data) {
                if (stateInput) {
                    stateInput.value = data.state || '';
                }
                if (cityInput) {
                    cityInput.value = data.city || '';
                }
                if (data.delivery && data.delivery.deliverable === false) {
                    setComingSoon(true, data.delivery.message_hi, data.delivery.message_en);
                    setPincodeStatus('', 'error');
                } else if (data.delivery && data.delivery.deliverable && data.delivery.zone) {
                    setComingSoon(false);
                    setPincodeStatus('✅ ' + data.delivery.message + ' · State aur city auto-fill ho gaye', 'ok');
                    flashToast(data.delivery.message, 'success');
                } else {
                    setComingSoon(false);
                    setPincodeStatus('State aur city auto-fill ho gaye ✅', 'ok');
                    flashToast('State aur city auto-fill ho gaye', 'success');
                }
            })
            .catch(function (err) {
                if (err.delivery && err.delivery.deliverable === false) {
                    setComingSoon(true, err.delivery.message_hi, err.delivery.message_en);
                    setPincodeStatus('', 'error');
                    return;
                }
                setComingSoon(false);
                var lookupFail = (err.message || 'Pincode lookup fail') + ' — State/City manually bhar dein.';
                setPincodeStatus(lookupFail, 'error');
                flashToast(lookupFail, 'error');
            });
    }

    if (pincodeInput) {
        pincodeInput.addEventListener('input', function () {
            var v = pincodeInput.value.replace(/\D/g, '').slice(0, 6);
            pincodeInput.value = v;
            if (v.length === 6) {
                lookupPincode(v);
            } else {
                setComingSoon(false);
                setPincodeStatus('', 'muted');
            }
        });
        if (/^\d{6}$/.test(pincodeInput.value)) {
            lookupPincode(pincodeInput.value);
        }
    }

    form.addEventListener('submit', function (e) {
        if (!outOfZone) {
            return;
        }
        e.preventDefault();
        setComingSoon(true);
        if (comingSoonBox) {
            comingSoonBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    function highlightScheduleOption() {
        document.querySelectorAll('.checkout-schedule-option').forEach(function (label) {
            var input = label.querySelector('input[type="radio"]');
            label.classList.toggle('active', input && input.checked);
        });
    }

    if (scheduleInstant) {
        scheduleInstant.addEventListener('change', function () {
            toggleScheduleFields();
            highlightScheduleOption();
        });
    }
    if (scheduleScheduled) {
        scheduleScheduled.addEventListener('change', function () {
            toggleScheduleFields();
            highlightScheduleOption();
        });
    }
    toggleScheduleFields();
    highlightScheduleOption();

    if (couponApplyBtn) {
        couponApplyBtn.addEventListener('click', function () {
            applyCouponCode(couponInput ? couponInput.value : '');
        });
    }
    if (couponInput) {
        couponInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCouponCode(couponInput.value);
            }
        });
        couponInput.addEventListener('input', function () {
            if (appliedCode && couponInput.value.trim().toUpperCase() !== appliedCode) {
                appliedCoupon = null;
                appliedCode = '';
                if (couponApplyBtn) {
                    couponApplyBtn.textContent = 'Apply';
                }
                setCouponMessage('', false);
                updateTotals();
            }
        });
        if (couponInput.value) {
            applyCouponCode(couponInput.value);
        }
    }

    if (deliveryDate) {
        var today = new Date().toISOString().split('T')[0];
        deliveryDate.min = today;
    }

    var addressDropdown = document.getElementById('addressSuggestDropdown');
    var suggestTimer = null;
    var suggestAbort = null;

    function hideAddressSuggestions() {
        if (addressDropdown) {
            addressDropdown.hidden = true;
            addressDropdown.innerHTML = '';
        }
    }

    function applyAddressSuggestion(item) {
        if (addressInput && item.address) {
            addressInput.value = item.address;
        }
        if (item.state && stateInput) {
            stateInput.value = item.state;
        }
        if (item.city && cityInput) {
            cityInput.value = item.city;
        }
        if (item.pincode && pincodeInput) {
            var pin = String(item.pincode).replace(/\D/g, '').slice(0, 6);
            if (pin.length === 6 && pincodeInput.value !== pin) {
                pincodeInput.value = pin;
                lookupPincode(pin);
            }
        }
        hideAddressSuggestions();
    }

    function renderAddressSuggestions(list) {
        if (!addressDropdown) {
            return;
        }
        if (!list || list.length === 0) {
            hideAddressSuggestions();
            return;
        }
        addressDropdown.innerHTML = '';
        list.forEach(function (item) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'checkout-address-dropdown__item';
            btn.textContent = item.label;
            btn.addEventListener('click', function () {
                applyAddressSuggestion(item);
            });
            addressDropdown.appendChild(btn);
        });
        addressDropdown.hidden = false;
    }

    function fetchAddressSuggestions(query) {
        if (!addressSuggestUrl || query.length < 3) {
            hideAddressSuggestions();
            return;
        }
        if (suggestAbort) {
            suggestAbort.abort();
        }
        suggestAbort = new AbortController();
        var url = addressSuggestUrl + (addressSuggestUrl.indexOf('?') >= 0 ? '&' : '?') + 'q=' + encodeURIComponent(query);
        fetch(url, {
            signal: suggestAbort.signal,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) {
                        throw new Error(data.message || 'Suggest failed');
                    }
                    return data;
                });
            })
            .then(function (data) {
                renderAddressSuggestions(data.suggestions || []);
            })
            .catch(function (err) {
                if (err.name === 'AbortError') {
                    return;
                }
                hideAddressSuggestions();
            });
    }

    if (addressInput) {
        addressInput.addEventListener('input', function () {
            clearTimeout(suggestTimer);
            var q = addressInput.value.trim();
            suggestTimer = setTimeout(function () {
                fetchAddressSuggestions(q);
            }, 320);
        });
        addressInput.addEventListener('focus', function () {
            var q = addressInput.value.trim();
            if (q.length >= 3) {
                fetchAddressSuggestions(q);
            }
        });
        addressInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideAddressSuggestions();
            }
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.checkout-address-wrap')) {
            hideAddressSuggestions();
        }
    });

    var mainCol = form.querySelector('.checkout-main-col');
    var summaryCol = form.querySelector('.checkout-summary-col');
    var summaryPin = summaryCol && summaryCol.querySelector('.checkout-summary-pin');
    var summarySpacer = summaryCol && summaryCol.querySelector('.checkout-summary-spacer');
    var pinMq = window.matchMedia('(min-width: 992px)');

    function checkoutPinOffset() {
        var raw = getComputedStyle(document.documentElement).getPropertyValue('--site-header-h').trim();
        var headerH = parseFloat(raw);
        if (Number.isNaN(headerH)) {
            headerH = 76;
        }
        return headerH + 16;
    }

    function resetSummaryPin() {
        if (!summaryPin) {
            return;
        }
        summaryPin.style.position = '';
        summaryPin.style.top = '';
        summaryPin.style.left = '';
        summaryPin.style.width = '';
        summaryPin.style.bottom = '';
        if (summarySpacer) {
            summarySpacer.hidden = true;
            summarySpacer.style.height = '';
        }
        if (summaryCol) {
            summaryCol.style.minHeight = '';
        }
    }

    function updateSummaryPin() {
        if (!mainCol || !summaryCol || !summaryPin || !summarySpacer) {
            return;
        }
        if (!pinMq.matches) {
            resetSummaryPin();
            return;
        }

        summaryCol.style.minHeight = mainCol.offsetHeight + 'px';
        summaryCol.style.position = 'relative';

        var top = checkoutPinOffset();
        var colRect = summaryCol.getBoundingClientRect();
        var cardH = summaryPin.offsetHeight;
        var colWidth = summaryCol.offsetWidth;

        if (colRect.top >= top) {
            resetSummaryPin();
            summaryCol.style.minHeight = mainCol.offsetHeight + 'px';
            return;
        }

        if (colRect.bottom - cardH <= top) {
            summaryPin.style.position = 'absolute';
            summaryPin.style.top = (summaryCol.offsetHeight - cardH) + 'px';
            summaryPin.style.left = '0';
            summaryPin.style.width = '100%';
            summarySpacer.hidden = true;
            summarySpacer.style.height = '';
            return;
        }

        summaryPin.style.position = 'fixed';
        summaryPin.style.top = top + 'px';
        summaryPin.style.left = colRect.left + 'px';
        summaryPin.style.width = colWidth + 'px';
        summarySpacer.hidden = false;
        summarySpacer.style.height = cardH + 'px';
    }

    if (summaryPin && mainCol) {
        var pinTick = false;
        function schedulePinUpdate() {
            if (pinTick) {
                return;
            }
            pinTick = true;
            window.requestAnimationFrame(function () {
                pinTick = false;
                updateSummaryPin();
            });
        }

        window.addEventListener('scroll', schedulePinUpdate, { passive: true });
        window.addEventListener('resize', schedulePinUpdate);
        if (typeof pinMq.addEventListener === 'function') {
            pinMq.addEventListener('change', schedulePinUpdate);
        } else if (typeof pinMq.addListener === 'function') {
            pinMq.addListener(schedulePinUpdate);
        }
        if (typeof ResizeObserver !== 'undefined') {
            var pinRo = new ResizeObserver(schedulePinUpdate);
            pinRo.observe(mainCol);
            pinRo.observe(summaryPin);
        }
        schedulePinUpdate();
    }
})();
