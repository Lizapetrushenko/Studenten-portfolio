@props(['status'])

@if ($status)
    <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible" x-transition.opacity role="status" aria-live="polite" {{ $attributes->merge(['class' => 'success-toast']) }}>
        <span class="success-toast-icon" aria-hidden="true">✓</span>
        <div class="success-toast-message">
            <strong>Gelukt</strong>
            <span>{{ match($status) { 'verification-link-sent' => 'Er is een nieuwe bevestigingslink verstuurd.', default => $status } }}</span>
        </div>
        <button type="button" class="success-toast-close" aria-label="Melding sluiten" @click="visible = false">×</button>
    </div>
@endif
