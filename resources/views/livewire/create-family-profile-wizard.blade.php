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
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-end">
                        <span class="text-lg font-bold uppercase tracking-widest text-white/80">
                            Paso
                            {{ $step }}
                            de {{ $this->totalSteps }}</span>
                    </div>
                    <div class="h-4 w-full rounded-full bg-white/15">
                        <div
                            class="bg-highlight h-4 rounded-full transition-all duration-500"
                            style="width: {{ (min($step, $this->totalSteps) / $this->totalSteps) * 100 }}%"
                        ></div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="flex items-center gap-4 rounded-2xl border-2 border-red-400 bg-red-500/20 p-4">
                        <i class='bx bxs-error-circle bx-md shrink-0 text-red-400'></i>
                        <p class="text-xl font-bold text-red-200">Revisa los campos marcados en rojo abajo. Hay algunos
                            datos incompletos o incorrectos.</p>
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'family')
                    <div class="animate-in fade-in slide-in-from-bottom-4 flex flex-col gap-8 duration-500">
                        <x-form-section
                            title="👨‍👩‍👧‍👦 Tu Familia"
                            icon="bxs-home-heart"
                            subtitle="Por favor, responde estas preguntas básicas sobre tu familia."
                        />

                        <x-form-text
                            wire:model="family.name"
                            label="¿Cómo se llama tu familia?"
                            description="Solo los apellidos. Ejemplo: Pérez López"
                            icon="bxs-group"
                            placeholder="Escribe aquí..."
                            error="family.name"
                        />

                        <x-form-toggle
                            label="¿Viven actualmente en el terreno donde se va a construir la casa?"
                            yes-label="Sí, aquí vivimos"
                            no-label="No, rentamos o nos prestan"
                            yes-active="{{ $family->lives_on_land === true }}"
                            no-active="{{ $family->lives_on_land === false }}"
                            wire-click-yes="$set('family.lives_on_land', true)"
                            wire-click-no="$set('family.lives_on_land', false)"
                            error="family.lives_on_land"
                        />

                        <x-form-counter
                            value="{{ $family->member_count }}"
                            label="¿Cuántas personas van a vivir en la casa?"
                            description="Incluyéndote a ti."
                            field="family.member_count"
                            error="family.member_count"
                        />

                        <x-form-toggle
                            label="¿Los papás están casados por el civil?"
                            yes-label="Sí, están casados"
                            no-label="No (unión libre / no casados)"
                            yes-active="{{ $family->parents_married === true }}"
                            no-active="{{ $family->parents_married === false }}"
                            yes-color="highlight"
                            no-color="amber-400"
                            wire-click-yes="$set('family.parents_married', true)"
                            wire-click-no="$set('family.parents_married', false)"
                            error="family.parents_married"
                        />

                        <x-form-toggle
                            label="¿Alguien de la familia tiene adicciones?"
                            yes-label="Sí"
                            no-label="No, ninguna"
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
                                    label="Detalles de las adicciones"
                                    placeholder="Por favor proporciona más detalles de forma confidencial..."
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
                            class="border-highlight/40 flex flex-col gap-4 rounded-2xl border bg-white/10 p-6"
                            x-data="locationPicker('land.lat', 'land.lng')"
                        >
                            <h3 class="text-center text-2xl font-bold text-white md:text-3xl">Ubicación GPS</h3>
                            <button
                                class="bg-highlight hover:bg-highlight/80 flex w-full items-center justify-center gap-3 rounded-full px-8 py-4 text-2xl font-bold text-white shadow-lg transition-transform active:scale-95"
                                type="button"
                                x-on:click="getLocation"
                            >
                                <i
                                    class='bx bxs-location-plus text-4xl'
                                    x-show="!loading"
                                ></i>
                                <i
                                    class='bx bx-loader-alt bx-spin text-4xl'
                                    x-show="loading"
                                    x-cloak
                                ></i>
                                <span x-show="!loading">Usar mi ubicación actual</span>
                                <span
                                    x-show="loading"
                                    x-cloak
                                >Buscando...</span>
                            </button>
                            <div
                                class="h-80 w-full overflow-hidden rounded-xl border-2 border-white/25"
                                x-ref="mapContainer"
                                wire:ignore
                            ></div>
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
                            class="flex flex-col gap-4 rounded-2xl border border-amber-400/40 bg-white/10 p-6"
                            x-data="locationPicker('home.lat', 'home.lng')"
                        >
                            <h3 class="text-center text-2xl font-bold text-white md:text-3xl">Ubicación GPS</h3>
                            <button
                                class="flex w-full items-center justify-center gap-3 rounded-full bg-amber-500 px-8 py-4 text-2xl font-bold text-white shadow-lg hover:bg-amber-600"
                                type="button"
                                x-on:click="getLocation"
                            >
                                <i
                                    class='bx bxs-location-plus text-4xl'
                                    x-show="!loading"
                                ></i>
                                <span x-show="!loading">Usar mi ubicación actual</span>
                            </button>
                            <div
                                class="h-80 w-full overflow-hidden rounded-xl border-2 border-white/25"
                                x-ref="mapContainer"
                                wire:ignore
                            ></div>
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
                        $title = $memberIndex === 0 ? 'Titular de la familia' : 'Familiar ' . ($memberIndex + 1);
                    @endphp
                    <div
                        class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500"
                        wire:key="member-upload-{{ $memberIndex }}"
                    >
                        <x-form-section
                            title="👤 {{ $title }}"
                            icon="bxs-user-badge"
                            subtitle="Primero, sube la foto de su documento de identificación."
                        />

                        <x-form-upload-card
                            title="Identificación Oficial (INE)"
                            badge="optional"
                            description="Solo mayores de 18 años. Si es menor, sube su Acta de Nacimiento."
                            :success="!empty($member['identification']) || !empty($member['birth_certificate'])"
                            success-text="Documento cargado con éxito."
                            remove-label="Quitar y cambiar"
                            remove-action="$set('familyMembers.list.{{ $memberIndex }}.identification', null); $set('familyMembers.list.{{ $memberIndex }}.birth_certificate', null)"
                            :error="[
                                'familyMembers.list.{{ $memberIndex }}.identification',
                                'familyMembers.list.{{ $memberIndex }}.birth_certificate',
                            ]"
                        >
                            <div class="grid grid-cols-1 gap-6">
                                <x-form-upload-label
                                    icon="bxs-id-card"
                                    text="Subir INE (Adulto)"
                                    wire:model="familyMembers.list.{{ $memberIndex }}.identification"
                                    accept="image/*,.pdf"
                                />

                                <div class="text-center text-xl font-bold text-white/50">— O —</div>

                                <x-form-upload-label
                                    icon="bxs-file"
                                    icon-class="text-amber-300 text-7xl"
                                    bg-class="bg-amber-400/10 hover:bg-amber-400/20"
                                    border-class="border-amber-300/50"
                                    text="Subir Acta (Menor)"
                                    wire:model="familyMembers.list.{{ $memberIndex }}.birth_certificate"
                                    accept="image/*,.pdf"
                                />
                            </div>
                            <x-form-upload-loading
                                text="Cargando..."
                                wire:target="familyMembers.list.{{ $memberIndex }}.identification, familyMembers.list.{{ $memberIndex }}.birth_certificate"
                            />
                        </x-form-upload-card>

                        <x-form-upload-card
                            title="Comprobante de Salario"
                            description="Sube una foto de su recibo si esta persona aporta dinero a la casa."
                            :success="!empty($member['income_proof'])"
                            success-text="Recibo cargado."
                            remove-label="Quitar y cambiar"
                            remove-action="$set('familyMembers.list.{{ $memberIndex }}.income_proof', null)"
                            error="familyMembers.list.{{ $memberIndex }}.income_proof"
                        >
                            <x-form-upload-label
                                icon="bxs-wallet"
                                icon-class="text-emerald-300 text-6xl"
                                bg-class="bg-emerald-400/10 hover:bg-emerald-400/20"
                                border-class="border-emerald-300/50"
                                text="Subir comprobante de ingresos"
                                text-class="text-2xl font-bold text-white"
                                padding-class="px-6 py-10"
                                wire:model="familyMembers.list.{{ $memberIndex }}.income_proof"
                                accept="image/*,.pdf"
                            />
                            <x-form-upload-loading
                                wire:target="familyMembers.list.{{ $memberIndex }}.income_proof" />
                        </x-form-upload-card>
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'member_review')
                    @php
                        $memberIndex = $this->currentStep['index'];
                        $member = $familyMembers->list[$memberIndex];
                        $title = $memberIndex === 0 ? 'Titular de la familia' : 'Familiar ' . ($memberIndex + 1);
                    @endphp
                    <div
                        class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500"
                        wire:key="member-review-{{ $memberIndex }}"
                    >
                        <x-form-section
                            title="📝 Datos de {{ $title }}"
                            subtitle="Completa los datos del familiar."
                        />
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.name"
                            label="Nombre(s)"
                            required="true"
                            error="familyMembers.list.{{ $memberIndex }}.name"
                        />
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.paternal_surname"
                            label="Apellido Paterno"
                            required="true"
                            error="familyMembers.list.{{ $memberIndex }}.paternal_surname"
                        />
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.maternal_surname"
                            label="Apellido Materno"
                            required="true"
                            error="familyMembers.list.{{ $memberIndex }}.maternal_surname"
                        />
                        <x-form-select
                            wire:model.live="familyMembers.list.{{ $memberIndex }}.relationship"
                            label="Parentesco"
                            required="true"
                            error="familyMembers.list.{{ $memberIndex }}.relationship"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
                            @foreach (\App\Enums\Relationship::cases() as $rel)
                                <option
                                    class="text-slate-800"
                                    value="{{ $rel->value }}"
                                >{{ $rel->getLabel() }}</option>
                            @endforeach
                        </x-form-select>

                        <x-form-select
                            wire:model="familyMembers.list.{{ $memberIndex }}.marital_status"
                            label="Estado Civil"
                            error="familyMembers.list.{{ $memberIndex }}.marital_status"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
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
                            label="CURP"
                            optional="true"
                            placeholder="18 letras y números"
                            error="familyMembers.list.{{ $memberIndex }}.curp"
                        />
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.phone"
                            label="Teléfono / Celular"
                            icon="bxs-phone"
                            error="familyMembers.list.{{ $memberIndex }}.phone"
                        />
                        <x-form-select
                            wire:model="familyMembers.list.{{ $memberIndex }}.occupation"
                            label="Ocupación"
                            error="familyMembers.list.{{ $memberIndex }}.occupation"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
                            @foreach (\App\Enums\Occupation::cases() as $occ)
                                <option
                                    class="text-slate-800"
                                    value="{{ $occ->value }}"
                                >{{ $occ->getLabel() }}</option>
                            @endforeach
                        </x-form-select>
                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.weekly_income"
                            label="Ingreso semanal (aprox.)"
                            inputmode="decimal"
                            placeholder="$ 0"
                            error="familyMembers.list.{{ $memberIndex }}.weekly_income"
                        />

                        <x-form-select
                            wire:model="familyMembers.list.{{ $memberIndex }}.education_level"
                            label="Nivel de Estudios"
                            error="familyMembers.list.{{ $memberIndex }}.education_level"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
                            @foreach (\App\Enums\EducationLevel::cases() as $level)
                                <option
                                    class="text-slate-800"
                                    value="{{ $level->value }}"
                                >{{ $level->getLabel() }}</option>
                            @endforeach
                        </x-form-select>

                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.education_grade"
                            label="Grado Cursado"
                            placeholder="Ej. 3"
                            inputmode="numeric"
                            error="familyMembers.list.{{ $memberIndex }}.education_grade"
                        />

                        <x-form-select
                            wire:model="familyMembers.list.{{ $memberIndex }}.religion"
                            label="Religión"
                            error="familyMembers.list.{{ $memberIndex }}.religion"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
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
                                label="País de Origen"
                                placeholder="Ej. México"
                                error="familyMembers.list.{{ $memberIndex }}.origin_country"
                            />
                            
                            <x-form-text
                                wire:model="familyMembers.list.{{ $memberIndex }}.origin_state"
                                label="Estado de Origen"
                                placeholder="Ej. Baja California"
                                error="familyMembers.list.{{ $memberIndex }}.origin_state"
                            />
                        </div>

                        <x-form-select
                            wire:model.live="familyMembers.list.{{ $memberIndex }}.speaks_indigenous_language"
                            label="¿Habla alguna lengua indígena?"
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
                                label="¿Cuál lengua?"
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
                                    label="¿Está embarazada?"
                                    error="familyMembers.list.{{ $memberIndex }}.is_pregnant"
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
                                @if (!empty($familyMembers->list[$memberIndex]['is_pregnant']))
                                    <x-form-text
                                        wire:model="familyMembers.list.{{ $memberIndex }}.pregnancy_months"
                                        label="¿Cuántos meses tiene?"
                                        inputmode="numeric"
                                        error="familyMembers.list.{{ $memberIndex }}.pregnancy_months"
                                    />
                                @endif
                            </div>
                        @endif

                        <x-form-text
                            wire:model="familyMembers.list.{{ $memberIndex }}.medical_notes"
                            label="¿Necesita atención médica especial?"
                            placeholder="Ej. diabetes, silla de ruedas..."
                            error="familyMembers.list.{{ $memberIndex }}.medical_notes"
                        />
                        <x-form-select
                            class="border-highlight/50"
                            wire:model="familyMembers.list.{{ $memberIndex }}.is_land_owner"
                            label="¿Es dueño(a) del terreno?"
                            description="Marca sí si esta persona es la dueña del terreno."
                            error="familyMembers.list.{{ $memberIndex }}.is_land_owner"
                        >
                            <option
                                class="font-bold text-black"
                                value=""
                            >Selecciona...</option>
                            <option
                                class="text-slate-800"
                                value="1"
                            >Sí, es dueño(a)</option>
                            <option
                                class="text-slate-800"
                                value="0"
                            >No</option>
                        </x-form-select>
                    </div>
                @endif

                @if ($this->currentStep && $this->currentStep['type'] === 'general_docs')
                    <div class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500">
                        <x-form-section
                            title="📸 Fotos Finales"
                            icon="bxs-folder-open"
                            subtitle="Casi terminamos. Sube estos últimos documentos."
                        />

                        <x-form-upload-card
                            title="1. Foto Familiar"
                            badge="optional"
                            description="Deben aparecer TODAS las personas que vivirán en la casa."
                            :success="(bool) $docs->family_photo"
                            success-text="Foto recibida."
                            remove-label="Quitar y tomar otra"
                            remove-action="$set('docs.family_photo', null)"
                            error="docs.family_photo"
                        >
                            <x-form-upload-label
                                icon="bxs-camera"
                                text="Toca para tomar foto"
                                wire:model="docs.family_photo"
                                accept="image/*"
                            />
                            <x-form-upload-loading wire:target="docs.family_photo" />
                        </x-form-upload-card>

                        <x-form-upload-card
                            title="2. Contrato o Título del Terreno"
                            badge="optional"
                            description="Sube una foto clara del documento."
                            :success="(bool) $docs->land_ownership"
                            success-text="Documento recibido."
                            remove-label="Quitar y subir otro"
                            remove-action="$set('docs.land_ownership', null)"
                            error="docs.land_ownership"
                        >
                            <x-form-upload-label
                                icon="bxs-file"
                                icon-class="text-amber-300 text-7xl"
                                bg-class="bg-amber-400/10 hover:bg-amber-400/20"
                                border-class="border-amber-300/50"
                                text="Toca para subir título"
                                wire:model="docs.land_ownership"
                                accept="image/*,.pdf"
                            />
                            <x-form-upload-loading wire:target="docs.land_ownership" />
                        </x-form-upload-card>

                        @if ($family->parents_married === true)
                            <x-form-upload-card
                                title="Acta de Matrimonio"
                                badge="optional"
                                description="Sube una foto clara del acta de matrimonio civil."
                                :success="(bool) $docs->marriage_certificate"
                                success-text="Acta recibida."
                                remove-label="Quitar y subir otra"
                                remove-action="$set('docs.marriage_certificate', null)"
                                error="docs.marriage_certificate"
                            >
                                <x-form-upload-label
                                    icon="bxs-heart"
                                    icon-class="text-pink-300 text-7xl"
                                    bg-class="bg-pink-400/10 hover:bg-pink-400/20"
                                    border-class="border-pink-300/50"
                                    text="Toca para subir acta"
                                    wire:model="docs.marriage_certificate"
                                    accept="image/*,.pdf"
                                />
                                <x-form-upload-loading wire:target="docs.marriage_certificate" />
                            </x-form-upload-card>
                        @endif

                        <x-form-receipts-upload
                            label="3. Últimos Recibos (Hasta 5)"
                            :receipts="$docs->land_receipts"
                            model="docs.new_land_receipts"
                            :error="['docs.land_receipts', 'docs.land_receipts.*']"
                        />
                    </div>
                @endif

                <div
                    class="{{ $step > 1 ? 'justify-between' : 'justify-end' }} flex flex-col items-center gap-6 border-t border-white/15 pt-10 md:flex-row">
                    @if ($step > 1)
                        <button
                            class="flex w-full items-center justify-center gap-3 rounded-2xl bg-white/10 px-10 py-6 text-2xl font-bold text-white transition-colors hover:bg-white/20 md:w-auto"
                            type="button"
                            wire:click="previousStep"
                        >
                            <i class='bx bxs-chevron-left text-4xl'></i> Regresar
                        </button>
                    @endif

                    @if ($step < $this->totalSteps)
                        <button
                            class="bg-highlight hover:bg-highlight/90 flex w-full items-center justify-center gap-3 rounded-full px-14 py-6 text-2xl font-bold text-white shadow-xl transition-all hover:scale-105 md:w-auto"
                            type="button"
                            wire:click="nextStep"
                        >
                            Siguiente <i class='bx bxs-chevron-right text-4xl'></i>
                        </button>
                    @else
                        <button
                            class="bg-highlight hover:bg-highlight/90 flex w-full items-center justify-center gap-3 rounded-full px-14 py-6 text-3xl font-black text-white shadow-xl transition-all hover:scale-105 md:w-auto"
                            type="button"
                            wire:click="submit"
                            wire:loading.attr="disabled"
                        >
                            <span
                                wire:loading.remove
                                wire:target="submit"
                            >¡Terminar!</span>
                            <span
                                wire:loading
                                wire:target="submit"
                            >Enviando...</span>
                            <i
                                class='bx bxs-badge-check text-4xl'
                                wire:loading.remove
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