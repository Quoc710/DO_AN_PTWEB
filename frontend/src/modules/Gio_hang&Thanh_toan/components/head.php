<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$sessionUser = $_SESSION['user'] ?? null;
$displayName = $sessionUser
    ? ($sessionUser['TenKH'] ?? $sessionUser['TenNV'] ?? 'Khách')
    : 'Khách';
$userEmail = $sessionUser
    ? ($sessionUser['Email'] ?? ($sessionUser['TenTK'] ?? 'guest@auraminimal.vn'))
    : 'guest@auraminimal.vn';
$roleTitle = $sessionUser ? 'Khách Hàng' : 'Khách';
$firstLetter = mb_strtoupper(mb_substr($displayName, 0, 1, 'UTF-8'));

$pageTitle = $pageTitle ?? 'Aura Fabrics - Kho Vải Thời Trang & May Mặc Cao Cấp';
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-variant": "#dce2f3", "primary-fixed-dim": "#c0c6db",
                        "secondary-container": "#82f5c1", "surface": "#f9f9ff", "tertiary": "#000000",
                        "outline": "#76777d", "primary-container": "#141b2b",
                        "on-tertiary-fixed-variant": "#6e3900", "surface-tint": "#575e70",
                        "on-secondary-fixed-variant": "#005137", "on-secondary": "#ffffff",
                        "tertiary-container": "#2f1500", "on-primary-container": "#7d8497",
                        "on-primary-fixed": "#141b2b", "background": "#f9f9ff",
                        "on-secondary-container": "#00714e", "surface-container": "#e7eefe",
                        "primary": "#000000", "inverse-surface": "#2a313d",
                        "on-primary-fixed-variant": "#404758", "inverse-on-surface": "#ebf1ff",
                        "error": "#ba1a1a", "surface-container-high": "#e2e8f8",
                        "primary-fixed": "#dce2f7", "on-background": "#151c27",
                        "on-tertiary": "#ffffff", "tertiary-fixed": "#ffdcc3",
                        "on-surface-variant": "#45464c", "on-error": "#ffffff",
                        "surface-bright": "#f9f9ff", "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f0f3ff", "surface-dim": "#d3daea",
                        "on-surface": "#151c27", "on-tertiary-fixed": "#2f1500",
                        "outline-variant": "#c6c6cd", "on-primary": "#ffffff",
                        "secondary": "#006c4a", "on-secondary-fixed": "#002114",
                        "on-tertiary-container": "#c76c00", "secondary-fixed": "#85f8c4",
                        "surface-container-highest": "#dce2f3", "inverse-primary": "#c0c6db",
                        "tertiary-fixed-dim": "#ffb77d", "secondary-fixed-dim": "#68dba9",
                        "error-container": "#ffdad6", "on-error-container": "#93000a"
                    },
                    borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    spacing: {
                        "space-xs": "0.25rem", "space-lg": "1.5rem", "space-sm": "0.5rem",
                        "space-xl": "2rem", "margin": "2rem", "space-md": "1rem", "gutter": "1.25rem"
                    },
                    fontFamily: {
                        "label-sm": ["Plus Jakarta Sans"], "headline-lg": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"], "headline-sm": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"], "body-md": ["Plus Jakarta Sans"],
                        "label-lg": ["Plus Jakarta Sans"], "display": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"], "body-sm": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "label-sm": ["10px", { lineHeight: "14px", letterSpacing: "0.06em", fontWeight: "600" }],
                        "headline-lg": ["24px", { lineHeight: "32px", letterSpacing: "-0.015em", fontWeight: "700" }],
                        "body-lg": ["15px", { lineHeight: "24px", fontWeight: "400" }],
                        "headline-sm": ["16px", { lineHeight: "24px", fontWeight: "600" }],
                        "headline-md": ["20px", { lineHeight: "28px", letterSpacing: "-0.01em", fontWeight: "600" }],
                        "body-md": ["13px", { lineHeight: "20px", fontWeight: "400" }],
                        "label-lg": ["13px", { lineHeight: "18px", letterSpacing: "0.01em", fontWeight: "600" }],
                        "display": ["32px", { lineHeight: "40px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "label-md": ["11px", { lineHeight: "16px", letterSpacing: "0.04em", fontWeight: "600" }],
                        "body-sm": ["12px", { lineHeight: "18px", fontWeight: "400" }]
                    }
                }
            }
        };
    </script>
    <style>
        html, body { margin: 0; padding: 0; }
        body { overscroll-behavior: none; font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { display: none; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background font-body-md text-on-background antialiased">
