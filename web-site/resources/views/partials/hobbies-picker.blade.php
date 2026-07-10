@php
    use App\Support\HobbyCatalog;
    $maxHobbies = HobbyCatalog::max();
    $selected = old('hobbies', $selectedHobbies ?? []);
    if (! is_array($selected)) {
        $selected = [];
    }
    $selected = HobbyCatalog::normalize($selected);
@endphp

<div class="hobbies-picker" data-hobbies-picker data-max="{{ $maxHobbies }}">
    <div class="hobbies-picker-head">
        <label class="hobbies-picker-title">Hobiler</label>
        <span class="hobbies-picker-count"><span data-hobby-count>{{ count($selected) }}</span>/{{ $maxHobbies }}</span>
    </div>
    <p class="hobbies-picker-hint">En fazla {{ $maxHobbies }} hobi seçebilirsiniz.</p>

    <div class="hobbies-picker-grid" role="group" aria-label="Hobi seçimi">
        @foreach(HobbyCatalog::all() as $hobby)
            @php $isOn = in_array($hobby['id'], $selected, true); @endphp
            <label
                class="hobby-chip {{ $isOn ? 'hobby-chip--on' : '' }}"
                style="--hobby-color: {{ $hobby['color'] }}"
                data-hobby-chip
            >
                <input
                    type="checkbox"
                    name="hobbies[]"
                    value="{{ $hobby['id'] }}"
                    {{ $isOn ? 'checked' : '' }}
                    data-hobby-input
                >
                <span class="hobby-chip-face">
                    <span class="hobby-chip-icon" aria-hidden="true">{{ $hobby['icon'] }}</span>
                    <span class="hobby-chip-label">{{ $hobby['label'] }}</span>
                </span>
            </label>
        @endforeach
    </div>

    @error('hobbies')
        <small class="form-error">{{ $message }}</small>
    @enderror
    @error('hobbies.*')
        <small class="form-error">{{ $message }}</small>
    @enderror
</div>
