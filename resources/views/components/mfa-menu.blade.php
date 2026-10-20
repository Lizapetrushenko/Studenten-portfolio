@if (auth()->user()->mfa_enabled)
    <div x-data x-id="['mfa-disable-title', 'mfa-disable-description']">
        <button type="button" @click="$dispatch('open-mfa-disable')" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">MFA uitschakelen</button>
        <template x-teleport="body">
            <dialog @open-mfa-disable.window="if (!$el.open) $el.showModal()"
                    @click="if ($event.target === $el) $el.close()"
                    :aria-labelledby="$id('mfa-disable-title')"
                    :aria-describedby="$id('mfa-disable-description')"
                    class="mfa-disable-dialog"
                    style="width: min(28rem, calc(100% - 2rem)); margin: auto; padding: 0; border: 0; border-radius: 1rem; background: white; color: #302d73; box-shadow: 0 24px 64px #0004;">
                <div style="padding: 1.75rem;">
                    <h2 :id="$id('mfa-disable-title')" style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1rem;">MFA uitschakelen?</h2>
                    <p :id="$id('mfa-disable-description')">Weet je zeker dat je MFA wilt uitschakelen? Je account is daarna niet meer beveiligd met een authenticator-code.</p>
                    <form method="POST" action="{{ route('mfa.disable') }}" style="display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.5rem;">
                        @csrf
                        <button type="button" autofocus @click="$el.closest('dialog').close()" style="padding: .75rem 1rem; border-radius: .5rem; border: 1px solid #d9d7e1; background: #f3f4f6; color: #302d73; font-weight: 600;">Annuleren</button>
                        <button type="submit" style="padding: .75rem 1rem; border-radius: .5rem; border: 0; background: #b91c1c; color: white; font-weight: 600;">Ja, MFA uitschakelen</button>
                    </form>
                </div>
            </dialog>
        </template>
        <style>.mfa-disable-dialog::backdrop { background: rgb(15 23 42 / 50%); backdrop-filter: blur(3px); }</style>
    </div>
@else
    <x-dropdown-link :href="route('mfa.setup')">MFA instellen</x-dropdown-link>
@endif