{{-- "Brzo dodaj" (ROADMAP Faza 2.4). Alpine modal nad trenutnom stranicom
     (zamagljena pozadina): korisnik izabere tip i doda minimalne podatke; snimi
     šalje fetch POST na /brzo/{key}, modal se zatvori i korisnik ostaje gdje je
     bio (bez navigacije, bez Livewire → bez 419). Tipovi/polja iz
     QuickCaptureRegistry; ikone su native Filament ikone (renderovane server-side).
     Datum koristi flatpickr (d.m.Y H:i, 24h) radi RULES.md §6. --}}
@vite('resources/js/quick-capture.js')

<div
    x-data="{
        open: false,
        items: @js($items),
        postUrlTemplate: @js($postUrlTemplate),
        csrf: @js($csrfToken),
        activeKey: null,
        form: {},
        errors: {},
        saving: false,
        saved: false,
        prefillDate: null,
        // flatpickr instance po imenu polja — treba nam da dugme „Sada\" može
        // postaviti vrijeme i u kalendaru, ne samo u modelu (inače korisnik
        // vidi prazno polje a šalje se vrijednost).
        pickers: {},
        get activeItem() { return this.items.find((i) => i.key === this.activeKey) || null; },
        openModal(date = null) { this.open = true; this.reset(); this.prefillDate = this.normalizeDate(date); },
        reset() { this.activeKey = null; this.form = {}; this.errors = {}; this.saved = false; this.prefillDate = null; this.pickers = {}; },
        // Isto ponašanje kao „Sada\" na klasičnoj formi podsjetnika
        // (ReminderResource: suffixAction → set(due_date, now())).
        setNow(name) {
            const picker = this.pickers[name];
            if (picker) { picker.setDate(new Date(), true); return; }
            // Bez flatpickra (npr. blokiran asset) polje ostaje obično tekstualno,
            // pa vrijednost upisujemo u formatu koji server očekuje.
            const d = new Date();
            const p = (n) => String(n).padStart(2, '0');
            this.form[name] = `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`;
        },
        // Kalendar javlja dan (ili tačan termin) klikom — pretvori u 'Y-m-d H:i'
        // koji očekuje flatpickr; za cijeli dan uzmi 09:00 kao razuman početak.
        normalizeDate(raw) {
            if (! raw) return null;
            if (raw.length <= 10) return raw + ' 09:00';
            return raw.slice(0, 10) + ' ' + raw.slice(11, 16);
        },
        close() { this.open = false; },
        pick(key) {
            this.activeKey = key;
            this.form = {};
            this.errors = {};
            this.saved = false;
            // Datum s kalendara ide uz svaki unos: modul ga sam iskoristi kako mu
            // odgovara (rok zadatka, datum dnevnika, datum troška), i onda kad
            // nema vidljivo polje za njega.
            if (this.prefillDate) {
                this.form.date = this.prefillDate;
            }
            this.$nextTick(() => {
                this.$root.querySelectorAll('[data-qc-datetime], [data-qc-date]').forEach((el) => {
                    if (el._flatpickr || ! window.flatpickr) return;
                    const withTime = el.hasAttribute('data-qc-datetime');
                    const name = el.getAttribute(withTime ? 'data-qc-datetime' : 'data-qc-date');
                    const picker = window.flatpickr(el, {
                        enableTime: withTime,
                        time_24hr: true,
                        dateFormat: withTime ? 'Y-m-d H:i' : 'Y-m-d',
                        altInput: true,
                        altFormat: withTime ? 'd.m.Y H:i' : 'd.m.Y',
                        onChange: (dates, str) => { this.form[name] = str; },
                    });

                    this.pickers[name] = picker;

                    // Dan izabran u kalendaru → već upisan u polje datuma/vremena.
                    if (this.prefillDate) {
                        picker.setDate(withTime ? this.prefillDate : this.prefillDate.slice(0, 10), true);
                    }
                });
                this.$root.querySelector('[data-qc-field]')?.focus();
            });
        },
        async submit() {
            if (this.saving) return;
            this.saving = true;
            this.errors = {};
            try {
                const res = await fetch(this.postUrlTemplate.replace('__KEY__', this.activeKey), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(this.form),
                });
                if (res.ok) {
                    this.saved = true;
                    // Stranice koje prikazuju podatke (kalendar) osvježe se same,
                    // bez navigacije i bez gubitka trenutnog prikaza.
                    window.dispatchEvent(new CustomEvent('homeos-quick-created', {
                        detail: { key: this.activeKey },
                    }));
                    setTimeout(() => { this.close(); this.reset(); }, 700);
                } else if (res.status === 422) {
                    this.errors = (await res.json()).errors || {};
                } else {
                    this.errors = { _: [@js(__('platform.quick_capture.error'))] };
                }
            } catch (e) {
                this.errors = { _: [@js(__('platform.quick_capture.error'))] };
            }
            this.saving = false;
        },
    }"
    x-on:keydown.escape.window="close()"
    x-on:homeos-quick-capture.window="openModal($event.detail?.date)"
>
    <x-filament::button icon="heroicon-m-plus" size="sm" color="primary" x-on:click="openModal()">
        {{ __('platform.quick_capture.button') }}
    </x-filament::button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6" style="display: none;">
        <div
            x-show="open"
            x-transition.opacity
            class="fixed inset-0 bg-gray-950/50 backdrop-blur-sm"
            x-on:click="close()"
        ></div>

        <div x-show="open" x-transition class="relative mx-auto mt-16 w-full max-w-lg">
            <div class="overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 px-4 py-3 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-gray-950 dark:text-white">
                        <span x-show="! activeItem">{{ __('platform.quick_capture.heading') }}</span>
                        <span x-show="activeItem" x-text="activeItem?.label"></span>
                    </h2>
                    <button type="button" x-on:click="close()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                    </button>
                </div>

                <div class="p-4">
                    {{-- Izbor tipa — dugmad renderovana server-side radi native ikona (iz registryja). --}}
                    <div x-show="activeKey === null">
                        @forelse ($items as $item)
                            <button
                                type="button"
                                x-on:click="pick(@js($item['key']))"
                                class="mb-2 flex w-full items-center gap-3 rounded-xl border border-gray-200 p-4 text-start transition hover:border-primary-500 hover:bg-primary-50 dark:border-gray-700 dark:hover:bg-primary-500/10"
                            >
                                @if ($item['icon'])
                                    <x-filament::icon :icon="$item['icon']" class="h-6 w-6 shrink-0 text-primary-600 dark:text-primary-400" />
                                @endif
                                <span class="font-medium text-gray-950 dark:text-white">{{ $item['label'] }}</span>
                            </button>
                        @empty
                            <p class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('platform.quick_capture.empty') }}</p>
                        @endforelse
                    </div>

                    {{-- Forma izabranog tipa --}}
                    <template x-if="activeKey !== null">
                        <form x-on:submit.prevent="submit()" class="space-y-4">
                            <template x-for="(field, index) in activeItem.fields" :key="field.name">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300" x-text="field.label"></label>

                                    <template x-if="field.type === 'textarea'">
                                        <textarea
                                            data-qc-field
                                            x-model="form[field.name]"
                                            rows="4"
                                            class="block w-full rounded-lg border-none bg-white text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20"
                                        ></textarea>
                                    </template>
                                    {{-- Datum i vrijeme: isti kalendar i isti format kao klasična
                                         forma (d.m.Y H:i, 24h), plus dugme „Sada" — ista radnja
                                         koju tamo nosi suffixAction na DateTimePickeru. --}}
                                    <template x-if="field.type === 'datetime'">
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="text"
                                                data-qc-field
                                                x-bind:data-qc-datetime="field.name"
                                                placeholder="dd.mm.gggg ss:mm"
                                                class="block w-full rounded-lg border-none bg-white text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20"
                                            />
                                            <button
                                                type="button"
                                                x-on:click="setNow(field.name)"
                                                class="flex shrink-0 items-center gap-1 rounded-lg px-2 py-1.5 text-xs font-medium text-primary-600 transition hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-500/10"
                                            >
                                                <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4" />
                                                {{ __('platform.quick_capture.now') }}
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="field.type === 'date'">
                                        <input
                                            type="text"
                                            data-qc-field
                                            x-bind:data-qc-date="field.name"
                                            placeholder="dd.mm.gggg"
                                            class="block w-full rounded-lg border-none bg-white text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20"
                                        />
                                    </template>
                                    <template x-if="field.type === 'text'">
                                        <input
                                            type="text"
                                            data-qc-field
                                            x-model="form[field.name]"
                                            class="block w-full rounded-lg border-none bg-white text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20"
                                        />
                                    </template>
                                    <template x-if="field.type === 'select'">
                                        <select
                                            data-qc-field
                                            x-model="form[field.name]"
                                            class="block w-full rounded-lg border-none bg-white text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20"
                                        >
                                            <option value="">—</option>
                                            <template x-for="option in field.options" :key="option.value">
                                                <option :value="option.value" x-text="option.label"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="field.type === 'number'">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            data-qc-field
                                            x-model="form[field.name]"
                                            class="block w-full rounded-lg border-none bg-white text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20"
                                        />
                                    </template>

                                    <template x-if="errors[field.name]">
                                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400" x-text="errors[field.name][0]"></p>
                                    </template>
                                </div>
                            </template>

                            <template x-if="errors._">
                                <p class="text-xs text-danger-600 dark:text-danger-400" x-text="errors._[0]"></p>
                            </template>

                            <div class="flex items-center justify-between gap-2 pt-1">
                                <button type="button" x-on:click="reset()" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    &larr; {{ __('platform.quick_capture.back') }}
                                </button>
                                <div class="flex items-center gap-2">
                                    <span x-show="saved" class="text-sm font-medium text-success-600 dark:text-success-400">{{ __('platform.quick_capture.saved') }}</span>
                                    <x-filament::button type="submit" size="sm" color="primary" x-bind:disabled="saving">
                                        {{ __('platform.quick_capture.save') }}
                                    </x-filament::button>
                                </div>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
