<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold" id="imgModalCaption">Product image</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-light">
                <img src="" alt="" id="imgModalImg" style="max-width:100%;max-height:70vh;border-radius:10px">
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('click', function (e) {
    var el = e.target.closest('.js-img-zoom');
    if (!el) return;
    var modalEl = document.getElementById('imgModal');
    document.getElementById('imgModalImg').src = el.dataset.full || el.src;
    document.getElementById('imgModalImg').alt = el.alt || '';
    document.getElementById('imgModalCaption').textContent = el.dataset.caption || el.alt || 'Product image';
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});
</script>
