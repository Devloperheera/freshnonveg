<style>
.adm-catalog-table { font-size: 12px; }
.adm-catalog-table thead th { font-size: 10px; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
.adm-prod-thumb { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; flex-shrink: 0; border: 1px solid #e2e8f0; }
.adm-prod-thumb--empty {
    display: inline-flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8; font-size: 18px;
}
.adm-user-cell { display: flex; align-items: flex-start; gap: 10px; min-width: 200px; color: inherit; }
.adm-user-cell:hover .adm-user-cell__name { color: #2BAE49; }
.adm-user-cell__av {
    width: 40px; height: 40px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
    background: linear-gradient(135deg, #F57C00, #D32F2F); color: #fff; font-weight: 800; font-size: 14px;
    display: flex; align-items: center; justify-content: center;
}
.adm-user-cell__av img { width: 100%; height: 100%; object-fit: cover; }
.adm-user-cell__name { font-weight: 700; color: #0f172a; text-decoration: none; font-size: 13px; display: block; }
.adm-user-cell__name:hover { color: #2BAE49; }
.adm-user-cell__meta { font-size: 11px; color: #64748b; line-height: 1.4; }
.adm-user-cell__meta span { display: block; }
.adm-user-cell__meta i { margin-right: 3px; opacity: .75; }
.adm-prod-cell { min-width: 220px; }
.adm-prod-cell-link { display: flex; align-items: flex-start; gap: 10px; color: inherit; }
.adm-prod-cell-link:hover .adm-prod-cell__name { color: #2BAE49; }
.adm-prod-cell__name { font-weight: 700; color: #0f172a; text-decoration: none; font-size: 13px; display: block; line-height: 1.25; }
.adm-prod-cell__name:hover { color: #2BAE49; }
.adm-prod-cell__meta { font-size: 11px; color: #64748b; margin-top: 2px; }
.adm-prod-cell__meta span { display: inline-block; margin-right: 8px; }
.adm-stat-pill {
    display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: 700;
}
.adm-stat-pill strong { color: #2BAE49; font-size: 15px; }
</style>
