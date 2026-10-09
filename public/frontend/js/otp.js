/*
 * Kids Avon – "Send OTP to my email" link.
 *
 * <form data-otp-form data-purpose="login|register"> … <a data-send-otp="/otp/send" data-status="#otp-status">
 *
 * Sends the form's email (plus the bot-trap fields and the robot-check token)
 * to the server, shows the answer, then waits 60 seconds before the code can be
 * sent again. The robot check is refreshed afterwards, because each Cloudflare
 * token can only be used once and the final form needs a fresh one.
 */
document.addEventListener('click', async (event) => {
    const link = event.target.closest('[data-send-otp]');
    if (!link) return;
    event.preventDefault();
    if (link.classList.contains('is-sending') || link.classList.contains('is-waiting')) return;

    const form = link.closest('form');
    const email = form.querySelector('input[name="email"]');
    const status = document.querySelector(link.dataset.status);
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const show = (text, isError) => {
        if (!status) return;
        status.textContent = text;
        status.classList.toggle('is-error', !!isError);
    };

    if (!email || !email.value.trim()) {
        show('Please enter your email address first.', true);
        email && email.focus();
        return;
    }

    // The robot check may still be working (or refreshing after the last send): give it a few seconds.
    const turnstileBox = form.querySelector('.cf-turnstile');
    const robotToken = () => (form.querySelector('input[name="cf-turnstile-response"]') || {}).value;
    if (turnstileBox && !robotToken()) {
        show('Checking you’re not a robot…');
        link.classList.add('is-sending');
        for (let i = 0; i < 16 && !robotToken(); i++) await new Promise((r) => setTimeout(r, 500));
        link.classList.remove('is-sending');
        if (!robotToken()) {
            show('Please complete the robot check first, then tap “Send OTP” again.', true);
            return;
        }
    }

    const body = new FormData();
    ['email', 'name', 'username', 'kv_website', 'kv_ts', 'cf-turnstile-response'].forEach((field) => {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) body.append(field, input.value);
    });
    body.append('purpose', form.dataset.purpose || 'login');

    const label = link.dataset.label || link.textContent;
    link.dataset.label = label;
    link.classList.add('is-sending');
    show('Sending…');

    const refreshRobotCheck = () => {
        if (turnstileBox && window.turnstile) window.turnstile.reset(turnstileBox);
    };

    try {
        const response = await fetch(link.dataset.sendOtp, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body,
        });
        const data = await response.json().catch(() => ({}));

        if (response.ok) {
            show(data.message || 'Code sent! Please check your email.');
            const otp = form.querySelector('input[name="otp"]');
            otp && otp.focus();
            startCountdown(link, label, data.resend_seconds || 60);
        } else if (response.status === 429) {
            show('Too many requests. Please wait a minute and try again.', true);
        } else {
            const errors = data.errors ? Object.values(data.errors)[0] : null;
            show((errors && errors[0]) || data.message || 'Could not send the code. Please try again.', true);
        }
    } catch (e) {
        show('Could not send the code. Please check your internet and try again.', true);
    } finally {
        link.classList.remove('is-sending');
        refreshRobotCheck();
    }
});

function startCountdown(link, label, seconds) {
    link.classList.add('is-waiting');
    const tick = () => {
        if (seconds <= 0) {
            link.classList.remove('is-waiting');
            link.textContent = 'Resend code';
            return;
        }
        link.textContent = `Resend code in ${seconds}s`;
        seconds -= 1;
        setTimeout(tick, 1000);
    };
    tick();
}
