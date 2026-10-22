@extends('Admin.Layout.master')

@section('title', ($zone->exists ? 'Edit' : 'New').' Delivery Zone — FreshNonVeg Admin')

@php
    $type = old('type', $zone->type);
    $pincodeRows = old('pincodes', $zone->exists
        ? $zone->pincodes->map(fn ($p) => $p->only(['pincode', 'area_name', 'city', 'state']))->all()
        : []);
    if ($pincodeRows === []) {
        $pincodeRows = [['pincode' => '', 'area_name' => '', 'city' => '', 'state' => '']];
    }
    $polygonJson = old('polygon', $zone->polygon ? json_encode($zone->polygon) : '');
    $isActive = (bool) old('status', $zone->exists ? $zone->status : 1);
@endphp

@section('custom_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
.zone-wrap{background:#fff;border-radius:14px;padding:24px;box-shadow:0 2px 10px rgba(30,142,62,.1)}
.zone-title{display:inline-flex;align-items:center;gap:10px;font-size:1.15rem;font-weight:800;color:#14532d;padding-bottom:8px;border-bottom:3px solid #F57C00;margin:0}
.zone-title i{color:#14532d}
.zone-card{border:1px solid #e5e7eb;border-radius:12px;padding:20px;height:100%;background:#fff}
.zone-card-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding-bottom:12px;margin-bottom:16px;border-bottom:1px solid #eef2f7}
.zone-card-head h3{font-size:1rem;font-weight:700;margin:0;display:flex;align-items:center;gap:10px;color:#1f2937}
.zone-card .form-label{font-size:12.5px;font-weight:600;color:#374151;margin-bottom:4px}
.zone-card .form-control,.zone-card .form-select{font-size:13px}
.zone-hint{font-size:11.5px;color:#6b7280;margin-top:4px}
.zone-dot{width:14px;height:14px;border-radius:50%;background:#22c3e6;display:inline-block}
.zone-map{height:420px;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;z-index:0}
.zone-alert{font-size:12.5px;border-radius:8px;padding:10px 12px;margin-bottom:16px;display:flex;gap:8px;align-items:center}
.zone-alert-info{background:#cff4fc;color:#055160;border:1px solid #9eeaf9}
.zone-alert-warn{background:#fff3cd;color:#664d03;border:1px solid #ffe69c}
.zone-alert-muted{background:#e9ecef;color:#343a40;border:1px solid #dee2e6}
.zone-panel[hidden]{display:none!important}
.zone-pin-head,.zone-pin-row{display:grid;grid-template-columns:110px minmax(0,1.3fr) minmax(0,1fr) minmax(0,1fr) 34px;gap:8px;align-items:center}
.zone-pin-head{font-size:12px;font-weight:600;color:#374151;margin-bottom:4px}
.zone-pin-row{margin-bottom:8px}
.zone-pin-row .btn-remove{width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;border:1px solid #dc3545;color:#dc3545;background:#fff;border-radius:6px}
.zone-pin-row .btn-remove:hover{background:#dc3545;color:#fff}
.zone-pin-row .pin-loading{border-color:#F57C00!important}
.zone-search{background:#fff;border-radius:6px;box-shadow:0 1px 5px rgba(0,0,0,.35);display:flex;align-items:flex-start}
.zone-search-btn{width:34px;height:34px;border:0;background:#fff;border-radius:6px;font-size:15px;cursor:pointer;color:#111827}
.zone-search-box{display:none;width:260px;padding:4px 4px 4px 0}
.zone-search.open .zone-search-box{display:block}
.zone-search-box input{width:100%;font-size:13px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:5px;outline:0}
.zone-search-results{list-style:none;margin:4px 0 0;padding:0;max-height:220px;overflow:auto}
.zone-search-results li{font-size:12px;padding:6px 8px;cursor:pointer;border-radius:4px;line-height:1.3}
.zone-search-results li:hover{background:#f1f5f9}
.zone-search-results li.muted{color:#6b7280;cursor:default}
.zone-poly-meta{font-size:12px;color:#374151;margin-top:8px;display:flex;gap:14px;flex-wrap:wrap}
.zone-switch .form-check-input{width:2.4em;height:1.25em;cursor:pointer}
.zone-switch .form-check-input:checked{background-color:#1a73e8;border-color:#1a73e8}
.zone-switch .form-check-label{font-weight:700;font-size:13px;margin-left:4px}
.zone-actions .btn{font-size:13px;font-weight:600;padding:8px 18px}
.btn-zone-save{background:#14532d;border-color:#14532d;color:#fff}
.btn-zone-save:hover{background:#1E8E3E;border-color:#1E8E3E;color:#fff}
.btn-zone-cancel{background:#F57C00;border-color:#F57C00;color:#fff}
.btn-zone-cancel:hover{background:#e06f00;color:#fff}
@media (max-width:767.98px){
.zone-pin-head{display:none}
.zone-pin-row{grid-template-columns:1fr 1fr;padding:10px;border:1px dashed #e5e7eb;border-radius:10px}
.zone-map{height:320px}
}
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">
    <div class="zone-wrap">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
            <h2 class="zone-title">
                <i class="fas fa-{{ $zone->exists ? 'edit' : 'plus-circle' }}"></i>
                {{ $zone->exists ? 'Edit Delivery Zone' : 'New Delivery Zone' }}
            </h2>
            <a href="{{ route('admin.delivery-zones.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" id="zoneForm"
              action="{{ $zone->exists ? route('admin.delivery-zones.update', $zone) : route('admin.delivery-zones.store') }}"
              data-pincode-url="{{ route('admin.delivery-zones.pincode', ['pincode' => '__PIN__']) }}">
            @csrf
            @if($zone->exists) @method('PUT') @endif

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="zone-card">
                        <div class="zone-card-head">
                            <h3><i class="fas fa-info-circle text-primary"></i> Zone Info</h3>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="zoneName">Zone Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="zoneName" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $zone->name) }}" required maxlength="255" placeholder="e.g. Delhi Zone 1">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="zoneType">Type <span class="text-danger">*</span></label>
                            <select name="type" id="zoneType" class="form-select" required>
                                @foreach(\App\Models\DeliveryZone::TYPES as $key => $label)
                                    <option value="{{ $key }}" @selected($type === $key)>{{ \App\Models\DeliveryZone::TYPE_ICONS[$key] }} {{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="zone-hint">Radius = KM circle | Pincode = specific areas | Polygon = custom area</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="zoneDesc">Description</label>
                            <textarea name="description" id="zoneDesc" rows="3" class="form-control" maxlength="2000"
                                      placeholder="Admin note...">{{ old('description', $zone->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="zoneSort">Sort Order</label>
                            <input type="number" name="sort_order" id="zoneSort" min="0" max="9999" class="form-control"
                                   value="{{ old('sort_order', $zone->sort_order) }}">
                            <div class="zone-hint">Chhota number = pehle check hoga</div>
                        </div>

                        <div class="form-check form-switch zone-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="status" value="1" id="zoneStatus" @checked($isActive)>
                            <label class="form-check-label" for="zoneStatus">Active</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    {{-- Radius --}}
                    <div class="zone-card zone-panel" data-panel="radius" @if($type !== 'radius') hidden @endif>
                        <div class="zone-card-head">
                            <h3><span class="zone-dot"></span> Radius Settings</h3>
                        </div>
                        <div class="zone-alert zone-alert-info">
                            <i class="fas fa-info-circle"></i> Search box se location dhundo ya map pe click karke center set karo.
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="centerLat">Center Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="center_lat" id="centerLat" class="form-control @error('center_lat') is-invalid @enderror"
                                       value="{{ old('center_lat', $zone->center_lat) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="centerLng">Center Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="center_lng" id="centerLng" class="form-control @error('center_lng') is-invalid @enderror"
                                       value="{{ old('center_lng', $zone->center_lng) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="radiusKm">Radius (KM) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0.1" max="200" name="radius_km" id="radiusKm" class="form-control @error('radius_km') is-invalid @enderror"
                                       value="{{ old('radius_km', $zone->radius_km) }}">
                                <div class="zone-hint">Center se kitne KM tak delivery</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="centerAddress">Center Address</label>
                                <input type="text" name="center_address" id="centerAddress" class="form-control" maxlength="255"
                                       value="{{ old('center_address', $zone->center_address) }}" placeholder="e.g. Nangloi, Delhi">
                            </div>
                        </div>
                        <div id="radiusMap" class="zone-map"></div>
                        <div class="zone-hint mt-2">
                            <i class="fas fa-search me-1"></i> Search box map ke upar-right mein hai |
                            <i class="fas fa-mouse-pointer mx-1"></i> Map pe click karke center set karo | Marker drag bhi kar sakte ho
                        </div>
                    </div>

                    {{-- Pincode --}}
                    <div class="zone-card zone-panel" data-panel="pincode" @if($type !== 'pincode') hidden @endif>
                        <div class="zone-card-head">
                            <h3><i class="fas fa-map-pin text-warning"></i> Pincode List</h3>
                            <button type="button" class="btn btn-success btn-sm" id="addPinRow"><i class="fas fa-plus me-1"></i> Add Row</button>
                        </div>
                        <div class="zone-alert zone-alert-warn">
                            <i class="fas fa-info-circle"></i> Jin pincodes mein delivery karni hai wo add karo. Pincode daalte hi area, city, state auto-fill ho jayenge.
                        </div>
                        <div class="zone-pin-head">
                            <span>Pincode <span class="text-danger">*</span></span><span>Area Name</span><span>City</span><span>State</span><span></span>
                        </div>
                        <div id="pinRows">
                            @foreach($pincodeRows as $i => $row)
                                <div class="zone-pin-row">
                                    <input type="text" name="pincodes[{{ $i }}][pincode]" class="form-control pin-code" inputmode="numeric" maxlength="6" placeholder="110041" value="{{ $row['pincode'] ?? '' }}">
                                    <input type="text" name="pincodes[{{ $i }}][area_name]" class="form-control pin-area" maxlength="255" placeholder="Area" value="{{ $row['area_name'] ?? '' }}">
                                    <input type="text" name="pincodes[{{ $i }}][city]" class="form-control pin-city" maxlength="100" placeholder="City" value="{{ $row['city'] ?? '' }}">
                                    <input type="text" name="pincodes[{{ $i }}][state]" class="form-control pin-state" maxlength="100" placeholder="State" value="{{ $row['state'] ?? '' }}">
                                    <button type="button" class="btn-remove" title="Remove"><i class="fas fa-times"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <div class="zone-hint" id="pinCount"></div>
                    </div>

                    {{-- Polygon --}}
                    <div class="zone-card zone-panel" data-panel="polygon" @if($type !== 'polygon') hidden @endif>
                        <div class="zone-card-head">
                            <h3><i class="fas fa-draw-polygon" style="color:#6f42c1"></i> Polygon Settings</h3>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="polyUndo"><i class="fas fa-undo me-1"></i> Undo</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="polyReset"><i class="fas fa-redo me-1"></i> Reset</button>
                            </div>
                        </div>
                        <div class="zone-alert zone-alert-muted">
                            <i class="fas fa-info-circle"></i> Search box se location dhundo, phir map pe click karke area define karo. Min 3 points required.
                        </div>
                        <input type="hidden" name="polygon" id="polygonInput" value="{{ $polygonJson }}">
                        <div id="polygonMap" class="zone-map"></div>
                        <div class="zone-poly-meta">
                            <span><i class="fas fa-map-marker-alt me-1 text-danger"></i> Points: <strong id="polyCount">0</strong></span>
                            <span><i class="fas fa-vector-square me-1 text-primary"></i> Area: <strong id="polyArea">—</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="zone-actions d-flex gap-2 flex-wrap mt-4">
                <button type="submit" class="btn btn-zone-save"><i class="fas fa-save me-2"></i>{{ $zone->exists ? 'Update Zone' : 'Save Zone' }}</button>
                <a href="{{ route('admin.delivery-zones.index') }}" class="btn btn-zone-cancel"><i class="fas fa-undo me-2"></i>Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom_js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function () {
    'use strict';

    var DEFAULT_CENTER = [28.6139, 77.2090];
    var form = document.getElementById('zoneForm');
    var typeSelect = document.getElementById('zoneType');
    var panels = document.querySelectorAll('.zone-panel');
    var maps = {};

    function tiles(map) {
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
    }

    function searchControl(map, onPick) {
        var Control = L.Control.extend({
            options: { position: 'topright' },
            onAdd: function () {
                var wrap = L.DomUtil.create('div', 'zone-search');
                wrap.innerHTML = '<button type="button" class="zone-search-btn" title="Search location"><i class="fas fa-search"></i></button>'
                    + '<div class="zone-search-box"><input type="text" placeholder="Search area, city..."><ul class="zone-search-results"></ul></div>';
                L.DomEvent.disableClickPropagation(wrap);
                L.DomEvent.disableScrollPropagation(wrap);

                var btn = wrap.querySelector('.zone-search-btn');
                var input = wrap.querySelector('input');
                var list = wrap.querySelector('.zone-search-results');
                var timer = null;

                btn.addEventListener('click', function () {
                    wrap.classList.toggle('open');
                    if (wrap.classList.contains('open')) input.focus();
                });
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') e.preventDefault();
                });
                input.addEventListener('input', function () {
                    clearTimeout(timer);
                    var q = input.value.trim();
                    if (q.length < 3) { list.innerHTML = ''; return; }
                    timer = setTimeout(function () {
                        list.innerHTML = '<li class="muted">Searching…</li>';
                        fetch('https://photon.komoot.io/api/?limit=6&lang=en&bbox=68.1,6.5,97.4,35.7&q=' + encodeURIComponent(q))
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                var feats = (data && data.features) || [];
                                if (!feats.length) { list.innerHTML = '<li class="muted">Kuch nahi mila</li>'; return; }
                                list.innerHTML = '';
                                feats.forEach(function (f) {
                                    var p = f.properties || {};
                                    var label = [p.name, p.street, p.district, p.city, p.state, p.postcode]
                                        .filter(function (v, i, a) { return v && a.indexOf(v) === i; }).join(', ');
                                    var li = document.createElement('li');
                                    li.textContent = label;
                                    li.addEventListener('click', function () {
                                        var c = f.geometry.coordinates;
                                        onPick(c[1], c[0], label);
                                        list.innerHTML = '';
                                        input.value = label;
                                        wrap.classList.remove('open');
                                    });
                                    list.appendChild(li);
                                });
                            })
                            .catch(function () { list.innerHTML = '<li class="muted">Search abhi available nahi</li>'; });
                    }, 400);
                });
                return wrap;
            }
        });
        map.addControl(new Control());
    }

    /* ---------- Radius ---------- */
    function initRadius() {
        var latIn = document.getElementById('centerLat');
        var lngIn = document.getElementById('centerLng');
        var kmIn = document.getElementById('radiusKm');
        var addrIn = document.getElementById('centerAddress');

        var lat = parseFloat(latIn.value), lng = parseFloat(lngIn.value);
        var start = (isFinite(lat) && isFinite(lng)) ? [lat, lng] : DEFAULT_CENTER;
        var map = L.map('radiusMap').setView(start, 12);
        tiles(map);

        var marker = L.marker(start, { draggable: true }).addTo(map).bindPopup('<strong>Delivery Center</strong>');
        var circle = L.circle(start, { radius: 1000, color: '#1a73e8', weight: 2, fillColor: '#1a73e8', fillOpacity: 0.08 }).addTo(map);

        function km() {
            var v = parseFloat(kmIn.value);
            return isFinite(v) && v > 0 ? v : 0;
        }

        function reverse(la, ln) {
            fetch('https://photon.komoot.io/reverse?lang=en&lat=' + la + '&lon=' + ln)
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    var p = ((d && d.features && d.features[0]) || {}).properties || {};
                    var label = [p.name, p.district || p.locality, p.city, p.state]
                        .filter(function (v, i, a) { return v && a.indexOf(v) === i; }).join(', ');
                    if (label) addrIn.value = label;
                })
                .catch(function () {});
        }

        function setCenter(la, ln, fit, label) {
            latIn.value = la.toFixed(6);
            lngIn.value = ln.toFixed(6);
            marker.setLatLng([la, ln]);
            circle.setLatLng([la, ln]);
            if (label) { addrIn.value = label; } else { reverse(la, ln); }
            if (fit) redraw(true);
        }

        function redraw(fit) {
            var la = parseFloat(latIn.value), ln = parseFloat(lngIn.value);
            if (!isFinite(la) || !isFinite(ln)) return;
            marker.setLatLng([la, ln]);
            circle.setLatLng([la, ln]);
            circle.setRadius(km() * 1000);
            if (fit && km() > 0) map.fitBounds(circle.getBounds(), { padding: [20, 20] });
        }

        map.on('click', function (e) { setCenter(e.latlng.lat, e.latlng.lng, false); marker.openPopup(); });
        marker.on('dragend', function () { var p = marker.getLatLng(); setCenter(p.lat, p.lng, false); });
        [latIn, lngIn].forEach(function (el) { el.addEventListener('change', function () { redraw(true); }); });
        kmIn.addEventListener('input', function () { redraw(false); });
        kmIn.addEventListener('change', function () { redraw(true); });
        searchControl(map, function (la, ln, label) { setCenter(la, ln, true, label); });

        redraw(true);
        marker.openPopup();
        return map;
    }

    /* ---------- Polygon ---------- */
    function initPolygon() {
        var hidden = document.getElementById('polygonInput');
        var countEl = document.getElementById('polyCount');
        var areaEl = document.getElementById('polyArea');
        var points = [];
        try { points = JSON.parse(hidden.value || '[]') || []; } catch (e) { points = []; }

        var map = L.map('polygonMap').setView(points.length ? points[0] : DEFAULT_CENTER, 12);
        tiles(map);
        var shape = L.polygon([], { color: '#6f42c1', weight: 2, fillColor: '#6f42c1', fillOpacity: 0.15 }).addTo(map);
        var vertexLayer = L.layerGroup().addTo(map);

        function areaKm2(pts) {
            if (pts.length < 3) return 0;
            var R = 6371, sum = 0;
            for (var i = 0; i < pts.length; i++) {
                var a = pts[i], b = pts[(i + 1) % pts.length];
                sum += (b[1] - a[1]) * Math.PI / 180 * (2 + Math.sin(a[0] * Math.PI / 180) + Math.sin(b[0] * Math.PI / 180));
            }
            return Math.abs(sum * R * R / 2);
        }

        function render() {
            shape.setLatLngs(points);
            vertexLayer.clearLayers();
            points.forEach(function (p, idx) {
                var m = L.circleMarker(p, { radius: 6, color: '#fff', weight: 2, fillColor: idx === 0 ? '#dc3545' : '#6f42c1', fillOpacity: 1 });
                m.bindTooltip('Point ' + (idx + 1) + ' — click to remove');
                m.on('click', function (e) {
                    L.DomEvent.stopPropagation(e);
                    points.splice(idx, 1);
                    render();
                });
                vertexLayer.addLayer(m);
            });
            hidden.value = points.length ? JSON.stringify(points) : '';
            countEl.textContent = points.length;
            countEl.style.color = points.length >= 3 ? '#198754' : '#dc3545';
            var a = areaKm2(points);
            areaEl.textContent = a > 0 ? a.toFixed(2) + ' km²' : '—';
        }

        map.on('click', function (e) {
            points.push([+e.latlng.lat.toFixed(6), +e.latlng.lng.toFixed(6)]);
            render();
        });
        document.getElementById('polyUndo').addEventListener('click', function () { points.pop(); render(); });
        document.getElementById('polyReset').addEventListener('click', function () {
            if (!points.length || confirm('Saare points hata dein?')) { points = []; render(); }
        });
        searchControl(map, function (la, ln) { map.setView([la, ln], 14); });

        render();
        if (points.length >= 3) map.fitBounds(shape.getBounds(), { padding: [20, 20] });
        return map;
    }

    /* ---------- Pincode rows ---------- */
    var pinRows = document.getElementById('pinRows');
    var pinCount = document.getElementById('pinCount');
    var pinUrl = form.dataset.pincodeUrl;

    function reindexPins() {
        var rows = pinRows.querySelectorAll('.zone-pin-row');
        rows.forEach(function (row, i) {
            row.querySelectorAll('input').forEach(function (input) {
                input.name = input.name.replace(/pincodes\[\d+\]/, 'pincodes[' + i + ']');
            });
        });
        var filled = Array.prototype.filter.call(rows, function (r) { return /^\d{6}$/.test(r.querySelector('.pin-code').value); }).length;
        pinCount.textContent = filled + ' pincode' + (filled === 1 ? '' : 's') + ' added';
    }

    function addPinRow() {
        var tpl = pinRows.querySelector('.zone-pin-row');
        var row = tpl.cloneNode(true);
        row.querySelectorAll('input').forEach(function (i) { i.value = ''; i.classList.remove('is-invalid'); });
        pinRows.appendChild(row);
        reindexPins();
        row.querySelector('.pin-code').focus();
    }

    document.getElementById('addPinRow').addEventListener('click', addPinRow);

    pinRows.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove');
        if (!btn) return;
        var rows = pinRows.querySelectorAll('.zone-pin-row');
        var row = btn.closest('.zone-pin-row');
        if (rows.length === 1) {
            row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        } else {
            row.remove();
        }
        reindexPins();
    });

    pinRows.addEventListener('input', function (e) {
        if (!e.target.classList.contains('pin-code')) return;
        var input = e.target;
        input.value = input.value.replace(/\D/g, '').slice(0, 6);
        reindexPins();
        if (input.value.length !== 6) return;

        var codes = Array.prototype.map.call(pinRows.querySelectorAll('.pin-code'), function (i) { return i.value; });
        var dup = codes.filter(function (c) { return c === input.value; }).length > 1;
        input.classList.toggle('is-invalid', dup);
        if (dup) return;

        var row = input.closest('.zone-pin-row');
        input.classList.add('pin-loading');
        fetch(pinUrl.replace('__PIN__', input.value), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                var area = row.querySelector('.pin-area');
                var city = row.querySelector('.pin-city');
                var state = row.querySelector('.pin-state');
                if (!area.value && d.area) area.value = d.area;
                if (!city.value && d.city) city.value = d.city;
                if (!state.value && d.state) state.value = d.state;
            })
            .catch(function () {})
            .finally(function () { input.classList.remove('pin-loading'); });
    });

    /* ---------- Type switch ---------- */
    function showPanel(type) {
        panels.forEach(function (p) { p.hidden = p.dataset.panel !== type; });
        if (typeof L === 'undefined') return;
        if (type === 'radius') { maps.radius = maps.radius || initRadius(); setTimeout(function () { maps.radius.invalidateSize(); }, 50); }
        if (type === 'polygon') { maps.polygon = maps.polygon || initPolygon(); setTimeout(function () { maps.polygon.invalidateSize(); }, 50); }
    }

    typeSelect.addEventListener('change', function () { showPanel(typeSelect.value); });

    form.addEventListener('submit', function (e) {
        var type = typeSelect.value;
        if (type === 'polygon') {
            var pts = [];
            try { pts = JSON.parse(document.getElementById('polygonInput').value || '[]'); } catch (err) {}
            if (pts.length < 3) { e.preventDefault(); alert('Polygon ke liye map par kam se kam 3 points click karein.'); }
        }
        if (type === 'radius' && (!document.getElementById('centerLat').value || !document.getElementById('centerLng').value)) {
            e.preventDefault();
            alert('Map par click karke center set karein.');
        }
        if (type === 'pincode') {
            var any = Array.prototype.some.call(pinRows.querySelectorAll('.pin-code'), function (i) { return /^\d{6}$/.test(i.value); });
            if (!any) { e.preventDefault(); alert('Kam se kam ek 6-digit pincode add karein.'); return; }
            pinRows.querySelectorAll('.zone-pin-row').forEach(function (row) {
                if (pinRows.querySelectorAll('.zone-pin-row').length > 1 && !row.querySelector('.pin-code').value) row.remove();
            });
            reindexPins();
        }
    });

    reindexPins();
    showPanel(typeSelect.value);
})();
</script>
@endsection
