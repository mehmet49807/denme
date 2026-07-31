/**
 * Sosyal giriş katmanı.
 * Üretimde Google / Facebook Client ID ile gerçek OAuth bağlanır.
 * Şimdilik cihaz üzerinde sağlayıcı seçimi + teknik direktör adı ile oturum açılır.
 */

const GOOGLE_CLIENT_ID = import.meta.env.VITE_GOOGLE_CLIENT_ID || '';
const FACEBOOK_APP_ID = import.meta.env.VITE_FACEBOOK_APP_ID || '';

export function authConfigReady() {
  return {
    google: Boolean(GOOGLE_CLIENT_ID),
    facebook: Boolean(FACEBOOK_APP_ID),
  };
}

/**
 * @param {'google'|'facebook'|'guest'} provider
 * @param {string} managerName
 */
export async function signIn(provider, managerName) {
  const name = managerName.trim();

  if (provider === 'google' && GOOGLE_CLIENT_ID && window.google?.accounts?.id) {
    try {
      return await signInGoogleGis(name);
    } catch {
      /* local fallback */
    }
  }

  if (provider === 'facebook' && FACEBOOK_APP_ID && window.FB) {
    try {
      return await signInFacebookSdk(name);
    } catch {
      /* local fallback */
    }
  }

  // Yerel oturum (APK / demo): Google & Facebook butonları sağlayıcıyı kaydeder
  return {
    provider,
    managerName: name,
    email: provider === 'guest' ? null : `${slug(name)}@${provider}.local`,
    photoUrl: null,
    signedAt: Date.now(),
    mode: GOOGLE_CLIENT_ID || FACEBOOK_APP_ID ? 'fallback' : 'local',
  };
}

async function signInGoogleGis(managerName) {
  return new Promise((resolve, reject) => {
    if (!window.google?.accounts?.id) {
      reject(new Error('Google SDK yok'));
      return;
    }
    window.google.accounts.id.initialize({
      client_id: GOOGLE_CLIENT_ID,
      callback: (resp) => {
        const payload = parseJwt(resp.credential);
        resolve({
          provider: 'google',
          managerName: managerName || payload.name || 'Teknik Direktör',
          email: payload.email || null,
          photoUrl: payload.picture || null,
          signedAt: Date.now(),
          mode: 'oauth',
        });
      },
    });
    window.google.accounts.id.prompt((n) => {
      if (n.isNotDisplayed?.() || n.isSkippedMoment?.()) reject(new Error('Google iptal'));
    });
  });
}

async function signInFacebookSdk(managerName) {
  return new Promise((resolve, reject) => {
    window.FB.login(
      (response) => {
        if (!response.authResponse) {
          reject(new Error('Facebook iptal'));
          return;
        }
        window.FB.api('/me', { fields: 'name,email,picture' }, (me) => {
          resolve({
            provider: 'facebook',
            managerName: managerName || me.name || 'Teknik Direktör',
            email: me.email || null,
            photoUrl: me.picture?.data?.url || null,
            signedAt: Date.now(),
            mode: 'oauth',
          });
        });
      },
      { scope: 'public_profile,email' }
    );
  });
}

function parseJwt(token) {
  try {
    const b64 = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
    return JSON.parse(atob(b64));
  } catch {
    return {};
  }
}

function slug(s) {
  return String(s)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '')
    .slice(0, 16) || 'coach';
}
