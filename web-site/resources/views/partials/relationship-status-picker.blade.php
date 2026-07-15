@php
    use App\Support\RelationshipStatus;
    $selected = old('relationship_status', $selected ?? null);
    $name = $name ?? 'relationship_status';
    $options = RelationshipStatus::all();
@endphp

<div class="relationship-status-picker" role="radiogroup" aria-label="İlişki durumu">
    <label class="relationship-status-option relationship-status-option--neutral {{ $selected === null || $selected === '' ? 'relationship-status-option--active' : '' }}">
        <input type="radio" name="{{ $name }}" value="" {{ $selected === null || $selected === '' ? 'checked' : '' }}>
        <span class="relationship-status-option-icon" aria-hidden="true">🫥</span>
        <span class="relationship-status-option-label">Belirtmek istemiyorum</span>
    </label>
    @foreach($options as $key => $meta)
        <label class="relationship-status-option relationship-status-option--{{ $meta['color'] }} {{ $selected === $key ? 'relationship-status-option--active' : '' }}">
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $key }}"
                {{ $selected === $key ? 'checked' : '' }}
            >
            <span class="relationship-status-option-icon" aria-hidden="true">{{ $meta['icon'] }}</span>
            <span class="relationship-status-option-label">{{ $meta['label'] }}</span>
        </label>
    @endforeach
</div>
@error('relationship_status') <small class="form-error">{{ $message }}</small> @enderror
