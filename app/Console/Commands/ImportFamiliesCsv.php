<?php

namespace App\Console\Commands;

use App\Enums\Currency;
use App\Enums\Relationship;
use App\Models\FamilyMember;
use App\Models\FamilyProfile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportFamiliesCsv extends Command
{
    protected $signature = 'app:import-families';

    protected $description = 'Import families from legacy CSV files';

    public function handle()
    {
        $file1 = base_path('BD Familias - Registro general.csv');
        $file2 = base_path('BD Familias - Respuestas de formulario 1.csv');

        if (! file_exists($file1) || ! file_exists($file2)) {
            $this->error('CSV files not found.');

            return 1;
        }

        $this->info('Clearing existing records...');
        FamilyMember::query()->delete();
        FamilyProfile::query()->delete();
        $this->info('All existing families and members deleted.');

        $this->info('Processing File 1: Registro general...');
        $this->processFile1($file1);

        $this->info('Processing File 2: Respuestas de formulario 1...');
        $this->processFile2($file2);

        $totalProfiles = FamilyProfile::count();
        $totalMembers = FamilyMember::count();
        $this->info("Import completed! Total Profiles: $totalProfiles, Total Members: $totalMembers");

        return 0;
    }

    private function getPaddedRow($headers, $row)
    {
        if (count($headers) > count($row)) {
            return array_pad($row, count($headers), '');
        } elseif (count($row) > count($headers)) {
            return array_slice($row, 0, count($headers));
        }

        return $row;
    }

    private function processFile1(string $path)
    {
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        $count = 0;

        // "Colonia" appears twice. We must make keys unique so we don't lose data.
        $headerCounts = [];
        foreach ($headers as &$h) {
            $h = trim($h);
            if (isset($headerCounts[$h])) {
                $headerCounts[$h]++;
                $h = $h.'_'.$headerCounts[$h];
            } else {
                $headerCounts[$h] = 1;
            }
        }

        while (($row = fgetcsv($handle)) !== false) {
            $row = $this->getPaddedRow($headers, $row);
            $data = array_combine($headers, $row);
            $familyName = trim($data['FAMILIA'] ?? '');

            if (empty($familyName)) {
                continue;
            }

            $profile = FamilyProfile::firstOrNew(['family_name' => $familyName]);
            $profile->lives_on_land = true;

            $interviewer = $data['Nombre de Entrevistador/Nombre de Entrevistador'] ?? null;
            if ($interviewer) {
                $profile->interviewer_name = $this->formatInterviewerNames($interviewer);
            }
            $profile->land_city = $data['Ciudad'] ?? null;
            $profile->land_colony = $data['Colonia_2'] ?? ($data['Colonia'] ?? null);

            $plusCode = trim($data['Dirección  (GPS)'] ?? '');
            $profile->land_address = $plusCode;
            if (! empty($plusCode)) {
                $profile->land_address_link = 'https://www.google.com/maps/search/?api=1&query='.urlencode($plusCode);
            }

            $rawStatus = strtolower(trim($data['Estatus'] ?? ''));
            $statusMapping = [
                'calificada' => 'built',
                'descalificada' => 'not_eligible',
                'en proceso' => 'in_process',
            ];
            $profile->status = $statusMapping[$rawStatus] ?? 'in_process';

            if (! empty($data['Fecha de la entrevista'])) {
                $profile->opened_at = $this->parseDate($data['Fecha de la entrevista']) ?? '1900-01-01';
            } else {
                $profile->opened_at = '1900-01-01';
            }

            $profile->land_total_cost = $this->parseAmount($data['Costo del terreno (total)'] ?? '');
            $profile->land_currency = $this->parseCurrency($data['Costo del terreno (total)'] ?? '');
            $profile->land_monthly_payment = $this->parseAmount($data['Cantidad que paga  mensualmente por el terreno'] ?? '');

            $profile->general_observations = $data['Observaciones'] ?? '';

            $motivo = trim($data['Motivo'] ?? '');
            $comentarios = trim($data['Comentarios'] ?? '');
            $reasonParts = array_filter([$motivo, $comentarios]);
            $profile->reason = implode("\n", $reasonParts);

            // Building info — dates are in two consecutive columns: "Fechas de construcción" + one unnamed col
            $profile->building_start_date = $this->parseDateDash($data['Fechas de construcción'] ?? null);
            $profile->building_finish_date = $this->parseDateDash($data['Fechas de construcción 2'] ?? null);
            $profile->building_team = $data['Equipo'] ?? null;
            $profile->building_team_color = $data['Color del equipo'] ?? null;

            $profile->save();

            // Extract parents
            $this->extractParent($profile, $data['Nombre de la madre  Teléfono de la madre'] ?? '', Relationship::Mother, true);
            $this->extractParent($profile, $data['Nombre del padre  Teléfono del padre'] ?? '', Relationship::Father, true);

            // Extract children
            $childrenStr = $data['1. Nombre del hijo/a o menor a su cargo'] ?? '';
            if (! empty($childrenStr)) {
                $children = explode(',', $childrenStr);
                foreach ($children as $child) {
                    $child = trim($child);
                    if (! empty($child)) {
                        // Remove age if present in string (e.g. "Name 10")
                        $name = preg_replace('/\s+\d+$/', '', $child);

                        $this->createMember($profile, [
                            'name' => $name,
                            'relationship' => Relationship::Child,
                            'birth_date' => '1900-01-01',
                        ]);
                    }
                }
            }

            $count++;
        }
        $this->info("Finished File 1. Processed $count records.");
        fclose($handle);
    }

    private function extractParent(FamilyProfile $profile, string $str, Relationship $rel, bool $isResponsible)
    {
        $str = trim($str);
        if (empty($str)) {
            return;
        }

        $phone = null;
        if (preg_match('/(\d{10})$/', $str, $matches)) {
            $phone = $matches[1];
            $str = trim(str_replace($phone, '', $str));
        }
        if (! empty($str)) {
            $this->createMember($profile, [
                'name' => $str,
                'relationship' => $rel,
                'phone' => $phone,
                'is_responsible' => $isResponsible,
                'birth_date' => '1900-01-01',
            ]);
        }
    }

    private function processFile2(string $path)
    {
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        $count = 0;

        // Duplicate headers logic
        $headerCounts = [];
        foreach ($headers as &$h) {
            $h = trim($h);
            if (isset($headerCounts[$h])) {
                $headerCounts[$h]++;
                $h = $h.'_'.$headerCounts[$h];
            } else {
                $headerCounts[$h] = 1;
            }
        }

        while (($row = fgetcsv($handle)) !== false) {
            $row = $this->getPaddedRow($headers, $row);
            $data = array_combine($headers, $row);
            $familyName = trim($data['Apellidos de su Hijo menor'] ?? '');

            if (empty($familyName)) {
                continue;
            }

            $profile = FamilyProfile::firstOrCreate(['family_name' => $familyName], [
                'opened_at' => '1900-01-01',
                'lives_on_land' => true,
                'status' => 'in_process',
            ]);

            $interviewer1 = trim($data['Nombre de Entrevistador'] ?? '', ' /');
            $interviewer2 = trim($data['Nombre de Entrevistador_2'] ?? '', ' /');
            $combinedInterviewer = $this->formatInterviewerNames($interviewer1.($interviewer2 ? '/'.$interviewer2 : ''));
            if (! empty($combinedInterviewer)) {
                $profile->interviewer_name = $combinedInterviewer;
            }

            if (! empty($data['Costo del terreno (total)'])) {
                $profile->land_total_cost = $this->parseAmount($data['Costo del terreno (total)']);
                $profile->land_currency = $this->parseCurrency($data['Costo del terreno (total)']);
            }

            if (! empty($data['Cantidad que paga  mensualmente por el terreno'])) {
                $profile->land_monthly_payment = $this->parseAmount($data['Cantidad que paga  mensualmente por el terreno']);
            }
            $profile->save();

            $this->importMembers($profile, $data);
            $count++;
        }
        $this->info("Finished File 2. Processed $count records.");
        fclose($handle);
    }

    private function formatInterviewerNames(string $str): string
    {
        $names = array_filter(explode('/', $str), fn ($name) => ! empty(trim($name)));
        $names = array_map('trim', $names);

        return implode(' y ', $names);
    }

    private function importMembers(FamilyProfile $profile, array $data)
    {
        if (! empty($data['Nombre de la madre'])) {
            $this->createMember($profile, [
                'name' => $data['Nombre de la madre'],
                'relationship' => Relationship::Mother,
                'phone' => $this->normalizePhone($data['Teléfono de la madre'] ?? ''),
                'curp' => $data['CURP (Madre)'] ?? null,
                'is_responsible' => true,
            ]);
        }

        if (! empty($data['Nombre del padre'])) {
            $this->createMember($profile, [
                'name' => $data['Nombre del padre'],
                'relationship' => Relationship::Father,
                'phone' => $this->normalizePhone($data['Teléfono del padre'] ?? ''),
                'curp' => $data['CURP  (Padre)'] ?? null,
                'is_responsible' => true,
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            $nameKey = match ($i) {
                1, 2, 3, 9 => "$i. Nombre del hijo/a o menor a su cargo",
                default => "$i.Nombre del hijo/a o menor a su cargo",
            };

            if (! empty($data[$nameKey])) {
                // Correct birthdate key based on unique header renaming logic
                $bdKey = ($i === 1) ? 'Fecha de nacimiento' : "Fecha de nacimiento_$i";

                $this->createMember($profile, [
                    'name' => $data[$nameKey],
                    'relationship' => Relationship::Child,
                    'birth_date' => $this->parseDateStrict($data[$bdKey] ?? null),
                ]);
            }
        }

        for ($i = 1; $i <= 4; $i++) {
            $nameKey = "$i.Nombre";
            if (! empty($data[$nameKey])) {
                $relKey = "$i.Parentesco";
                $ageKey = ($i === 1) ? '1. Edad' : "$i.Edad";

                $attributes = [
                    'name' => $data[$nameKey],
                    'relationship' => $this->mapRelationship($data[$relKey] ?? 'otro'),
                ];

                if (! empty($data[$ageKey])) {
                    $attributes['birth_date'] = $this->estimateBirthDate(
                        $data[$ageKey],
                        $this->parseDate($data['Fecha de la entrevista'] ?? null)
                    );
                }

                try {
                    $this->createMember($profile, $attributes);
                } catch (\Exception $e) {
                    $this->error("Failed to create member {$attributes['name']} for profile {$profile->family_name}: ".$e->getMessage());
                }
            }
        }
    }

    private function estimateBirthDate(string $ageStr, ?string $interviewDate): string
    {
        $ageStr = strtolower(trim($ageStr));
        $date = $interviewDate ? Carbon::parse($interviewDate) : now();

        // If the date is clearly invalid (e.g. year 22), fallback to a safe date
        if ($date->year < 1900) {
            $date = now();
        }

        if (str_ends_with($ageStr, 'm')) {
            $months = (int) $ageStr;

            return $date->subMonths($months)->format('Y-m-d');
        }

        $years = (int) $ageStr;
        if ($years > 0 && $years < 120) {
            return $date->subYears($years)->startOfYear()->format('Y-m-d');
        }

        return '1900-01-01';
    }

    private function createMember(FamilyProfile $profile, array $attributes)
    {
        $fullName = trim($attributes['name']);
        if (empty($fullName)) {
            return;
        }

        $parts = explode(' ', $fullName);
        $firstName = $parts[0];
        $paternal = $parts[1] ?? '';
        $maternal = $parts[2] ?? '';

        $curp = ! empty($attributes['curp']) ? trim($attributes['curp']) : null;
        $birthDate = $attributes['birth_date'] ?? '1900-01-01';

        $exists = $profile->members()
            ->where('name', $firstName)
            ->where('relationship', $attributes['relationship'])
            ->first();

        if ($exists) {
            if ($curp && empty($exists->curp)) {
                if (! FamilyMember::where('curp', $curp)->where('id', '!=', $exists->id)->exists()) {
                    $exists->curp = $curp;
                }
            }
            if (! empty($attributes['phone']) && empty($exists->phone)) {
                $exists->phone = $attributes['phone'];
            }
            // Update birth date if it was 1900 or a guessed date (ending in -01-01) but now we have a date from a more specific source
            $currentBD = $exists->birth_date ? $exists->birth_date->format('Y-m-d') : null;
            $isGuessed = $currentBD && (str_ends_with($currentBD, '-01-01') || $currentBD === '1900-01-01');

            if ($isGuessed && $birthDate !== '1900-01-01') {
                $exists->birth_date = $birthDate;
            }

            if ($exists->isDirty()) {
                $exists->save();
            }

            return;
        }

        if ($curp) {
            $globalExists = FamilyMember::where('curp', $curp)->first();
            if ($globalExists) {
                return;
            }
        }

        $profile->members()->create([
            'name' => $firstName,
            'paternal_surname' => $paternal ?: '-',
            'maternal_surname' => $maternal ?: null,
            'relationship' => $attributes['relationship'],
            'phone' => $attributes['phone'] ?? null,
            'curp' => $curp,
            'is_responsible' => $attributes['is_responsible'] ?? false,
            'birth_date' => $birthDate,
        ]);
    }

    private function parseAmount($string): ?float
    {
        if (empty($string)) {
            return null;
        }
        if (preg_match('/([0-9,.]+)/', $string, $matches)) {
            return (float) str_replace(',', '', $matches[1]);
        }

        return null;
    }

    private function parseCurrency($string): Currency
    {
        $lowered = strtolower($string);
        if (strpos($lowered, '(mxn)') !== false && strpos($lowered, '(usd)') !== false) {
            return strpos($lowered, '(mxn)') < strpos($lowered, '(usd)') ? Currency::MXN : Currency::USD;
        }
        if (Str::contains($lowered, 'usd')) {
            return Currency::USD;
        }

        return Currency::MXN;
    }

    private function parseDate($string): ?string
    {
        if (empty($string)) {
            return null;
        }
        $string = trim($string);

        try {
            $date = Carbon::createFromFormat('d/m/Y', $string);

            // Fix years like 0022 -> 2022
            if ($date->year < 100) {
                $date->year = 2000 + $date->year;
            }

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDateDash($string): ?string
    {
        if (empty($string)) {
            return null;
        }
        $string = trim($string);

        try {
            $date = Carbon::createFromFormat('d-M-Y', $string);

            if ($date->year < 100) {
                $date->year = 2000 + $date->year;
            }

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDateStrict($string): string
    {
        $parsed = $this->parseDate($string);

        return $parsed ?? '1900-01-01';
    }

    private function normalizePhone(string $phone): ?string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) === 10) {
            return '52'.$cleaned;
        }

        return $cleaned ?: null;
    }

    private function mapRelationship(string $label): Relationship
    {
        $label = strtolower($label);

        return match (true) {
            Str::contains($label, 'madre o padre') => Relationship::Tutor,
            Str::contains($label, 'tutor') => Relationship::Tutor,
            Str::contains($label, 'padre') => Relationship::Father,
            Str::contains($label, 'madre') => Relationship::Mother,
            Str::contains($label, 'hijo') => Relationship::Child,
            Str::contains($label, 'abuelo') => Relationship::Grandparent,
            Str::contains($label, 'nieto') => Relationship::Grandchild,
            default => Relationship::Other,
        };
    }
}
