{{-- Date range field — clickable trigger + popover with quick presets.
     Binds to a single component property as an associative array:
       public array $period = ['from' => null, 'to' => null];
--}}
@php
    $dateFormat = $field['dateFormat'] ?? 'date';
    $htmlType   = match ($dateFormat) {
        'datetime'   => 'datetime-local',
        'month_year' => 'month',
        'year'       => 'number',
        default      => 'date',
    };
    $minAttr = $field['min'] ?? '';
    $maxAttr = $field['max'] ?? '';
@endphp
<fieldset class="fieldset"
          x-data="mrcatzFormDateRange({
              fieldId: @js($id),
              initial: ($wire.get(@js($id)) || { from: null, to: null }),
              min: @js($minAttr),
              max: @js($maxAttr),
              labels: @js([
                  'pick'       => mrcatz_lang('filter_date_pick'),
                  'from'       => mrcatz_lang('filter_date_from'),
                  'to'         => mrcatz_lang('filter_date_to'),
                  'apply'      => mrcatz_lang('filter_date_apply'),
                  'clear'      => mrcatz_lang('filter_date_clear'),
                  'today'      => mrcatz_lang('filter_date_today'),
                  'yesterday'  => mrcatz_lang('filter_date_yesterday'),
                  'last_7'     => mrcatz_lang('filter_date_last_7'),
                  'last_30'    => mrcatz_lang('filter_date_last_30'),
                  'this_month' => mrcatz_lang('filter_date_this_month'),
                  'last_6m'    => mrcatz_lang('filter_date_last_6m'),
                  'this_year'  => mrcatz_lang('filter_date_this_year'),
                  'last_year'  => mrcatz_lang('filter_date_last_year'),
              ]),
          })"
          @keydown.escape.window="open = false"
          @click.outside="if (! ($refs.popover && $refs.popover.contains($event.target))) open = false">

    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>

    {{-- Wrapper styled exactly like input.blade.php's <label class="input ..."> --}}
    <div class="relative">
        <button type="button"
                x-ref="trigger"
                @click="togglePopover()"
                @if($disabled) disabled @endif
                class="input input-bordered w-full flex items-center gap-3 text-left transition-all duration-200 focus-within:shadow-sm
                       @error($id) input-error @enderror
                       @if($disabled) opacity-60 bg-base-200 @endif"
                :class="{ 'input-primary': open }">
            @if($field['icon'])
                <span class="text-base-content/40 text-lg shrink-0">{!! mrcatz_form_icon($field['icon'], 'text-base-content/40 text-lg') !!}</span>
            @endif

            <span class="grow text-sm min-w-0 truncate"
                  :class="hasValue() ? 'text-base-content' : 'text-base-content/40'"
                  x-text="triggerText()"></span>

            <span class="shrink-0 flex items-center gap-1">
                <span x-show="hasValue()"
                      role="button"
                      tabindex="0"
                      @click.stop="clear()"
                      @keydown.enter.stop.prevent="clear()"
                      @keydown.space.stop.prevent="clear()"
                      class="hover:bg-base-200 rounded-full p-0.5 transition cursor-pointer inline-flex items-center justify-center text-base-content/40 hover:text-base-content/70"
                      :title="labels.clear">
                    {!! mrcatz_form_icon('close', 'text-base-content/40 w-4 h-4') !!}
                </span>
                {!! mrcatz_form_icon('expand_more', 'text-base-content/40 w-5 h-5') !!}
            </span>
        </button>

        {{-- Popover — teleported to <body> so it escapes any `overflow: hidden`
             ancestor (modal dialogs, cards, scroll containers, etc.) and uses
             position: fixed with coords computed from the trigger rect. --}}
        <template x-teleport="body">
        <div x-show="open"
             x-ref="popover"
             :style="popoverStyle"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed z-[100] w-[22rem] bg-base-100 rounded-xl shadow-2xl border border-base-300 overflow-hidden"
             x-cloak>

            <div class="grid grid-cols-[7.5rem_1fr]">
                {{-- Shortcuts column --}}
                <div class="bg-base-200/50 border-r border-base-300 py-2 max-h-[20rem] overflow-y-auto">
                    <template x-for="preset in presets" :key="preset.key">
                        <button type="button"
                                @click="applyPreset(preset.key)"
                                class="block w-full text-left px-3 py-1.5 text-xs hover:bg-base-300/50 transition-colors"
                                :class="activePreset === preset.key ? 'bg-primary/10 text-primary font-semibold' : 'text-base-content/70'"
                                x-text="preset.label"></button>
                    </template>
                </div>

                {{-- Date inputs column --}}
                <div class="p-3 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-base-content/60 mb-1" x-text="labels.from"></label>
                        <input type="{{ $htmlType }}"
                               x-ref="fromInput"
                               x-model="draftFrom"
                               @change="activePreset = null"
                               @if($minAttr) min="{{ $minAttr }}" @endif
                               :max="draftTo || @js($maxAttr)"
                               class="input input-bordered input-sm w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-base-content/60 mb-1" x-text="labels.to"></label>
                        <input type="{{ $htmlType }}"
                               x-ref="toInput"
                               x-model="draftTo"
                               @change="activePreset = null"
                               :min="draftFrom || @js($minAttr)"
                               @if($maxAttr) max="{{ $maxAttr }}" @endif
                               class="input input-bordered input-sm w-full text-sm">
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="button"
                                @click="clear(); open = false"
                                class="btn btn-ghost btn-sm flex-1"
                                x-text="labels.clear"></button>
                        <button type="button"
                                @click="apply()"
                                class="btn btn-primary btn-sm flex-1"
                                x-text="labels.apply"></button>
                    </div>
                </div>
            </div>
        </div>
        </template>
    </div>

    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>

{{-- Alpine component definition (idempotent guard) --}}
<script>
if (typeof window.mrcatzFormDateRange === 'undefined') {
    window.mrcatzFormDateRange = function (config) {
        const fmtDate = (d) => {
            if (!(d instanceof Date) || isNaN(d)) return '';
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
        };

        const initial = config.initial && typeof config.initial === 'object'
            ? config.initial
            : { from: null, to: null };

        return {
            open: false,
            from: initial.from || '',
            to: initial.to || '',
            draftFrom: initial.from || '',
            draftTo: initial.to || '',
            min: config.min || '',
            max: config.max || '',
            fieldId: config.fieldId,
            labels: config.labels,
            activePreset: null,
            // Start OFF-SCREEN so that if position compute ever fails
            // (e.g. $refs.trigger not yet registered after a Livewire morph
            // / lazy-load hydration), the popover is never seen at the
            // viewport top-left default of position:fixed.
            popoverStyle: 'top: -9999px; left: -9999px;',

            presets: [
                { key: 'today',      label: config.labels.today      },
                { key: 'yesterday',  label: config.labels.yesterday  },
                { key: 'last_7',     label: config.labels.last_7     },
                { key: 'last_30',    label: config.labels.last_30    },
                { key: 'this_month', label: config.labels.this_month },
                { key: 'last_6m',    label: config.labels.last_6m    },
                { key: 'this_year',  label: config.labels.this_year  },
                { key: 'last_year',  label: config.labels.last_year  },
            ],

            init() {
                // Sync drafts when popover opens + install scroll/resize
                // handlers that reposition the teleported popover.
                this.$watch('open', (v) => {
                    if (v) {
                        this.draftFrom = this.from;
                        this.draftTo = this.to;
                        this._reposition = () => this.positionPopover();
                        window.addEventListener('scroll', this._reposition, true);
                        window.addEventListener('resize', this._reposition);
                    } else if (this._reposition) {
                        window.removeEventListener('scroll', this._reposition, true);
                        window.removeEventListener('resize', this._reposition);
                        this._reposition = null;
                    }
                });
            },

            togglePopover() {
                // Compute position BEFORE flipping open — see datatable-filter
                // blade for the full rationale. tl;dr: avoids the first-frame
                // flash at viewport top-left before $nextTick fires.
                if (!this.open) this.computePosition();
                this.open = !this.open;
                // Safety net for morph/lazy-load edge cases where $refs.trigger
                // wasn't ready on the synchronous call.
                if (this.open) this.$nextTick(() => this.computePosition());
            },

            computePosition() {
                // Fallback to querySelector because $refs.trigger can be
                // momentarily undefined during Livewire morph cycles.
                const trigger = this.$refs.trigger
                    || (this.$el && this.$el.querySelector('[x-ref="trigger"]'));
                if (!trigger) return;
                const rect = trigger.getBoundingClientRect();
                const pw = 352;
                const ph = 340;
                const gap = 4;
                const margin = 8;

                let top = rect.bottom + gap;
                let left = rect.left;

                if (top + ph > window.innerHeight - margin) {
                    top = Math.max(margin, rect.top - ph - gap);
                }
                if (left + pw > window.innerWidth - margin) {
                    left = window.innerWidth - pw - margin;
                }
                if (left < margin) left = margin;

                this.popoverStyle = `top: ${top}px; left: ${left}px;`;
            },

            positionPopover() { this.computePosition(); },

            hasValue() {
                return !!(this.from || this.to);
            },

            triggerText() {
                if (!this.from && !this.to) return this.labels.pick;
                const f = this.from || '…';
                const t = this.to   || '…';
                return `${this.labels.from} ${f}  →  ${this.labels.to} ${t}`;
            },

            applyPreset(key) {
                const today = new Date(); today.setHours(0, 0, 0, 0);
                let from = null, to = null;

                switch (key) {
                    case 'today':
                        from = to = new Date(today);
                        break;
                    case 'yesterday':
                        from = new Date(today); from.setDate(from.getDate() - 1);
                        to = new Date(from);
                        break;
                    case 'last_7':
                        to = new Date(today);
                        from = new Date(today); from.setDate(from.getDate() - 6);
                        break;
                    case 'last_30':
                        to = new Date(today);
                        from = new Date(today); from.setDate(from.getDate() - 29);
                        break;
                    case 'this_month':
                        from = new Date(today.getFullYear(), today.getMonth(), 1);
                        to = new Date(today);
                        break;
                    case 'last_6m':
                        to = new Date(today);
                        from = new Date(today); from.setMonth(from.getMonth() - 6);
                        break;
                    case 'this_year':
                        from = new Date(today.getFullYear(), 0, 1);
                        to = new Date(today);
                        break;
                    case 'last_year':
                        to = new Date(today);
                        from = new Date(today); from.setFullYear(from.getFullYear() - 1);
                        break;
                }

                this.draftFrom = fmtDate(from);
                this.draftTo = fmtDate(to);
                this.activePreset = key;
            },

            apply() {
                // Auto-swap if from > to
                if (this.draftFrom && this.draftTo && this.draftFrom > this.draftTo) {
                    [this.draftFrom, this.draftTo] = [this.draftTo, this.draftFrom];
                }
                this.from = this.draftFrom;
                this.to = this.draftTo;
                this.open = false;

                // Sync to Livewire as a single associative array property
                this.$wire.set(this.fieldId, {
                    from: this.from || null,
                    to: this.to || null,
                });
            },

            clear() {
                this.from = '';
                this.to = '';
                this.draftFrom = '';
                this.draftTo = '';
                this.activePreset = null;

                // Force native input clear (some browsers don't reflect '' via x-model)
                ['fromInput', 'toInput'].forEach((ref) => {
                    const el = this.$refs[ref];
                    if (el) {
                        el.value = '';
                        el.dispatchEvent(new Event('input',  { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });

                // Single atomic Livewire update
                this.$wire.set(this.fieldId, { from: null, to: null });
            },
        };
    };
}
</script>
