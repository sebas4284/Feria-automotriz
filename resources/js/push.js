function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}

function isIos() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
}

function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

export function pushBanner(vapidPublicKey) {
    return {
        status: 'checking',
        loading: false,

        async init() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                this.status = isIos() ? 'unsupported-ios' : 'unsupported';
                return;
            }

            if (isIos() && !isStandalone()) {
                this.status = 'unsupported-ios';
                return;
            }

            if (Notification.permission === 'denied') {
                this.status = 'denied';
                return;
            }

            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                const subscription = await registration.pushManager.getSubscription();
                this.status = subscription ? 'subscribed' : 'default';
            } catch (e) {
                this.status = 'unsupported';
            }
        },

        async subscribe() {
            this.loading = true;

            try {
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    this.status = permission === 'denied' ? 'denied' : 'default';
                    return;
                }

                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                });

                await fetch('/push-subscriptions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(subscription.toJSON()),
                });

                this.status = 'subscribed';
            } catch (e) {
                this.status = 'default';
            } finally {
                this.loading = false;
            }
        },
    };
}
