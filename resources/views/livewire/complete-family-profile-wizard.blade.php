<div
    class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-stretch justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">

    @if ($alreadyCompleted)
        <div class="flex flex-col items-center gap-6 py-16 text-center">
            <div
                class="bg-highlight/20 border-highlight flex h-28 w-28 items-center justify-center rounded-full border-2">
                <i class='bx bx-check text-highlight bx-lg'></i>
            </div>
            <h1 class="text-4xl font-bold text-white md:text-5xl">¡Perfil completado!</h1>
            <p class="max-w-xl text-2xl text-white/80">Ya revisaste y confirmaste los datos de tu familia.
                Nuestro equipo los está revisando.</p>
        </div>
    @else
        @if ($step <= $this->getTotalSteps())
            <div class="flex flex-col gap-8">
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center justify-between text-white px-1">
                        <span class="text-xl md:text-2xl font-bold tracking-wide">
                            Familiar {{ $step }} de {{ $this->getTotalSteps() }}
                        </span>
                        <span class="text-lg md:text-xl font-bold text-white/70">
                            {{ round((min($step, $this->getTotalSteps()) / $this->getTotalSteps()) * 100) }}%
                        </span>
                    </div>
                    <div class="h-3.5 w-full rounded-full bg-white/15 overflow-hidden">
                        <div
                            class="bg-highlight h-3.5 rounded-full transition-all duration-500 shadow-md"
                            style="width: {{ (min($step, $this->getTotalSteps()) / $this->getTotalSteps()) * 100 }}%"
                        ></div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="flex items-center gap-4 rounded-2xl border-2 border-red-400 bg-red-500/20 p-5 shadow-lg animate-in fade-in duration-300">
                        <i class='bx bxs-error-circle bx-md shrink-0 text-red-400'></i>
                        <p class="text-xl font-bold text-red-200">Revisa los campos marcados en rojo. Hay datos incompletos o con errores.</p>
                    </div>
                @endif

                @php
                    $currentMember = $this->getCurrentMember();
                    $memberIndex = $step - 1;
                @endphp

                @if ($currentMember)
                    <div
                        class="animate-in fade-in slide-in-from-right-8 flex flex-col gap-8 duration-500"
                        wire:key="member-review-{{ $memberIndex }}"
                    >
                        <x-form-section
                            title="Revisa los datos de: {{ $currentMember['name'] ?: 'Familiar ' . ($memberIndex + 1) }}"
                            icon="bxs-user-detail"
                            subtitle="Los datos fueron extraídos de los documentos. Verifica que sean correctos y completa los que faltan."
                        />

                        <x-form-text
                            wire:model="members.{{ $memberIndex }}.name"
                            label="Nombre(s)"
                            required="true"
                            placeholder="Ej. María Guadalupe"
                            error="members.{{ $memberIndex }}.name"
                        />
                        <x-form-text
                            wire:model="members.{{ $memberIndex }}.paternal_surname"
                            label="Primer Apellido (Paterno)"
                            required="true"
                            placeholder="Ej. Pérez"
                            error="members.{{ $memberIndex }}.paternal_surname"
                        />
                        <x-form-text
                            wire:model="members.{{ $memberIndex }}.maternal_surname"
                            label="Segundo Apellido (Materno)"
                            required="true"
                            placeholder="Ej. López"
                            error="members.{{ $memberIndex }}.maternal_surname"
                        />
                        <x-form-select
                            wire:model.live="members.{{ $memberIndex }}.relationship"
                            label="¿Qué es de ti esta persona? (Relación familiar)"
                            required="true"
                            error="members.{{ $memberIndex }}.relationship"
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
                            wire:model="members.{{ $memberIndex }}.marital_status"
                            label="¿Cuál es su estado civil?"
                            error="members.{{ $memberIndex }}.marital_status"
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
                            wire:model="members.{{ $memberIndex }}.birth_date"
                            label="Fecha de Nacimiento"
                            required
                            error="members.{{ $memberIndex }}.birth_date"
                        />
                        <x-form-text
                            class="uppercase"
                            wire:model="members.{{ $memberIndex }}.curp"
                            label="CURP (Opcional)"
                            description="Son 18 letras y números. Si no te lo sabes, puedes dejarlo en blanco."
                            optional="true"
                            placeholder="Ej. ABCD900101HBCXXX01"
                            error="members.{{ $memberIndex }}.curp"
                        />
                        <x-form-text
                            wire:model="members.{{ $memberIndex }}.phone"
                            label="Número de Teléfono o Celular"
                            placeholder="Ej. 664 123 4567"
                            icon="bxs-phone"
                            error="members.{{ $memberIndex }}.phone"
                        />
                        <x-form-select
                            wire:model="members.{{ $memberIndex }}.occupation"
                            label="¿En qué trabaja o a qué se dedica?"
                            error="members.{{ $memberIndex }}.occupation"
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
                            wire:model="members.{{ $memberIndex }}.weekly_income"
                            label="¿Cuánto dinero gana por semana en su trabajo aproximadamente?"
                            description="Si no trabaja o es menor de edad, puedes dejarlo en $0."
                            inputmode="decimal"
                            placeholder="$ 0"
                            error="members.{{ $memberIndex }}.weekly_income"
                        />

                        <x-form-select
                            wire:model="members.{{ $memberIndex }}.education_level"
                            label="¿Hasta qué nivel escolar estudió?"
                            error="members.{{ $memberIndex }}.education_level"
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
                            wire:model="members.{{ $memberIndex }}.education_grade"
                            label="¿Qué año o grado escolar cursó?"
                            description="Ejemplo: Si terminó 3ro de primaria o secundaria, escribe 3."
                            placeholder="Ej. 3"
                            inputmode="numeric"
                            error="members.{{ $memberIndex }}.education_grade"
                        />

                        <x-form-select
                            wire:model="members.{{ $memberIndex }}.religion"
                            label="¿Cuál es su religión o creencia?"
                            error="members.{{ $memberIndex }}.religion"
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
                                wire:model="members.{{ $memberIndex }}.origin_country"
                                label="País de Nacimiento"
                                placeholder="Ej. México"
                                error="members.{{ $memberIndex }}.origin_country"
                            />

                            <x-form-text
                                wire:model="members.{{ $memberIndex }}.origin_state"
                                label="Estado o Entidad de Nacimiento"
                                placeholder="Ej. Baja California, Sinaloa, Puebla..."
                                error="members.{{ $memberIndex }}.origin_state"
                            />
                        </div>

                        <x-form-select
                            wire:model.live="members.{{ $memberIndex }}.speaks_indigenous_language"
                            label="¿Habla alguna lengua indígena o dialecto originario?"
                            error="members.{{ $memberIndex }}.speaks_indigenous_language"
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
                        @if (!empty($currentMember['speaks_indigenous_language']))
                            <x-form-select
                                wire:model="members.{{ $memberIndex }}.indigenous_language"
                                label="¿Cuál lengua o dialecto habla?"
                                error="members.{{ $memberIndex }}.indigenous_language"
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

                        @if (($currentMember['relationship'] ?? '') !== \App\Enums\Relationship::Father->value)
                            <div class="animate-in fade-in slide-in-from-top-2 flex flex-col gap-8 duration-300">
                                <x-form-select
                                    wire:model.live="members.{{ $memberIndex }}.is_pregnant"
                                    label="¿Esta persona está embarazada actualmente?"
                                    error="members.{{ $memberIndex }}.is_pregnant"
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
                                @if (!empty($currentMember['is_pregnant']))
                                    <x-form-text
                                        wire:model="members.{{ $memberIndex }}.pregnancy_months"
                                        label="¿Cuántos meses de embarazo tiene?"
                                        placeholder="Ej. 5"
                                        inputmode="numeric"
                                        error="members.{{ $memberIndex }}.pregnancy_months"
                                    />
                                @endif
                            </div>
                        @endif

                        <x-form-text
                            wire:model="members.{{ $memberIndex }}.medical_notes"
                            label="¿Tiene alguna enfermedad, discapacidad o atención médica especial?"
                            description="Ejemplo: Diabetes, presión alta, utiliza silla de ruedas, etc."
                            placeholder="Describe aquí..."
                            error="members.{{ $memberIndex }}.medical_notes"
                        />
                        <x-form-select
                            class="border-highlight/50"
                            wire:model="members.{{ $memberIndex }}.is_land_owner"
                            label="¿Esta persona es dueña o copropietaria del terreno?"
                            description="Marca sí si el nombre de esta persona aparece en el contrato o título del terreno."
                            error="members.{{ $memberIndex }}.is_land_owner"
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

                    @if ($step < $this->getTotalSteps())
                        <button
                            class="bg-highlight hover:bg-highlight/90 flex w-full items-center justify-center gap-4 rounded-2xl px-12 py-5 text-2xl md:text-3xl font-black text-white shadow-2xl transition-all active:scale-[0.98] cursor-pointer md:w-auto"
                            type="button"
                            wire:click="nextStep"
                        >
                            <span>Siguiente familiar</span>
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
                            >¡Confirmar y Enviar!</span>
                            <span
                                wire:loading
                                wire:target="submit"
                            >Enviando...</span>
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
                <h1 class="text-4xl font-bold text-white md:text-5xl">¡Perfil Completado!</h1>
                <p class="text-2xl text-white/80">Tus datos fueron enviados correctamente. Nuestro equipo los
                    revisará pronto.</p>
            </div>
        @endif
    @endif
</div>
