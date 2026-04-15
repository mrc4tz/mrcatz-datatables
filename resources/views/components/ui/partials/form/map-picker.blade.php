{{-- Map picker field — coordinate (+ optional radius) picker. Supports
     two providers via the `provider` factory parameter:
       'leaflet' (default, OpenStreetMap, no API key)
       'google'  (Google Maps JS SDK, requires apiKey)

     Bound value is a single array property:
         public array $location = ['lat' => null, 'lng' => null];
         // or with showRadius:
         public array $location = ['lat' => null, 'lng' => null, 'radius' => null];

     Each provider's init + marker/circle logic is branched at runtime
     inside the x-init below. Both share the exact same DOM skeleton,
     slider, geolocation button, and Livewire wire-sync so switching
     providers doesn't require any template edits. --}}
@php
    $mapId          = 'mrcatz-map-' . $id;
    $provider       = $field['mapProvider'] ?? 'leaflet';
    $apiKey         = $field['mapApiKey'] ?? null;
    $defaultLat     = $field['defaultLat'] ?? -6.2088;
    $defaultLng     = $field['defaultLng'] ?? 106.8456;
    $defaultZoom   = $field['defaultZoom'] ?? 13;
    $showRadius    = (bool) ($field['showRadius'] ?? false);
    $defaultRadius = $field['defaultRadius'] ?? 500;
    $minRadius     = $field['minRadius'] ?? 10;
    $maxRadius     = $field['maxRadius'] ?? 10000;
    $mapHeight     = $field['mapHeight'] ?? '320px';
    $forceTheme    = $field['mapForceTheme'] ?? null; // 'light' | 'dark' | null — Leaflet only

    // Build the provider init snippet as PHP so the x-init expression
    // stays free of Blade @if (which would leak Livewire BLOCK
    // comments into the JS and break Alpine's parser).
    $googleSrc = $provider === 'google'
        ? 'https://maps.googleapis.com/maps/api/js?key=' . urlencode($apiKey) . '&v=weekly'
        : '';
@endphp

<style>
    /* `isolation: isolate` + explicit z-index:0 creates a new stacking
       context so Leaflet's internal z-indices (tile pane 200, controls
       400, popups 700) stay CONTAINED — they can no longer stack above
       the host app's navbar / modal / sticky header that sits in a
       lower outer context.

       Border color mirrors DaisyUI's .input look (base-content at 20%
       alpha via color-mix) so the map sits next to text inputs / select
       fields as if they were siblings. Error state flips to the theme's
       error color via the --mrcatz-map-error flag toggled by the Blade
       @@error directive below. */
    /* Outer wrapper holds the border so the whole control (map +
       action row + collapsible inputs + radius slider) reads as a
       single bordered field like the other form variants. */
    .mrcatz-map-wrap {
        border-radius: var(--radius-field, 0.5rem);
        border: 1px solid color-mix(in oklab, var(--color-base-content) 20%, transparent);
        background-color: var(--color-base-100);
        overflow: hidden;
        transition: border-color 150ms ease;
    }
    .mrcatz-map-wrap.mrcatz-map-error {
        border-color: var(--color-error, #ef4444);
    }
    /* Inner padding area wrapping the non-map UI (actions, slider, inputs). */
    .mrcatz-map-controls { padding: 0.625rem 0.75rem 0.75rem; }

    /* Map container inherits no border itself — border lives on the
       outer wrap — but still needs isolation so Leaflet's z-indexes
       stay contained below navbar / modal / sticky header. The top
       radius is inherited through overflow:hidden on the wrap; we
       still set bottom-straight edges because controls sit below. */
    .mrcatz-map-container {
        position: relative; isolation: isolate; z-index: 0;
        overflow: hidden;
        border-bottom: 1px solid color-mix(in oklab, var(--color-base-content) 15%, transparent);
    }
    .mrcatz-map-container .mrcatz-map { height: {{ $mapHeight }}; width: 100%; }
    .mrcatz-map-coords {
        position: absolute; bottom: 8px; left: 8px; z-index: 400;
        background: rgba(255,255,255,0.92); backdrop-filter: blur(4px);
        padding: 4px 10px; border-radius: 9999px; font-size: 11px;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        color: #1f2937; box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        pointer-events: none;
    }
    [data-theme*="dark"] .mrcatz-map-coords {
        background: rgba(31,41,55,0.92); color: #e5e7eb;
    }
</style>

<fieldset class="fieldset" id="fieldset-{{ $mapId }}">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">
        @if($field['icon'])
            <span class="inline-flex items-center gap-2">
                {!! mrcatz_form_icon($field['icon'], 'text-base-content/60 text-base') !!}
                {{ $field['label'] }}
            </span>
        @else
            {{ $field['label'] }}
        @endif
    </legend>

    <div class="mrcatz-map-wrap @error($errorKey ?? $id) mrcatz-map-error @enderror @error(($errorKey ?? $id).'.lat') mrcatz-map-error @enderror @error(($errorKey ?? $id).'.lng') mrcatz-map-error @enderror @error(($errorKey ?? $id).'.radius') mrcatz-map-error @enderror">
    <div class="mrcatz-map-container"
         x-data="mrcatzMapPicker({
             fieldId: @js($id),
             provider: @js($provider),
             googleSrc: @js($googleSrc),
             defaultLat: {{ (float) $defaultLat }},
             defaultLng: {{ (float) $defaultLng }},
             defaultZoom: {{ (int) $defaultZoom }},
             showRadius: {{ $showRadius ? 'true' : 'false' }},
             defaultRadius: {{ (int) $defaultRadius }},
             disabled: {{ $disabled ? 'true' : 'false' }},
             forceTheme: @js($forceTheme),
         })"
         x-init="init()"
         wire:ignore>
        <div id="{{ $mapId }}" class="mrcatz-map"></div>

        {{-- Live coord readout — pinned bottom-left of the map. --}}
        <div class="mrcatz-map-coords" x-text="coordsLabel()"></div>
    </div>

    {{-- `expanded` lives on the outer controls wrapper so both the
         action toggle AND the radius label below can react to it (the
         radius value badge hides once the Manual input panel is open —
         the user already sees a precise number input there, so the
         slider badge is redundant). --}}
    <div class="mrcatz-map-controls" x-data="{ expanded: false }">
    <div>
        @unless($disabled)
        {{-- Action row. At narrow widths (span(6), mobile) the three
             buttons would overflow; grid-cols-[auto_auto_1fr] keeps
             Use-my-location + Clear compact on the left and pushes
             the Manual-input toggle to the far right without
             relying on ml-auto that breaks on wrap. Labels hide
             below xs so icons alone are enough. --}}
        <div class="flex flex-wrap items-center gap-1 gap-y-2 justify-between">
            <div class="flex flex-wrap items-center gap-1">
                <button type="button"
                        class="btn btn-ghost btn-xs gap-1 px-2"
                        @click="$dispatch('mrcatz-map-locate-{{ $id }}')">
                    {!! mrcatz_form_icon('pin', 'w-4 h-4 shrink-0') !!}
                    <span class="hidden xs:inline sm:inline">{{ mrcatz_lang('map_use_my_location') }}</span>
                </button>
                <button type="button"
                        class="btn btn-ghost btn-xs gap-1 px-2 text-base-content/60"
                        @click="$dispatch('mrcatz-map-clear-{{ $id }}')">
                    {!! mrcatz_form_icon('close', 'w-4 h-4 shrink-0') !!}
                    <span class="hidden xs:inline sm:inline">{{ mrcatz_lang('map_clear') }}</span>
                </button>
            </div>
            <button type="button"
                    class="btn btn-ghost btn-xs gap-1 px-2 text-base-content/60"
                    @click="expanded = !expanded"
                    :aria-expanded="expanded">
                <span class="hidden sm:inline" x-text="expanded
                    ? @js(mrcatz_lang('map_hide_inputs'))
                    : @js(mrcatz_lang('map_show_inputs'))"></span>
                <span class="inline-flex items-center transition-transform duration-200 shrink-0"
                      :class="expanded ? 'rotate-180' : ''">
                    {!! mrcatz_form_icon('expand_more', 'w-4 h-4') !!}
                </span>
            </button>
        </div>
        @endunless

        {{-- Manual lat/lng number inputs — collapsed by default. Debounced
             wire:model.live so the field doesn't saturate the server with
             a request per keystroke; the map's $wire.$watch already
             listens for property changes and moves the marker when the
             user finishes typing. --}}
        <div x-show="expanded"
             x-cloak
             x-collapse
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid @if($showRadius) grid-cols-2 sm:grid-cols-3 @else grid-cols-2 @endif gap-2 mt-2">
                <label class="flex flex-col gap-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-base-content/60">Latitude</span>
                    <input type="number"
                           step="0.000001"
                           min="-90"
                           max="90"
                           inputmode="decimal"
                           class="input input-bordered input-sm w-full text-sm tabular-nums"
                           wire:model.live.debounce.400ms="{{ $id }}.lat"
                           placeholder="-6.208800"
                           @if($disabled) disabled @endif />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-base-content/60">Longitude</span>
                    <input type="number"
                           step="0.000001"
                           min="-180"
                           max="180"
                           inputmode="decimal"
                           class="input input-bordered input-sm w-full text-sm tabular-nums"
                           wire:model.live.debounce.400ms="{{ $id }}.lng"
                           placeholder="106.845600"
                           @if($disabled) disabled @endif />
                </label>
                @if($showRadius)
                    <label class="flex flex-col gap-1">
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-base-content/60">Radius (m)</span>
                        <input type="number"
                               step="1"
                               min="{{ (int) $minRadius }}"
                               max="{{ (int) $maxRadius }}"
                               inputmode="numeric"
                               class="input input-bordered input-sm w-full text-sm tabular-nums"
                               wire:model.live.debounce.400ms="{{ $id }}.radius"
                               {{-- Clamp on blur so typing beyond range snaps
                                    back to the slider's min/max instead of
                                    silently storing an out-of-range value. --}}
                               @change="
                                   const v = Number($event.target.value);
                                   const clamped = Math.min({{ (int) $maxRadius }}, Math.max({{ (int) $minRadius }}, isNaN(v) ? {{ (int) $defaultRadius }} : v));
                                   if (v !== clamped) { $event.target.value = clamped; $wire.set('{{ $id }}.radius', clamped); }
                               "
                               placeholder="{{ (int) $defaultRadius }}"
                               @if($disabled) disabled @endif />
                    </label>
                @endif
            </div>
        </div>
    </div>

    @if($showRadius)
        {{-- Radius: slider (quick) + number input (precise). Both bind to
             the same wire property via live debounce, and the map's
             $wire.$watch redraws the circle on every change regardless
             of which control the user interacted with. --}}
        <fieldset class="fieldset mt-3"
            x-data="{ r: Number($wire.get('{{ $id }}.radius') ?? {{ (int) $defaultRadius }}) }"
            x-init="$wire.$watch('{{ $id }}.radius', (v) => { this.r = Number(v ?? {{ (int) $defaultRadius }}); })">
            <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">
                {{ mrcatz_lang('map_radius') }}
            </legend>
            {{-- Slider-only at any width. Precise radius input lives in
                 the collapsible Manual input panel above (next to
                 Latitude / Longitude) — keeps this row uncluttered on
                 span(6) / mobile where a side-by-side number input
                 would push the slider off-screen. --}}
            <input type="range"
                   class="range range-primary w-full @if($disabled) opacity-60 @endif"
                   wire:model.live="{{ $id }}.radius"
                   @input="r = Number($event.target.value)"
                   min="{{ (int) $minRadius }}"
                   max="{{ (int) $maxRadius }}"
                   step="1"
                   @if($disabled) disabled @endif />
            <div class="flex justify-between text-xs text-base-content/50 px-1">
                <span>{{ (int) $minRadius }} m</span>
                <span class="tabular-nums" x-text="r + ' m'" x-show="!expanded"></span>
                <span>{{ (int) $maxRadius }} m</span>
            </div>
        </fieldset>
    @endif
    </div>{{-- /.mrcatz-map-controls --}}
    </div>{{-- /.mrcatz-map-wrap --}}

    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>

{{-- Alpine component factory. Wrapped in @@assets so Livewire runs it
     exactly once per page, INCLUDING when this partial is rendered
     inside a component that morphs in via lazy-load or full-page form
     mode (\$modalFullScreen = true). A plain <script> tag inside
     morphed HTML does not execute on its own, so without @@assets the
     factory is never registered and Alpine throws
     "mrcatzMapPicker is not defined". --}}
@assets
<script>
if (typeof window.mrcatzMapPicker === 'undefined') {
    window.mrcatzMapPicker = function (config) {
        // ─── Lazy asset loaders ─────────────────────────────────────
        const loadOnce = (key, attach) => new Promise((resolve) => {
            if (window['__mrcatz_' + key]) {
                window['__mrcatz_' + key].then(resolve);
                return;
            }
            window['__mrcatz_' + key] = new Promise((r) => attach(r));
            window['__mrcatz_' + key].then(resolve);
        });
        const loadScript = (src) => new Promise((resolve) => {
            if (document.querySelector('script[data-mrcatz-src="' + src + '"]')) {
                // Already injected — wait for it.
                const existing = document.querySelector('script[data-mrcatz-src="' + src + '"]');
                if (existing.dataset.mrcatzLoaded) return resolve();
                existing.addEventListener('load', () => resolve(), { once: true });
                return;
            }
            const s = document.createElement('script');
            s.src = src;
            s.async = true;
            s.dataset.mrcatzSrc = src;
            s.onload = () => { s.dataset.mrcatzLoaded = '1'; resolve(); };
            document.head.appendChild(s);
        });
        const loadStylesheet = (href) => new Promise((resolve) => {
            if (document.querySelector('link[data-mrcatz-href="' + href + '"]')) return resolve();
            const l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = href;
            l.dataset.mrcatzHref = href;
            l.onload = () => resolve();
            document.head.appendChild(l);
            // Fire resolve anyway — CSS failures shouldn't block init.
            setTimeout(resolve, 50);
        });

        const ensureLeaflet = () => loadOnce('leaflet', async (done) => {
            if (typeof window.L === 'undefined') {
                await loadStylesheet('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
                await loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
            }
            done();
        });
        const ensureGoogle = (src) => loadOnce('google', async (done) => {
            if (typeof window.google === 'undefined' || !window.google.maps) {
                await loadScript(src);
            }
            done();
        });

        // ─── Component state + methods ──────────────────────────────
        return {
            _map: null,
            _marker: null,
            _circle: null,
            _provider: null,
            _initialized: false,

            async init() {
                // Guard against double-init. Alpine can re-fire x-init
                // after a Livewire morph even inside wire:ignore if a
                // different ancestor re-walks the tree; Leaflet then
                // throws "Map container is already initialized". Set the
                // flag up front so re-entrant calls bail out immediately.
                if (this._initialized) return;
                this._initialized = true;

                // Also cheap-mark the DOM element — once Leaflet has
                // taken it over, `_leaflet_id` is a non-undefined
                // integer, so re-attempting init on the same element
                // becomes a no-op even if something bypassed this
                // component's state (e.g. a full morph that drops our
                // Alpine instance but keeps the DOM node).
                const el = document.getElementById('mrcatz-map-' + config.fieldId);
                if (el && (el._mrcatzMapClaimed || el._leaflet_id != null)) return;
                if (el) el._mrcatzMapClaimed = true;

                // Sync current state from Livewire. Falls back to defaults
                // if the bound property is empty (user hasn't picked yet).
                const initial = this.$wire.get(config.fieldId) || {};
                const lat = Number(initial.lat ?? config.defaultLat);
                const lng = Number(initial.lng ?? config.defaultLng);
                const radius = Number(initial.radius ?? config.defaultRadius);

                this._provider = config.provider;
                if (this._provider === 'google') {
                    await ensureGoogle(config.googleSrc);
                    this._initGoogle(lat, lng, radius);
                } else {
                    await ensureLeaflet();
                    this._initLeaflet(lat, lng, radius);
                }

                // Browser-event listeners for the action buttons (these
                // live OUTSIDE the x-data container, so we use custom
                // events dispatched per field id).
                window.addEventListener('mrcatz-map-locate-' + config.fieldId, () => this.locate());
                window.addEventListener('mrcatz-map-clear-' + config.fieldId,  () => this.clear());

                // Respond to external property changes (form reset, etc).
                this.$wire.$watch(config.fieldId, (value) => {
                    if (!value) return;
                    const nlat = Number(value.lat ?? NaN);
                    const nlng = Number(value.lng ?? NaN);
                    if (!isFinite(nlat) || !isFinite(nlng)) return;
                    this._setCenter(nlat, nlng, /*push*/ false);
                    if (config.showRadius && value.radius != null) {
                        this._setRadius(Number(value.radius), /*push*/ false);
                    }
                });

                // Seed Livewire with the initial (default) values so the
                // form has a valid submission even if the user doesn't
                // interact. Only fill keys that are currently missing.
                if (!initial.lat || !initial.lng) {
                    this._push(lat, lng, radius);
                }
            },

            // ─── Leaflet ────────────────────────────────────────────
            _initLeaflet(lat, lng, radius) {
                const id = 'mrcatz-map-' + config.fieldId;
                this._map = L.map(id, { dragging: !config.disabled, zoomControl: !config.disabled })
                    .setView([lat, lng], config.defaultZoom);

                // Tile layer — swap to dark variant when DaisyUI theme is dark.
                // `forceTheme` ('light' | 'dark') overrides the data-theme sniff
                // so the map can be pinned regardless of the host app's theme.
                const dark = config.forceTheme === 'dark'
                    ? true
                    : config.forceTheme === 'light'
                        ? false
                        : !!document.documentElement?.getAttribute('data-theme')?.includes('dark');
                const tileUrl = dark
                    ? 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/dark_all/{z}/{x}/{y}.png'
                    : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                L.tileLayer(tileUrl, {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap' + (dark ? ' &copy; CARTO' : ''),
                }).addTo(this._map);

                this._marker = L.marker([lat, lng], { draggable: !config.disabled }).addTo(this._map);
                if (config.showRadius) {
                    this._circle = L.circle([lat, lng], { radius, color: 'var(--color-primary, #2563eb)', fillOpacity: 0.15 }).addTo(this._map);
                }

                if (!config.disabled) {
                    this._map.on('click', (e) => this._setCenter(e.latlng.lat, e.latlng.lng, true));
                    this._marker.on('dragend', () => {
                        const { lat, lng } = this._marker.getLatLng();
                        this._setCenter(lat, lng, true);
                    });
                }
            },

            // ─── Google Maps ────────────────────────────────────────
            _initGoogle(lat, lng, radius) {
                const id = 'mrcatz-map-' + config.fieldId;
                this._map = new google.maps.Map(document.getElementById(id), {
                    center: { lat, lng },
                    zoom: config.defaultZoom,
                    disableDefaultUI: false,
                    draggable: !config.disabled,
                    clickableIcons: false,
                });
                this._marker = new google.maps.Marker({
                    position: { lat, lng },
                    map: this._map,
                    draggable: !config.disabled,
                });
                if (config.showRadius) {
                    this._circle = new google.maps.Circle({
                        map: this._map,
                        center: { lat, lng },
                        radius,
                        strokeColor: '#2563eb',
                        fillColor: '#2563eb',
                        fillOpacity: 0.15,
                    });
                }

                if (!config.disabled) {
                    this._map.addListener('click', (e) => {
                        this._setCenter(e.latLng.lat(), e.latLng.lng(), true);
                    });
                    this._marker.addListener('dragend', (e) => {
                        this._setCenter(e.latLng.lat(), e.latLng.lng(), true);
                    });
                }
            },

            // ─── Shared operations ──────────────────────────────────
            _setCenter(lat, lng, push) {
                if (this._provider === 'google') {
                    const pos = { lat, lng };
                    this._marker.setPosition(pos);
                    if (this._circle) this._circle.setCenter(pos);
                    this._map.panTo(pos);
                } else {
                    this._marker.setLatLng([lat, lng]);
                    if (this._circle) this._circle.setLatLng([lat, lng]);
                    this._map.panTo([lat, lng]);
                }
                if (push) this._push(lat, lng);
            },

            _setRadius(radius, push) {
                if (!this._circle) return;
                if (this._provider === 'google') this._circle.setRadius(radius);
                else this._circle.setRadius(radius);
                if (push) {
                    const v = this.$wire.get(config.fieldId) || {};
                    this.$wire.set(config.fieldId + '.radius', radius);
                }
            },

            _push(lat, lng, radius) {
                const payload = { lat, lng };
                if (config.showRadius) {
                    payload.radius = radius != null
                        ? radius
                        : Number(this.$wire.get(config.fieldId + '.radius') ?? config.defaultRadius);
                }
                this.$wire.set(config.fieldId, payload);
            },

            locate() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition((pos) => {
                    this._setCenter(pos.coords.latitude, pos.coords.longitude, true);
                });
            },

            clear() {
                // Reset to defaults
                this._setCenter(config.defaultLat, config.defaultLng, true);
            },

            coordsLabel() {
                const v = this.$wire.get(config.fieldId) || {};
                const lat = Number(v.lat ?? config.defaultLat);
                const lng = Number(v.lng ?? config.defaultLng);
                return lat.toFixed(5) + ', ' + lng.toFixed(5);
            },
        };
    };
}
</script>
@endassets
