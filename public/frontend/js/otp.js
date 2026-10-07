/*
 * Kids Avon – "Send OTP" links.
 * <a data-send-otp="/otp/send" data-mobile-input="#mobile" data-status="#otp-status">Send OTP</a>
 */
document.addEventListener('click', async (event) => {
    const link = event.target.closest('[data-send-otp]');
    if (!link) return;
    event.preventDefault();

    const input = document.querySelector(link.dataset.mobileInput);
    const status = document.querySelector(link.dataset.status);
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const show = (text, isError) => {
        if (!status) return;
        status.textContent = text;
        status.classList.toggle('is-error', !!isError);
    };

    if (!input || !input.value.trim()) {
        show('Please enter your mobile number first.', true);
        input && input.focus();
        return;
    }

    link.classList.add('is-sending');
    show('Sending…');

    try {
        const response = await fetch(link.dataset.sendOtp, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ mobile: input.value }),
        });
        const data = await response.json().catch(() => ({}));

        if (response.ok) {
            show(data.message || 'OTP sent!');
            link.textContent = 'Resend OTP';
            const otp = document.querySelector('input[name="otp"]');
            otp && otp.focus();
        } else if (response.status === 429) {
            show('Too many requests. Please wait a minute and try again.', true);
        } else {
            const errors = data.errors && data.errors.mobile;
            show((errors && errors[0]) || data.message || 'Could not send the OTP. Please try again.', true);
        }
    } catch (e) {
        show('Could not send the OTP. Please check your internet and try again.', true);
    } finally {
        link.classList.remove('is-sending');
    }
});
