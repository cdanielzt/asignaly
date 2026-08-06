// Live CSRF token. Read per request from the cookie Laravel refreshes on every
// response — the <meta> tag is only written on a full page load, so it goes
// stale in an SPA as soon as the session token rotates (login, impersonation,
// expiry) and every write then 419s with "CSRF token mismatch".
export const csrfHeaders = () => ({
    'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/)?.[1] ?? ''),
});
