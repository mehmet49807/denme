@once
@guest
<dialog class="gk-google-gate" id="gkGoogleSignupGate" aria-labelledby="gkGoogleGateTitle">
    <form method="POST" action="{{ route('auth.google.prepare') }}" class="gk-google-gate__panel" id="gkGoogleGateForm">
        @csrf
        <input type="hidden" name="city" id="gkGoogleGateCity" value="">

        <header class="gk-google-gate__head">
            <h2 id="gkGoogleGateTitle">Google ile üye ol</h2>
            <p>Cinsiyetini seç, sözleşmeleri onayla — saniyeler içinde hesabın açılsın.</p>
            <button type="button" class="gk-google-gate__close" data-gk-google-gate-close aria-label="Kapat">×</button>
        </header>

        <div class="form-group auth-field gk-google-gate__gender">
            <label for="gkGoogleGateGender">Cinsiyet</label>
            <div class="gk-google-gate__choices" role="group" aria-label="Cinsiyet">
                <label class="gk-google-gate__choice">
                    <input type="radio" name="gender" value="female" required>
                    <span>Kadın</span>
                </label>
                <label class="gk-google-gate__choice">
                    <input type="radio" name="gender" value="male" required>
                    <span>Erkek</span>
                </label>
            </div>
        </div>

        <p class="gk-google-gate__perk">Kadınlarda mesajlaşma ve kimler baktı ücretsiz.</p>

        <div class="auth-consent gk-google-gate__consent">
            <label class="auth-consent-item">
                <input type="checkbox" name="privacy_accepted" value="1" id="gkGoogleGatePrivacy" required>
                <span><a href="{{ route('privacy') }}" target="_blank" rel="noopener">Gizlilik Sözleşmesi</a>'ni kabul ediyorum</span>
            </label>
            <label class="auth-consent-item">
                <input type="checkbox" name="kvkk_accepted" value="1" id="gkGoogleGateKvkk" required>
                <span><a href="{{ route('kvkk') }}" target="_blank" rel="noopener">KVKK Aydınlatma</a>'nı kabul ediyorum</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-full" id="gkGoogleGateSubmit" disabled>
            <span class="btn-google-login__icon" aria-hidden="true">@include('partials.google-icon', ['size' => 18])</span>
            <span>Google ile devam et</span>
        </button>
    </form>
</dialog>

<style>
.gk-google-gate {
    border: none;
    padding: 0;
    border-radius: 20px;
    max-width: min(420px, calc(100vw - 1.5rem));
    width: 100%;
    background: transparent;
    box-shadow: none;
}
.gk-google-gate::backdrop {
    background: rgba(20, 10, 36, 0.62);
    backdrop-filter: blur(4px);
}
.gk-google-gate__panel {
    position: relative;
    margin: 0;
    padding: 1.35rem 1.25rem 1.25rem;
    background: #fff;
    border-radius: 20px;
    border: 1px solid rgba(26, 18, 37, 0.08);
    box-shadow: 0 24px 60px rgba(20, 10, 36, 0.28);
    display: grid;
    gap: 0.9rem;
}
.gk-google-gate__head h2 {
    margin: 0 2rem 0.35rem 0;
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #1a1225;
}
.gk-google-gate__head p {
    margin: 0 2rem 0 0;
    font-size: 0.9rem;
    line-height: 1.45;
    color: #6b5f72;
}
.gk-google-gate__close {
    position: absolute;
    top: 0.7rem;
    right: 0.75rem;
    width: 2rem;
    height: 2rem;
    border: none;
    border-radius: 999px;
    background: rgba(26, 18, 37, 0.06);
    color: #1a1225;
    font-size: 1.35rem;
    line-height: 1;
    cursor: pointer;
}
.gk-google-gate__choices {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.55rem;
}
.gk-google-gate__choice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    min-height: 2.75rem;
    padding: 0.55rem 0.75rem;
    border-radius: 14px;
    border: 1.5px solid rgba(26, 18, 37, 0.12);
    background: #fff8f4;
    font-weight: 750;
    cursor: pointer;
}
.gk-google-gate__choice:has(input:checked) {
    border-color: #ff5c70;
    background: rgba(255, 92, 112, 0.1);
    color: #c2314a;
}
.gk-google-gate__choice input {
    accent-color: #ff5c70;
}
.gk-google-gate__perk {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 650;
    color: #7c3aed;
}
.gk-google-gate__consent {
    margin: 0;
}
.gk-google-gate #gkGoogleGateSubmit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    min-height: 2.9rem;
}
.gk-google-gate #gkGoogleGateSubmit:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}
</style>

<script>
(function () {
    var dialog = document.getElementById('gkGoogleSignupGate');
    if (!dialog) return;
    var form = document.getElementById('gkGoogleGateForm');
    var cityInput = document.getElementById('gkGoogleGateCity');
    var privacy = document.getElementById('gkGoogleGatePrivacy');
    var kvkk = document.getElementById('gkGoogleGateKvkk');
    var submit = document.getElementById('gkGoogleGateSubmit');

    function sync() {
        var gender = form.querySelector('input[name="gender"]:checked');
        submit.disabled = !(gender && privacy.checked && kvkk.checked);
    }

    form.addEventListener('change', sync);
    sync();

    document.querySelectorAll('[data-gk-google-gate-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (typeof dialog.close === 'function') dialog.close();
        });
    });

    dialog.addEventListener('click', function (e) {
        if (e.target === dialog && typeof dialog.close === 'function') dialog.close();
    });

    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-google-signup-gate]');
        if (!trigger) return;
        e.preventDefault();
        if (cityInput) {
            cityInput.value = trigger.getAttribute('data-google-city') || '';
        }
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            window.location.href = trigger.getAttribute('href') || @json(url('auth/google'));
        }
    });
})();
</script>
@endguest
@endonce
