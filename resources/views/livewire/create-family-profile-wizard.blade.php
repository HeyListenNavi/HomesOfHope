<div
    class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-stretch justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">

    @if ($notEligible)
        <div class="flex flex-col items-center gap-6 py-16 text-center">
            <div class="flex h-28 w-28 items-center justify-center rounded-full border-2 border-amber-400 bg-white/10">
                <i class='bx bxs-info-circle bx-lg text-amber-400'></i>
            </div>
            <h1 class="text-4xl font-bold text-white md:text-5xl">Enlace no disponible</h1>
            <p class="max-w-xl text-2xl text-white/80">Este formulario es solo para familias que ya fueron
                seleccionadas y pertenecen a un grupo. Si tienes dudas, ponte en contacto con nuestro equipo.</p>
        </div>
    @elseif($alreadyCompleted)
        <div class="flex flex-col items-center gap-6 py-16 text-center">
            <div
                class="bg-highlight/20 border-highlight flex h-28 w-28 items-center justify-center rounded-full border-2">
                <i class='bx bx-check text-highlight bx-lg'></i>
            </div>
            <h1 class="text-4xl font-bold text-white md:text-5xl">Tu solicitud ya fue enviada</h1>
            <p class="max-w-xl text-2xl text-white/80">Ya terminaste: tu solicitud quedó registrada y
                nuestro equipo la está revisando.</p>
            <div
                class="flex max-w-2xl flex-col items-center gap-4 rounded-2xl border-2 border-amber-400/50 bg-amber-500/20 p-6 text-left shadow-lg md:flex-row md:items-start">
                <i class='bx bxs-error-circle shrink-0 border-x-emerald-100 text-amber-400'></i>
                <div class="flex flex-col gap-1">
                    <h4 class="text-center text-xl font-bold uppercase tracking-wide text-amber-400 md:text-left">
                        Nota Importante</h4>
                    <p class="text-center text-xl leading-relaxed text-white/90 md:text-left">Cuando vayas a tu
                        entrevista, avisa al equipo que tu solicitud apareció como un registro duplicado en nuestro
                        sistema.</p>
                </div>
            </div>
        </div>
    @else
        @if ($step <= $this->totalSteps)
            <div class="flex flex-col gap-8">
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center justify-between text-white px-1">
                        <span class="text-xl md:text-2xl font-bold tracking-wide">
                            Paso {{ $step }} de {{ $this->totalSteps }}
                        </span>
                        <span class="text-lg md:text-xl font-bold text-white/70">
                            {{ round((min($step, $this->totalSteps) / $this->totalSteps) * 100) }}%
                        </span>
                    </div>
                    <div class="h-3.5 w-full rounded-full bg-white/15 overflow-hidden">
                        <div
                            class="bg-highlight h-3.5 rounded-full transition-all duration-500 shadow-md"
                            style="width: {{ (min($step, $this->totalSteps) / $this->totalSteps) * 100 }}%"
                        ></div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="flex items-center gap-4 rounded-2xl border-2 border-red-400 bg-red-500/20 p-5 shadow-lg animate-in fade-in duration-300">
                        <i class='bx bxs-error-circle bx-md shrink-0 text-red-400'></i>
                        <p class="text-xl font-bold text-red-200">Revisa los campos marcados en rojo abajo. Hay algunos datos incompletos o que necesitan corrección.</p>
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'family')
                    <div class="animate-in fade-in slide-in-from-bottom-4 flex flex-col gap-8 duration-500">
                        <x-form-section
                            title="👨‍👩‍👧‍👦 Datos de tu Familia"
                            icon="bxs-home-heart"
                        />

                        <x-form-text
                            wire:model="family.name"
                            label="¿Cuáles son los apellidos de tu hijo menor?"
                            description="Escribe solo los dos apellidos (Ejemplo: Pérez López). Si no tienen hijos, escribe los apellidos de la pareja."
                            placeholder="Ej. Pérez López"
                            error="family.name"
                        />

                        <x-form-toggle
                            label="¿Viven actualmente en el terreno donde se va a construir la casa?"
                            yes-label="Sí, ya vivimos aquí en el terreno"
                            no-label="No, rentamos o nos prestan otra casa"
                            yes-active="{{ $family->lives_on_land === true }}"
                            no-active="{{ $family->lives_on_land === false }}"
                            wire-click-yes="$set('family.lives_on_land', true)"
                            wire-click-no="$set('family.lives_on_land', false)"
                            error="family.lives_on_land"
                        />

                        <x-form-counter
                            value="{{ $family->member_count }}"
                            label="¿Cuántas personas van a vivir en la casa en total?"
                            description="Incluyéndote a ti y a todos tus familiares que vivirán juntos."
                            field="family.member_count"
                            error="family.member_count"
                        />

                        <x-form-toggle
                            label="¿Los papás están casados por el civil?"
                            yes-label="Sí, casados por el civil"
                            no-label="No (unión libre o solteros)"
                            yes-active="{{ $family->parents_married === true }}"
                            no-active="{{ $family->parents_married === false }}"
                            yes-color="highlight"
                            no-color="amber-400"
                            wire-click-yes="$set('family.parents_married', true)"
                            wire-click-no="$set('family.parents_married', false)"
                            error="family.parents_married"
                        />

                        <x-form-toggle
                            label="¿Alguien de la familia tiene problemas de adicciones?"
                            yes-label="Sí"
                            no-label="No, nadie"
                            yes-active="{{ $family->has_addictions === true }}"
                            no-active="{{ $family->has_addictions === false }}"
                            yes-color="amber-400"
                            no-color="highlight"
                            wire-click-yes="$set('family.has_addictions', true)"
                            wire-click-no="$set('family.has_addictions', false)"
                            optional
                            error="family.has_addictions"
                        />

                        @if ($family->has_addictions)
                            <div class="animate-in fade-in slide-in-from-top-2 duration-300">
                                <x-form-textarea
                                    wire:model="family.addictions_details"
                                    label="Detalles de las adicciones (Confidencial)"
                                    placeholder="Por favor proporciona más detalles de forma completamente confidencial..."
                                    rows="3"
                                    error="family.addictions_details"
                                />
                            </div>
                        @endif
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'land')
                    <div class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500">
                        <x-form-section
                            title="📍 Ubicación del Terreno"
                            icon="bxs-map-pin"
                            subtitle="Donde se va a construir la casa."
                        />

                        <div
                            class="border-highlight/40 flex flex-col gap-6 rounded-3xl border bg-white/10 p-6 md:p-8 shadow-2xl backdrop-blur-md"
                            x-data="locationPicker('land.lat', 'land.lng', 'land.city', 'land.colony', 'land.address')"
                        >
                            <div class="flex flex-col gap-1 text-center md:text-left">
                                <h3 class="text-2xl font-black text-white md:text-3xl flex items-center justify-center md:justify-start gap-3">
                                    Ubica tu Terreno en el Mapa
                                </h3>
                                <p class="text-lg md:text-xl text-white/80">
                                    Escribe tu colonia o calle en Tijuana, o arrastra el mapa hasta colocar el pin verde sobre tu terreno.
                                </p>
                            </div>

                            <div
                                class="relative z-[2000] w-full"
                                x-on:click.outside="suggestions = []"
                            >
                                <form
                                    class="relative flex items-stretch gap-2"
                                    x-on:submit.prevent="performSearch"
                                >
                                    <div class="relative flex-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-white/60">
                                            <i class='bx bx-search text-2xl'></i>
                                        </div>
                                        <input
                                            type="text"
                                            x-model="searchQuery"
                                            x-on:input.debounce.250ms="fetchSuggestions"
                                            placeholder="Buscar colonia, calle o referencia en Tijuana..."
                                            class="w-full rounded-2xl border-2 border-white/20 bg-black/50 pl-12 pr-4 py-4 text-xl md:text-2xl font-medium text-white placeholder-white/50 shadow-inner focus:border-highlight focus:bg-black/70 focus:outline-none focus:ring-4 focus:ring-highlight/20"
                                            autocomplete="off"
                                        >
                                    </div>
                                    <button
                                        type="submit"
                                        class="bg-highlight hover:opacity-90 text-white rounded-2xl px-6 py-4 text-lg md:text-xl font-bold transition-all active:scale-95 shrink-0 flex items-center gap-2 shadow-lg"
                                    >
                                        <i class='bx bx-search text-2xl' x-show="!searching"></i>
                                        <i class='bx bx-loader-alt bx-spin text-2xl' x-show="searching" x-cloak></i>
                                        <span class="hidden sm:inline">Buscar</span>
                                    </button>
                                </form>

                                <div
                                    x-show="suggestions.length > 0"
                                    x-cloak
                                    class="absolute top-full left-0 right-0 z-[2050] mt-2 max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl divide-y divide-slate-100"
                                >
                                    <template x-for="(item, idx) in suggestions" :key="idx">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-xl p-3 text-left transition hover:bg-slate-100 active:bg-slate-200 cursor-pointer"
                                            x-on:click="selectSuggestion(item)"
                                        >
                                            <i class='bx bxs-map-pin text-2xl text-highlight shrink-0'></i>
                                            <div class="flex flex-col overflow-hidden">
                                                <span class="truncate text-base md:text-lg font-bold text-slate-900" x-text="item.title"></span>
                                                <span class="truncate text-xs md:text-sm font-medium text-slate-500" x-text="item.subtitle" x-show="item.subtitle"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div
                                x-show="searchMessage"
                                x-cloak
                                class="rounded-2xl bg-amber-500/20 border border-amber-400/40 p-3 text-white text-base md:text-lg font-medium flex items-center gap-2"
                            >
                                <i class='bx bx-info-circle text-amber-300 text-2xl shrink-0'></i>
                                <span x-text="searchMessage"></span>
                            </div>

                            <div class="relative h-[440px] md:h-[500px] w-full overflow-hidden rounded-2xl border-2 border-white/30 shadow-2xl">
                                <div
                                    class="h-full w-full"
                                    x-ref="mapContainer"
                                    wire:ignore
                                ></div>

                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center z-[1000]">
                                    <div class="relative flex flex-col items-center">
                                        <div
                                            class="transition-transform duration-150 ease-out"
                                            x-bind:class="isDragging ? '-translate-y-5 scale-110' : 'translate-y-0 scale-100'"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-14 h-14 md:w-16 md:h-16 filter drop-shadow-[0_10px_10px_rgba(0,0,0,0.7)]" style="fill: #61b346; stroke: #FFFFFF; stroke-width: 1.5px;">
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                            </svg>
                                        </div>
                                        <div
                                            class="w-5 h-2.5 rounded-full bg-black/70 blur-[1px] -mt-1 transition-all duration-150"
                                            x-bind:class="isDragging ? 'scale-75 opacity-40' : 'scale-100 opacity-90'"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <button
                                class="w-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center gap-2 rounded-2xl py-3 px-5 text-base md:text-lg font-bold text-white/90 shadow-md transition-all active:scale-[0.99]"
                                type="button"
                                x-on:click="getLocation"
                            >
                                <i class='bx bxs-navigation text-xl' x-show="!loadingGps"></i>
                                <i class='bx bx-loader-alt bx-spin text-xl' x-show="loadingGps" x-cloak></i>
                                <span x-show="!loadingGps">Usar mi ubicación actual (GPS)</span>
                                <span x-show="loadingGps" x-cloak>Obteniendo ubicación GPS...</span>
                            </button>

                            <button
                                class="w-full bg-highlight hover:opacity-90 flex items-center justify-center gap-3 rounded-2xl py-4.5 px-6 text-xl md:text-2xl font-black text-white shadow-2xl transition-all active:scale-[0.98]"
                                x-bind:class="confirmedSuccess ? 'bg-emerald-600 hover:bg-emerald-600' : 'bg-highlight'"
                                type="button"
                                x-on:click="confirmAndFillAddress"
                                x-bind:disabled="savingAddress"
                            >
                                <template x-if="savingAddress">
                                    <span class="flex items-center gap-2">
                                        <i class='bx bx-loader-alt bx-spin text-2xl md:text-3xl'></i>
                                        <span>Obteniendo datos de la dirección...</span>
                                    </span>
                                </template>
                                <template x-if="!savingAddress && confirmedSuccess">
                                    <span class="flex items-center gap-2">
                                        <i class='bx bx-check-circle text-2xl md:text-3xl'></i>
                                        <span>¡Ubicación confirmada!</span>
                                    </span>
                                </template>
                                <template x-if="!savingAddress && !confirmedSuccess">
                                    <span class="flex items-center gap-2">
                                        <i class='bx bxs-map-pin text-2xl md:text-3xl'></i>
                                        <span>Confirmar ubicación y llenar dirección</span>
                                    </span>
                                </template>
                            </button>

                            @error('land.lat')
                                <span class="block text-center text-xl font-bold text-red-300">⚠
                                    {{ $message }}</span>
                            @enderror
                        </div>

                        <x-form-select
                            wire:model="land.city"
                            label="Ciudad"
                            required="true"
                            error="land.city"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
                            <option
                                class="text-slate-800"
                                value="Tijuana"
                            >Tijuana</option>
                            <option
                                class="text-slate-800"
                                value="Rosarito"
                            >Rosarito</option>
                        </x-form-select>
                        <x-form-text
                            wire:model="land.colony"
                            label="Colonia"
                            required="true"
                            placeholder="Ej. El Florido"
                            error="land.colony"
                        />
                        <x-form-textarea
                            wire:model="land.address"
                            label="Dirección exacta / Referencias"
                            required="true"
                            rows="3"
                            placeholder="Calle, número, lote, manzana o indicaciones..."
                            error="land.address"
                        />

                        <div class="flex flex-col gap-8 border-t border-white/15 pt-10">
                            <h3 class="flex items-center gap-3 text-3xl font-bold text-white">Más información <span
                                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-base font-medium text-white/70"
                                >Opcional</span></h3>

                            <x-form-text
                                wire:model="land.ownership_time"
                                label="¿Desde hace cuánto tienen el terreno?"
                                placeholder="Ej. 2 años"
                            />
                            <x-form-select
                                wire:model="land.is_flat"
                                label="¿El terreno es plano?"
                            >
                                <option
                                    class="font-bold text-black"
                                    value=""
                                >Selecciona...</option>
                                <option
                                    class="text-slate-800"
                                    value="1"
                                >Sí, es plano</option>
                                <option
                                    class="text-slate-800"
                                    value="0"
                                >No, tiene desnivel</option>
                            </x-form-select>
                            
                            <x-form-select
                                wire:model="land.currency"
                                label="Moneda para el pago del terreno"
                            >
                                @foreach (\App\Enums\Currency::cases() as $currency)
                                    <option
                                        class="text-slate-800"
                                        value="{{ $currency->value }}"
                                    >{{ $currency->getLabel() }}</option>
                                @endforeach
                            </x-form-select>
                            
                            <x-form-text
                                wire:model="land.total_cost"
                                label="Costo total del terreno"
                                inputmode="decimal"
                                placeholder="$ 0"
                                error="land.total_cost"
                            />
                            <x-form-text
                                wire:model="land.down_payment"
                                label="Enganche que pagaron"
                                inputmode="decimal"
                                placeholder="$ 0"
                                error="land.down_payment"
                            />
                            <x-form-text
                                wire:model="land.monthly_payment"
                                label="Mensualidad"
                                inputmode="decimal"
                                placeholder="$ 0"
                                error="land.monthly_payment"
                            />
                            <x-date-picker
                                wire:model="land.last_payment_date"
                                label="Fecha del último pago"
                                error="land.last_payment_date"
                            />
                            <x-form-select
                                wire:model="land.is_up_to_date"
                                label="¿Estatus de Pago?"
                            >
                                <option
                                    class="font-bold text-black"
                                    value=""
                                >Selecciona...</option>
                                <option
                                    class="text-slate-800"
                                    value="1"
                                >Al corriente</option>
                                <option
                                    class="text-slate-800"
                                    value="0"
                                >Con retraso</option>
                            </x-form-select>

                            <x-form-checkbox-select
                                label="¿Qué servicios ya están instalados?"
                                model="land.services"
                                enum="App\Enums\LandService"
                            />
                        </div>
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'home')
                    <div class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500">
                        <x-form-section
                            title="📍 Ubicación de tu Casa"
                            icon="bxs-buildings"
                            icon-color="amber-400"
                            subtitle="Donde vives actualmente."
                        />

                        <div
                            class="border-amber-400/40 flex flex-col gap-6 rounded-3xl border bg-white/10 p-6 md:p-8 shadow-2xl backdrop-blur-md"
                            x-data="locationPicker('home.lat', 'home.lng', 'home.city', 'home.colony', 'home.address')"
                        >
                            <div class="flex flex-col gap-1 text-center md:text-left">
                                <h3 class="text-2xl font-black text-white md:text-3xl flex items-center justify-center md:justify-start gap-3">
                                    Ubica la Casa Donde Vives Actualmente
                                </h3>
                                <p class="text-lg md:text-xl text-white/80">
                                    Escribe tu colonia o calle en Tijuana, o arrastra el mapa hasta colocar el pin amarillo sobre la vivienda.
                                </p>
                            </div>

                            <div
                                class="relative z-[2000] w-full"
                                x-on:click.outside="suggestions = []"
                            >
                                <form
                                    class="relative flex items-stretch gap-2"
                                    x-on:submit.prevent="performSearch"
                                >
                                    <div class="relative flex-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-white/60">
                                            <i class='bx bx-search text-2xl'></i>
                                        </div>
                                        <input
                                            type="text"
                                            x-model="searchQuery"
                                            x-on:input.debounce.250ms="fetchSuggestions"
                                            placeholder="Buscar colonia, calle o referencia en Tijuana..."
                                            class="w-full rounded-2xl border-2 border-white/20 bg-black/50 pl-12 pr-4 py-4 text-xl md:text-2xl font-medium text-white placeholder-white/50 shadow-inner focus:border-amber-400 focus:bg-black/70 focus:outline-none focus:ring-4 focus:ring-amber-400/20"
                                            autocomplete="off"
                                        >
                                    </div>
                                    <button
                                        type="submit"
                                        class="bg-amber-500 hover:bg-amber-600 text-white rounded-2xl px-6 py-4 text-lg md:text-xl font-bold transition-all active:scale-95 shrink-0 flex items-center gap-2 shadow-lg"
                                    >
                                        <i class='bx bx-search text-2xl' x-show="!searching"></i>
                                        <i class='bx bx-loader-alt bx-spin text-2xl' x-show="searching" x-cloak></i>
                                        <span class="hidden sm:inline">Buscar</span>
                                    </button>
                                </form>

                                <div
                                    x-show="suggestions.length > 0"
                                    x-cloak
                                    class="absolute top-full left-0 right-0 z-[2050] mt-2 max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl divide-y divide-slate-100"
                                >
                                    <template x-for="(item, idx) in suggestions" :key="idx">
                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-xl p-3 text-left transition hover:bg-slate-100 active:bg-slate-200 cursor-pointer"
                                            x-on:click="selectSuggestion(item)"
                                        >
                                            <i class='bx bxs-map-pin text-2xl text-amber-500 shrink-0'></i>
                                            <div class="flex flex-col overflow-hidden">
                                                <span class="truncate text-base md:text-lg font-bold text-slate-900" x-text="item.title"></span>
                                                <span class="truncate text-xs md:text-sm font-medium text-slate-500" x-text="item.subtitle" x-show="item.subtitle"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div
                                x-show="searchMessage"
                                x-cloak
                                class="rounded-2xl bg-amber-500/20 border border-amber-400/40 p-3 text-white text-base md:text-lg font-medium flex items-center gap-2"
                            >
                                <i class='bx bx-info-circle text-amber-300 text-2xl shrink-0'></i>
                                <span x-text="searchMessage"></span>
                            </div>

                            <div class="relative h-[440px] md:h-[500px] w-full overflow-hidden rounded-2xl border-2 border-white/30 shadow-2xl">
                                <div
                                    class="h-full w-full"
                                    x-ref="mapContainer"
                                    wire:ignore
                                ></div>

                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center z-[1000]">
                                    <div class="relative flex flex-col items-center">
                                        <div
                                            class="transition-transform duration-150 ease-out"
                                            x-bind:class="isDragging ? '-translate-y-5 scale-110' : 'translate-y-0 scale-100'"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-14 h-14 md:w-16 md:h-16 filter drop-shadow-[0_10px_10px_rgba(0,0,0,0.7)]" style="fill: #F59E0B; stroke: #FFFFFF; stroke-width: 1.5px;">
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                            </svg>
                                        </div>
                                        <div
                                            class="w-5 h-2.5 rounded-full bg-black/70 blur-[1px] -mt-1 transition-all duration-150"
                                            x-bind:class="isDragging ? 'scale-75 opacity-40' : 'scale-100 opacity-90'"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <button
                                class="w-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center gap-2 rounded-2xl py-3 px-5 text-base md:text-lg font-bold text-white/90 shadow-md transition-all active:scale-[0.99]"
                                type="button"
                                x-on:click="getLocation"
                            >
                                <i class='bx bxs-navigation text-xl' x-show="!loadingGps"></i>
                                <i class='bx bx-loader-alt bx-spin text-xl' x-show="loadingGps" x-cloak></i>
                                <span x-show="!loadingGps">Usar mi ubicación actual (GPS)</span>
                                <span x-show="loadingGps" x-cloak>Obteniendo ubicación GPS...</span>
                            </button>

                            <!-- Botón Principal Prominente para Confirmar y Llenar Dirección -->
                            <button
                                class="w-full bg-amber-500 hover:bg-amber-400 flex items-center justify-center gap-3 rounded-2xl py-4.5 px-6 text-xl md:text-2xl font-black text-white shadow-2xl transition-all active:scale-[0.98]"
                                x-bind:class="confirmedSuccess ? 'bg-emerald-600 hover:bg-emerald-600' : 'bg-amber-500'"
                                type="button"
                                x-on:click="confirmAndFillAddress"
                                x-bind:disabled="savingAddress"
                            >
                                <template x-if="savingAddress">
                                    <span class="flex items-center gap-2">
                                        <i class='bx bx-loader-alt bx-spin text-2xl md:text-3xl'></i>
                                        <span>Obteniendo datos de la dirección...</span>
                                    </span>
                                </template>
                                <template x-if="!savingAddress && confirmedSuccess">
                                    <span class="flex items-center gap-2">
                                        <i class='bx bx-check-circle text-2xl md:text-3xl'></i>
                                        <span>¡Ubicación confirmada!</span>
                                    </span>
                                </template>
                                <template x-if="!savingAddress && !confirmedSuccess">
                                    <span class="flex items-center gap-2">
                                        <i class='bx bxs-buildings text-2xl md:text-3xl'></i>
                                        <span>Confirmar ubicación y llenar dirección</span>
                                    </span>
                                </template>
                            </button>

                            <div class="flex items-center justify-between rounded-2xl bg-black/40 px-5 py-3.5 border border-white/10">
                                <div class="flex items-center gap-2 text-white/95">
                                    <i class='bx bx-check-circle text-amber-400 text-2xl md:text-3xl'></i>
                                    <span class="text-base md:text-lg font-bold">Ubicación seleccionada en el marcador amarillo</span>
                                </div>
                                <span class="text-sm md:text-base text-white/60 hidden sm:inline">Arrastra el mapa si deseas ajustarla</span>
                            </div>

                            @error('home.lat')
                                <span class="block text-center text-xl font-bold text-red-300">⚠
                                    {{ $message }}</span>
                            @enderror
                        </div>

                        <x-form-select
                            wire:model="home.city"
                            label="Ciudad"
                            required="true"
                            error="home.city"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
                            <option
                                class="text-slate-800"
                                value="Tijuana"
                            >Tijuana</option>
                            <option
                                class="text-slate-800"
                                value="Rosarito"
                            >Rosarito</option>
                        </x-form-select>
                        <x-form-text
                            wire:model="home.colony"
                            label="Colonia"
                            required="true"
                            placeholder="Ej. Mariano Matamoros"
                            error="home.colony"
                        />
                        <x-form-textarea
                            wire:model="home.address"
                            label="Dirección exacta / Referencias"
                            required="true"
                            rows="3"
                            error="home.address"
                        />

                        <div class="flex flex-col gap-8 border-t border-white/15 pt-10">
                            <h3 class="flex items-center gap-3 text-3xl font-bold text-white">Más detalles <span
                                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-base font-medium text-white/70"
                                >Opcional</span></h3>
                            <div class="flex flex-col gap-6">
                                <x-form-select
                                    wire:model.live="home.status"
                                    label="¿Dónde viven ahora?"
                                >
                                    <option
                                        class="font-bold text-black"
                                        value=""
                                    >Selecciona...</option>
                                    @foreach (\App\Enums\HousingStatus::cases() as $status)
                                        <option
                                            class="text-slate-800"
                                            value="{{ $status->value }}"
                                        >{{ $status->getLabel() }}</option>
                                    @endforeach
                                </x-form-select>

                                <x-form-text
                                    wire:model="home.ownership_time"
                                    label="Tiempo viviendo aquí"
                                    placeholder="Ej. 2 años"
                                />

                                <x-form-text
                                    wire:model="home.owner_name"
                                    label="Dueño de la casa"
                                    placeholder="Nombre de quien renta/presta"
                                />

                                @if ($home->status === 'rented')
                                    <div class="animate-in fade-in slide-in-from-top-2 flex flex-col gap-6 duration-300">
                                        <x-form-select
                                            wire:model="home.monthly_rent_currency"
                                            label="Moneda"
                                        >
                                            @foreach (\App\Enums\Currency::cases() as $currency)
                                                <option
                                                    class="text-slate-800"
                                                    value="{{ $currency->value }}"
                                                >{{ $currency->getLabel() }}</option>
                                            @endforeach
                                        </x-form-select>
                                        <x-form-text
                                            wire:model="home.monthly_rent"
                                            label="Monto de renta"
                                            inputmode="decimal"
                                            placeholder="$ 0"
                                            error="home.monthly_rent"
                                        />
                                        <x-form-select
                                            wire:model="home.has_receipts"
                                            label="¿Tiene comprobantes de la renta?"
                                        >
                                            <option
                                                class="font-bold text-black"
                                                value=""
                                            >Selecciona...</option>
                                            <option
                                                class="text-slate-800"
                                                value="1"
                                            >Sí tiene</option>
                                            <option
                                                class="text-slate-800"
                                                value="0"
                                            >No tiene</option>
                                        </x-form-select>
                                    </div>
                                @endif

                                <x-form-textarea
                                    wire:model="home.description"
                                    label="Describe tu casa actual"
                                    rows="3"
                                />
                            </div>
                        </div>
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'member_upload')
                    @php
                        $memberIndex = $this->currentStep['index'];
                        $member = $familyMembers->list[$memberIndex];
                        $title = $memberIndex === 0 ? 'Titular de la familia (Tú)' : 'Familiar ' . ($memberIndex + 1);
                    @endphp
                    <div
                        class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500"
                        wire:key="member-upload-{{ $memberIndex }}"
                    >
                        <x-form-section
                            title="👤 Fotos de Documentos: {{ $title }}"
                            icon="bxs-camera"
                            subtitle="Toma una foto clara o sube el documento. El sistema lo escaneará y enderezará automáticamente."
                        />

                        <x-form-upload-card
                            title="1. Identificación Oficial (INE o Acta de Nacimiento)"
                            badge="optional"
                            description="Para mayores de 18 años sube su credencial de elector (INE). Si es menor de edad, sube su Acta de Nacimiento."
                            :success="!empty($member['identification']) || !empty($member['birth_certificate'])"
                            success-text="¡Documento recibido con éxito!"
                            remove-label="Quitar y cambiar documento"
                            remove-action="$set('familyMembers.list.{{ $memberIndex }}.identification', null); $set('familyMembers.list.{{ $memberIndex }}.birth_certificate', null)"
                            :error="[
                                'familyMembers.list.{{ $memberIndex }}.identification',
                                'familyMembers.list.{{ $memberIndex }}.birth_certificate',
                            ]"
                        >
                            <div class="grid grid-cols-1 gap-6">
                                <div x-data="documentScanner('familyMembers.list.{{ $memberIndex }}.identification')" class="flex flex-col gap-2">
                                    <x-form-upload-label
                                        icon="bxs-id-card"
                                        text="Tomar foto de INE (Adulto)"
                                        x-on:change="handleFileInput($event)"
                                        accept="image/*,.pdf"
                                        capture="environment"
                                    />
                                    <template x-if="scanning">
                                        <div class="flex items-center justify-center gap-2 text-xl font-bold text-amber-300 py-2">
                                            <i class='bx bx-loader-alt bx-spin text-2xl'></i>
                                            <span x-text="statusMessage"></span>
                                        </div>
                                    </template>
                                </div>

                                <div class="text-center text-xl font-bold text-white/50">— O SI ES MENOR DE EDAD —</div>

                                <div x-data="documentScanner('familyMembers.list.{{ $memberIndex }}.birth_certificate')" class="flex flex-col gap-2">
                                    <x-form-upload-label
                                        icon="bxs-file"
                                        icon-class="text-amber-300 text-7xl"
                                        bg-class="bg-amber-400/10 hover:bg-amber-400/20"
                                        border-class="border-amber-300/50"
                                        text="Tomar foto de Acta (Menor)"
                                        x-on:change="handleFileInput($event)"
                                        accept="image/*,.pdf"
                                        capture="environment"
                                    />
                                    <template x-if="scanning">
                                        <div class="flex items-center justify-center gap-2 text-xl font-bold text-amber-300 py-2">
                                            <i class='bx bx-loader-alt bx-spin text-2xl'></i>
                                            <span x-text="statusMessage"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <x-form-upload-loading
                                text="Subiendo y procesando..."
                                wire:target="familyMembers.list.{{ $memberIndex }}.identification, familyMembers.list.{{ $memberIndex }}.birth_certificate"
                            />
                        </x-form-upload-card>

                        <x-form-upload-card
                            title="2. Comprobante de Ingresos / Salario"
                            badge="optional"
                            description="Si esta persona trabaja y aporta dinero al hogar, sube una foto de su recibo o comprobante de sueldo."
                            :success="!empty($member['income_proof'])"
                            success-text="¡Comprobante recibido con éxito!"
                            remove-label="Quitar y cambiar comprobante"
                            remove-action="$set('familyMembers.list.{{ $memberIndex }}.income_proof', null)"
                            error="familyMembers.list.{{ $memberIndex }}.income_proof"
                        >
                            <div x-data="documentScanner('familyMembers.list.{{ $memberIndex }}.income_proof')" class="flex flex-col gap-2">
                                <x-form-upload-label
                                    icon="bxs-wallet"
                                    icon-class="text-emerald-300 text-6xl"
                                    bg-class="bg-emerald-400/10 hover:bg-emerald-400/20"
                                    border-class="border-emerald-300/50"
                                    text="Tomar foto de comprobante de ingresos"
                                    text-class="text-2xl font-bold text-white"
                                    padding-class="px-6 py-10"
                                    x-on:change="handleFileInput($event)"
                                    accept="image/*,.pdf"
                                    capture="environment"
                                />
                                <template x-if="scanning">
                                    <div class="flex items-center justify-center gap-2 text-xl font-bold text-amber-300 py-2">
                                        <i class='bx bx-loader-alt bx-spin text-2xl'></i>
                                        <span x-text="statusMessage"></span>
                                    </div>
                                </template>
                            </div>
                            <x-form-upload-loading
                                text="Subiendo comprobante..."
                                wire:target="familyMembers.list.{{ $memberIndex }}.income_proof"
                            />
                        </x-form-upload-card>
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'general_docs')
                    <div class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500">
                        <x-form-section
                            title="📸 Fotos Finales del Terreno y Familia"
                            icon="bxs-folder-open"
                            subtitle="Ya casi terminamos. Sube estas últimas fotos para completar tu expediente."
                        />

                        <x-form-upload-card
                            title="1. Foto Familiar Completa"
                            badge="optional"
                            description="Una foto donde aparezcan juntas TODAS las personas que vivirán en la casa."
                            :success="(bool) $docs->family_photo"
                            success-text="¡Foto familiar recibida con éxito!"
                            remove-label="Quitar y tomar otra foto"
                            remove-action="$set('docs.family_photo', null)"
                            error="docs.family_photo"
                        >
                            <x-form-upload-label
                                icon="bxs-camera"
                                text="Tomar foto familiar o elegir del celular"
                                wire:model="docs.family_photo"
                                accept="image/*"
                                capture="environment"
                            />
                            <x-form-upload-loading wire:target="docs.family_photo" text="Subiendo foto familiar..." />
                        </x-form-upload-card>

                        <x-form-upload-card
                            title="2. Título de Propiedad o Contrato del Terreno"
                            badge="optional"
                            description="Sube una foto clara del título, cesión de derechos o contrato de compraventa del terreno."
                            :success="(bool) $docs->land_ownership"
                            success-text="¡Documento del terreno recibido con éxito!"
                            remove-label="Quitar y subir otro documento"
                            remove-action="$set('docs.land_ownership', null)"
                            error="docs.land_ownership"
                        >
                            <div x-data="documentScanner('docs.land_ownership')" class="flex flex-col gap-2">
                                <x-form-upload-label
                                    icon="bxs-file"
                                    icon-class="text-amber-300 text-7xl"
                                    bg-class="bg-amber-400/10 hover:bg-amber-400/20"
                                    border-class="border-amber-300/50"
                                    text="Tomar foto del título o contrato"
                                    x-on:change="handleFileInput($event)"
                                    accept="image/*,.pdf"
                                    capture="environment"
                                />
                                <template x-if="scanning">
                                    <div class="flex items-center justify-center gap-2 text-xl font-bold text-amber-300 py-2">
                                        <i class='bx bx-loader-alt bx-spin text-2xl'></i>
                                        <span x-text="statusMessage"></span>
                                    </div>
                                </template>
                            </div>
                            <x-form-upload-loading wire:target="docs.land_ownership" text="Subiendo documento..." />
                        </x-form-upload-card>

                        @if ($family->parents_married === true)
                            <x-form-upload-card
                                title="3. Acta de Matrimonio Civil"
                                badge="optional"
                                description="Sube una foto clara del acta de matrimonio civil de los papás."
                                :success="(bool) $docs->marriage_certificate"
                                success-text="¡Acta de matrimonio recibida con éxito!"
                                remove-label="Quitar y subir otra acta"
                                remove-action="$set('docs.marriage_certificate', null)"
                                error="docs.marriage_certificate"
                            >
                                <div x-data="documentScanner('docs.marriage_certificate')" class="flex flex-col gap-2">
                                    <x-form-upload-label
                                        icon="bxs-heart"
                                        icon-class="text-pink-300 text-7xl"
                                        bg-class="bg-pink-400/10 hover:bg-pink-400/20"
                                        border-class="border-pink-300/50"
                                        text="Tomar foto del acta de matrimonio"
                                        x-on:change="handleFileInput($event)"
                                        accept="image/*,.pdf"
                                        capture="environment"
                                    />
                                    <template x-if="scanning">
                                        <div class="flex items-center justify-center gap-2 text-xl font-bold text-amber-300 py-2">
                                            <i class='bx bx-loader-alt bx-spin text-2xl'></i>
                                            <span x-text="statusMessage"></span>
                                        </div>
                                    </template>
                                </div>
                                <x-form-upload-loading wire:target="docs.marriage_certificate" text="Subiendo acta..." />
                            </x-form-upload-card>
                        @endif

                        <x-form-receipts-upload
                            label="4. Últimos Recibos de Pago del Terreno (Hasta 5 recibos)"
                            description="Sube las fotos de tus recibos más recientes para comprobar tus pagos."
                            :receipts="$docs->land_receipts"
                            model="docs.new_land_receipts"
                            :error="['docs.land_receipts', 'docs.land_receipts.*']"
                        />
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'member_review')
                    @php
                        $memberIndex = $this->currentStep['index'];
                        $member = $familyMembers->list[$memberIndex];
                        $title = $memberIndex === 0 ? 'Titular de la familia (Tú)' : 'Familiar ' . ($memberIndex + 1);
                    @endphp
                    <div
                        class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500"
                        wire:key="member-review-{{ $memberIndex }}"
                    >
                        <x-form-section
                            title="📝 Datos Personales: {{ $title }}"
                            icon="bxs-user-detail"
                            subtitle="Verifica y completa la información personal de esta persona."
                        />
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.name"
                            label="Nombre(s)"
                            required="true"
                            placeholder="Ej. María Guadalupe"
                            error="familyMembers.list.{{ $memberIndex }}.name"
                        />
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.paternal_surname"
                            label="Primer Apellido (Paterno)"
                            required="true"
                            placeholder="Ej. Pérez"
                            error="familyMembers.list.{{ $memberIndex }}.paternal_surname"
                        />
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.maternal_surname"
                            label="Segundo Apellido (Materno)"
                            required="true"
                            placeholder="Ej. López"
                            error="familyMembers.list.{{ $memberIndex }}.maternal_surname"
                        />
                        <x-form-select
                            wire:model.live="familyMembers.list.{{ $memberIndex }}.relationship"
                            label="¿Qué es de ti esta persona? (Relación familiar)"
                            required="true"
                            error="familyMembers.list.{{ $memberIndex }}.relationship"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona una opción...</option>
                            @foreach (\App\Enums\Relationship::cases() as $rel)
                                <option
                                    class="text-slate-800"
                                    value="{{ $rel->value }}"
                                >{{ $rel->getLabel() }}</option>
                            @endforeach
                        </x-form-select>

                        <x-form-select
                            wire:model="familyMembers.list.{{ $memberIndex }}.marital_status"
                            label="¿Cuál es su estado civil?"
                            error="familyMembers.list.{{ $memberIndex }}.marital_status"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona una opción...</option>
                            @foreach (\App\Enums\MaritalStatus::cases() as $maritalStatus)
                                <option
                                    class="text-slate-800"
                                    value="{{ $maritalStatus->value }}"
                                >{{ $maritalStatus->getLabel() }}</option>
                            @endforeach
                        </x-form-select>

                        <x-date-picker
                            wire:model="familyMembers.list.{{ $memberIndex }}.birth_date"
                            label="Fecha de Nacimiento"
                            required
                            error="familyMembers.list.{{ $memberIndex }}.birth_date"
                        />
                        <x-form-text
                            class="uppercase"
                            wire:model="familyMembers.list.{{ $memberIndex }}.curp"
                            label="CURP (Opcional)"
                            description="Son 18 letras y números. Si no te lo sabes, puedes dejarlo en blanco."
                            optional="true"
                            placeholder="Ej. ABCD900101HBCXXX01"
                            error="familyMembers.list.{{ $memberIndex }}.curp"
                        />
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.phone"
                            label="Número de Teléfono o Celular"
                            placeholder="Ej. 664 123 4567"
                            icon="bxs-phone"
                            error="familyMembers.list.{{ $memberIndex }}.phone"
                        />
                        <x-form-select
                            wire:model="familyMembers.list.{{ $memberIndex }}.occupation"
                            label="¿En qué trabaja o a qué se dedica?"
                            error="familyMembers.list.{{ $memberIndex }}.occupation"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona una opción...</option>
                            @foreach (\App\Enums\Occupation::cases() as $occ)
                                <option
                                    class="text-slate-800"
                                    value="{{ $occ->value }}"
                                >{{ $occ->getLabel() }}</option>
                            @endforeach
                        </x-form-select>
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.weekly_income"
                            label="¿Cuánto dinero gana por semana en su trabajo aproximadamente?"
                            description="Si no trabaja o es menor de edad, puedes dejarlo en $0."
                            inputmode="decimal"
                            placeholder="$ 0"
                            error="familyMembers.list.{{ $memberIndex }}.weekly_income"
                        />

                        <x-form-select
                            wire:model="familyMembers.list.{{ $memberIndex }}.education_level"
                            label="¿Hasta qué nivel escolar estudió?"
                            error="familyMembers.list.{{ $memberIndex }}.education_level"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona una opción...</option>
                            @foreach (\App\Enums\EducationLevel::cases() as $level)
                                <option
                                    class="text-slate-800"
                                    value="{{ $level->value }}"
                                >{{ $level->getLabel() }}</option>
                            @endforeach
                        </x-form-select>

                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.education_grade"
                            label="¿Qué año o grado escolar cursó?"
                            description="Ejemplo: Si terminó 3ro de primaria o secundaria, escribe 3."
                            placeholder="Ej. 3"
                            inputmode="numeric"
                            error="familyMembers.list.{{ $memberIndex }}.education_grade"
                        />

                        <x-form-select
                            wire:model="familyMembers.list.{{ $memberIndex }}.religion"
                            label="¿Cuál es su religión o creencia?"
                            error="familyMembers.list.{{ $memberIndex }}.religion"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona una opción...</option>
                            @foreach (\App\Enums\Religion::cases() as $religion)
                                <option
                                    class="text-slate-800"
                                    value="{{ $religion->value }}"
                                >{{ $religion->getLabel() }}</option>
                            @endforeach
                        </x-form-select>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form-text
                                wire:model="familyMembers.list.{{ $memberIndex }}.origin_country"
                                label="País de Nacimiento"
                                placeholder="Ej. México"
                                error="familyMembers.list.{{ $memberIndex }}.origin_country"
                            />
                            
                            <x-form-text
                                wire:model="familyMembers.list.{{ $memberIndex }}.origin_state"
                                label="Estado o Entidad de Nacimiento"
                                placeholder="Ej. Baja California, Sinaloa, Puebla..."
                                error="familyMembers.list.{{ $memberIndex }}.origin_state"
                            />
                        </div>

                        <x-form-select
                            wire:model.live="familyMembers.list.{{ $memberIndex }}.speaks_indigenous_language"
                            label="¿Habla alguna lengua indígena o dialecto originario?"
                            error="familyMembers.list.{{ $memberIndex }}.speaks_indigenous_language"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
                            <option
                                class="text-slate-800"
                                value="1"
                            >Sí</option>
                            <option
                                class="text-slate-800"
                                value="0"
                            >No</option>
                        </x-form-select>
                        @if (!empty($member['speaks_indigenous_language']))
                            <x-form-select
                                wire:model="familyMembers.list.{{ $memberIndex }}.indigenous_language"
                                label="¿Cuál lengua o dialecto habla?"
                                error="familyMembers.list.{{ $memberIndex }}.indigenous_language"
                            >
                                <option
                                    class="font-bold text-black"
                                    value=""
                                >Selecciona...</option>
                                @foreach (\App\Enums\IndigenousLanguage::cases() as $language)
                                    <option
                                        class="text-slate-800"
                                        value="{{ $language->value }}"
                                    >{{ $language->getLabel() }}</option>
                                @endforeach
                            </x-form-select>
                        @endif
                        
                        @if (($familyMembers->list[$memberIndex]['relationship'] ?? '') !== \App\Enums\Relationship::Father->value)
                            <div class="animate-in fade-in slide-in-from-top-2 flex flex-col gap-8 duration-300">
                                <x-form-select
                                    wire:model.live="familyMembers.list.{{ $memberIndex }}.is_pregnant"
                                    label="¿Esta persona está embarazada actualmente?"
                                    error="familyMembers.list.{{ $memberIndex }}.is_pregnant"
                                >
                                    <option
                                        class="font-bold text-black"
                                        value=""
                                    >Selecciona...</option>
                                    <option
                                        class="text-slate-800"
                                        value="1"
                                    >Sí, está embarazada</option>
                                    <option
                                        class="text-slate-800"
                                        value="0"
                                    >No</option>
                                </x-form-select>
                                @if (!empty($familyMembers->list[$memberIndex]['is_pregnant']))
                                    <x-form-text
                                        wire:model="familyMembers.list.{{ $memberIndex }}.pregnancy_months"
                                        label="¿Cuántos meses de embarazo tiene?"
                                        placeholder="Ej. 5"
                                        inputmode="numeric"
                                        error="familyMembers.list.{{ $memberIndex }}.pregnancy_months"
                                    />
                                @endif
                            </div>
                        @endif

                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.medical_notes"
                            label="¿Tiene alguna enfermedad, discapacidad o atención médica especial?"
                            description="Ejemplo: Diabetes, presión alta, utiliza silla de ruedas, etc."
                            placeholder="Describe aquí..."
                            error="familyMembers.list.{{ $memberIndex }}.medical_notes"
                        />
                        <x-form-select
                            class="border-highlight/50"
                            wire:model="familyMembers.list.{{ $memberIndex }}.is_land_owner"
                            label="¿Esta persona es dueña o copropietaria del terreno?"
                            description="Marca sí si el nombre de esta persona aparece en el contrato o título del terreno."
                            error="familyMembers.list.{{ $memberIndex }}.is_land_owner"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
                            <option
                                class="text-slate-800"
                                value="1"
                            >Sí, es dueño(a) del terreno</option>
                            <option
                                class="text-slate-800"
                                value="0"
                            >No</option>
                        </x-form-select>
                    </div>
                @endif

                <div
                    class="{{ $step > 1 ? 'justify-between' : 'justify-end' }} flex flex-col items-center gap-6 border-t border-white/15 pt-10 md:flex-row">
                    @if ($step > 1)
                        <button
                            class="flex w-full items-center justify-center gap-3 rounded-2xl bg-white/10 hover:bg-white/20 border-2 border-white/25 px-10 py-5 text-2xl font-bold text-white transition-all active:scale-95 cursor-pointer md:w-auto shadow-lg"
                            type="button"
                            wire:click="previousStep"
                        >
                            <i class='bx bxs-chevron-left text-4xl'></i> Regresar
                        </button>
                    @endif

                    @if ($step < $this->totalSteps)
                        <button
                            class="bg-highlight hover:bg-highlight/90 flex w-full items-center justify-center gap-4 rounded-2xl px-12 py-5 text-2xl md:text-3xl font-black text-white shadow-2xl transition-all active:scale-[0.98] cursor-pointer md:w-auto"
                            type="button"
                            wire:click="nextStep"
                        >
                            <span>Continuar al siguiente paso</span>
                            <i class='bx bxs-chevron-right text-4xl'></i>
                        </button>
                    @else
                        <button
                            class="bg-highlight hover:bg-highlight/90 flex w-full items-center justify-center gap-4 rounded-2xl px-14 py-6 text-2xl md:text-3xl font-black text-white shadow-2xl transition-all active:scale-[0.98] cursor-pointer md:w-auto"
                            type="button"
                            wire:click="submit"
                            wire:loading.attr="disabled"
                        >
                            <span
                                wire:loading.remove
                                wire:target="submit"
                            >✨ ¡Terminar y Enviar Solicitud!</span>
                            <span
                                wire:loading
                                wire:target="submit"
                            >Enviando solicitud...</span>
                            <i
                                class='bx bxs-badge-check text-4xl'
                                wire:loading.remove
                                wire:target="submit"
                            ></i>
                            <i
                                class='bx bx-loader-alt bx-spin text-4xl'
                                wire:loading
                                wire:target="submit"
                            ></i>
                        </button>
                    @endif
                </div>
            </div>
        @else
            <div class="animate-in fade-in zoom-in flex flex-col items-center gap-6 py-16 text-center duration-500">
                <div
                    class="bg-highlight/20 border-highlight flex h-28 w-28 items-center justify-center rounded-full border-2">
                    <i class='bx bxs-check-circle text-highlight bx-lg'></i>
                </div>
                <h1 class="text-4xl font-bold text-white md:text-5xl">¡Solicitud Enviada!</h1>
                <p class="text-2xl text-white/80">Tu solicitud ha sido recibida correctamente. Nuestro equipo la
                    revisará pronto.</p>
            </div>
        @endif

    @endif
</div>