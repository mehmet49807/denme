@php
    $birthDate = $birthDate ?? null;
    $oldDay = old('birth_day');
    $oldMonth = old('birth_month');
    $oldYear = old('birth_year');
    if ($birthDate) {
        try {
            $parsed = \Illuminate\Support\Carbon::parse($birthDate);
            $oldDay = $oldDay ?: $parsed->day;
            $oldMonth = $oldMonth ?: $parsed->month;
            $oldYear = $oldYear ?: $parsed->year;
        } catch (\Throwable) {
        }
    }
    $currentYear = (int) now()->year;
    $minYear = $currentYear - 100;
    $maxYear = $currentYear - 18;
@endphp

<div class="birth-date-fields" role="group" aria-label="Doğum tarihi">
    <div class="birth-date-fields-row">
        <label class="birth-date-field">
            <span class="birth-date-field-label">Gün</span>
            <select name="birth_day" aria-label="Gün">
                <option value="">Gün</option>
                @for($d = 1; $d <= 31; $d++)
                    <option value="{{ $d }}" @selected((string) $oldDay === (string) $d)>{{ $d }}</option>
                @endfor
            </select>
        </label>
        <label class="birth-date-field">
            <span class="birth-date-field-label">Ay</span>
            <select name="birth_month" aria-label="Ay">
                <option value="">Ay</option>
                @foreach([
                    1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
                    5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
                    9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
                ] as $m => $label)
                    <option value="{{ $m }}" @selected((string) $oldMonth === (string) $m)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="birth-date-field">
            <span class="birth-date-field-label">Yıl</span>
            <select name="birth_year" aria-label="Yıl">
                <option value="">Yıl</option>
                @for($y = $maxYear; $y >= $minYear; $y--)
                    <option value="{{ $y }}" @selected((string) $oldYear === (string) $y)>{{ $y }}</option>
                @endfor
            </select>
        </label>
    </div>
    @error('birth_date') <small class="form-error">{{ $message }}</small> @enderror
    @error('birth_day') <small class="form-error">{{ $message }}</small> @enderror
    @error('birth_month') <small class="form-error">{{ $message }}</small> @enderror
    @error('birth_year') <small class="form-error">{{ $message }}</small> @enderror
</div>
