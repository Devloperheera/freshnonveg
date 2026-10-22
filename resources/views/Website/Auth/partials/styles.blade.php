<style>
:root {
    --brand-red: #D32F2F;
    --brand-orange: #F57C00;
    --brand-green: #2BAE49;
    --brand-dark: #111827;
    --brand-cream: #fff8f3;
}
.auth-wrap { min-height: calc(100vh - 120px); display: flex; align-items: center; justify-content: center; padding: 40px 16px; background: var(--brand-cream); }
.auth-card { width: 100%; max-width: 460px; background: #fff; border: 3px solid var(--brand-dark); border-radius: 20px; box-shadow: 6px 6px 0 var(--brand-dark); padding: 32px 28px; }
.auth-card h1 { font-size: 1.6rem; font-weight: 800; margin-bottom: 6px; }
.auth-card .sub { color: #6b7280; margin-bottom: 22px; font-size: .92rem; }
.auth-card .form-label { font-weight: 700; font-size: .82rem; text-transform: uppercase; }
.auth-card .form-control { border: 2px solid var(--brand-dark); padding: 11px 12px; border-radius: 10px; font-weight: 600; box-shadow: 2px 2px 0 var(--brand-dark); }
.auth-card .form-control:focus { border-color: var(--brand-dark); box-shadow: 3px 3px 0 var(--brand-dark); outline: 0; }
.auth-card .form-control.is-invalid { border-color: var(--brand-red); box-shadow: 2px 2px 0 var(--brand-red); }
.btn-brand { width: 100%; border: 3px solid var(--brand-dark); border-radius: 50px; font-weight: 800; padding: 12px; color: #fff; background: linear-gradient(135deg, var(--brand-red), var(--brand-orange), var(--brand-green)); box-shadow: 4px 4px 0 var(--brand-dark); }
.btn-brand:hover { opacity: .95; color: #fff; transform: translate(-1px,-1px); }
.btn-google { width: 100%; border: 3px solid var(--brand-dark); border-radius: 50px; font-weight: 800; padding: 11px; background: #fff; box-shadow: 4px 4px 0 var(--brand-dark); }
.auth-links { text-align: center; margin-top: 16px; font-size: .9rem; }
.auth-links a { color: var(--brand-green); font-weight: 700; text-decoration: none; }
.auth-links a:hover { color: var(--brand-orange); text-decoration: underline; }
.alert-auth { border-radius: 12px; font-size: .88rem; padding: 10px 12px; margin-bottom: 14px; }
.auth-tabs { display: flex; gap: 8px; background: #f3f4f6; padding: 6px; border-radius: 12px; border: 2px solid var(--brand-dark); }
.auth-tab { flex: 1; border: none; background: transparent; font-weight: 800; padding: 8px 10px; border-radius: 8px; color: #6b7280; }
.auth-tab.active { background: #fff; color: var(--brand-dark); box-shadow: 2px 2px 0 var(--brand-dark); }
.pass-wrap { position: relative; }
.pass-wrap .form-control { padding-right: 44px; }
.pass-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: #6b7280; padding: 4px 8px; cursor: pointer; }
.pass-toggle:hover { color: var(--brand-green); }
.auth-wrap--compact { min-height: calc(100vh - 100px); padding: 20px 16px 28px; }
.auth-card--compact { max-width: 390px; padding: 20px 18px 18px; }
.auth-card--compact h1 { font-size: 1.32rem; margin-bottom: 4px; }
.auth-card--compact .sub { margin-bottom: 14px; font-size: .86rem; line-height: 1.45; }
.auth-card--compact .form-label { font-size: .76rem; margin-bottom: 4px; }
.auth-card--compact .form-control { padding: 9px 11px; font-size: .9rem; border-radius: 10px; }
.auth-card--compact .mb-compact { margin-bottom: .65rem !important; }
.auth-card--compact .btn-brand,
.auth-card--compact .btn-google { padding: 10px; font-size: .92rem; }
.auth-card--compact .auth-links { margin-top: 12px; font-size: .86rem; }
</style>
