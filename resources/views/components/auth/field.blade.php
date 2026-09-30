@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'value' => null, 'autofocus' => false])
<div class="mb-3">
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($autofocus) autofocus @endif
        {{ $attributes->class(['form-control form-control-lg', 'is-invalid' => $errors->has($name)]) }}
    >
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
