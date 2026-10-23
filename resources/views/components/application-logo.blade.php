<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    <defs>
        <!-- Background Gradient -->
        <linearGradient id="tt-bg" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#0F172A"/>
            <stop offset="55%" stop-color="#1E1B4B"/>
            <stop offset="100%" stop-color="#0284C7"/>
        </linearGradient>

        <!-- Metallic Border Gradient -->
        <linearGradient id="tt-border" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#A5B4FC" stop-opacity="0.8"/>
            <stop offset="40%" stop-color="#6366F1" stop-opacity="0.4"/>
            <stop offset="100%" stop-color="#38BDF8" stop-opacity="0.8"/>
        </linearGradient>

        <!-- Ambient Cyan Glow -->
        <radialGradient id="tt-glow" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(28 24) rotate(45) scale(18)">
            <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.55"/>
            <stop offset="60%" stop-color="#6366F1" stop-opacity="0.25"/>
            <stop offset="100%" stop-color="#0F172A" stop-opacity="0"/>
        </radialGradient>

        <!-- Card 1 Glass Gradient -->
        <linearGradient id="tt-card1" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#A78BFA" stop-opacity="0.45"/>
            <stop offset="100%" stop-color="#6366F1" stop-opacity="0.15"/>
        </linearGradient>

        <!-- Card 2 Glass Gradient -->
        <linearGradient id="tt-card2" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.5"/>
            <stop offset="100%" stop-color="#0284C7" stop-opacity="0.2"/>
        </linearGradient>

        <!-- Checkmark Facet Left -->
        <linearGradient id="tt-facet-left" x1="19" y1="21" x2="25" y2="34" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#C084FC"/>
            <stop offset="100%" stop-color="#6366F1"/>
        </linearGradient>

        <!-- Checkmark Facet Right -->
        <linearGradient id="tt-facet-right" x1="24" y1="34" x2="38" y2="12" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#38BDF8"/>
            <stop offset="50%" stop-color="#00F2FE"/>
            <stop offset="100%" stop-color="#FFFFFF"/>
        </linearGradient>

        <!-- Crisp Checkmark Stroke -->
        <linearGradient id="tt-stroke" x1="18" y1="22" x2="38" y2="12" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#E0E7FF"/>
            <stop offset="50%" stop-color="#BAE6FD"/>
            <stop offset="100%" stop-color="#FFFFFF"/>
        </linearGradient>
    </defs>

    <!-- Outer App Icon Squircle Container -->
    <rect width="48" height="48" rx="12" fill="url(#tt-bg)"/>
    <rect x="0.75" y="0.75" width="46.5" height="46.5" rx="11.25" fill="none" stroke="url(#tt-border)" stroke-width="1.5"/>

    <!-- Subtle Ambient Glow -->
    <circle cx="28" cy="24" r="14" fill="url(#tt-glow)"/>

    <!-- Layer 1: Translucent Back Task Board (Fanned -20 deg) -->
    <g transform="translate(13, 14) rotate(-22)">
        <rect x="0" y="0" width="13.5" height="18" rx="3" fill="url(#tt-card1)" stroke="#A78BFA" stroke-width="1" stroke-opacity="0.6"/>
        <line x1="2.5" y1="4.5" x2="8.5" y2="4.5" stroke="#FFFFFF" stroke-opacity="0.4" stroke-width="1" stroke-linecap="round"/>
        <line x1="2.5" y1="8" x2="11" y2="8" stroke="#FFFFFF" stroke-opacity="0.3" stroke-width="1" stroke-linecap="round"/>
    </g>

    <!-- Layer 2: Translucent Middle Task Board (Fanned -8 deg) -->
    <g transform="translate(15.5, 12.5) rotate(-9)">
        <rect x="0" y="0" width="14" height="19" rx="3" fill="url(#tt-card2)" stroke="#38BDF8" stroke-width="1" stroke-opacity="0.7"/>
        <line x1="3" y1="5" x2="9" y2="5" stroke="#FFFFFF" stroke-opacity="0.5" stroke-width="1" stroke-linecap="round"/>
        <line x1="3" y1="9" x2="11.5" y2="9" stroke="#FFFFFF" stroke-opacity="0.35" stroke-width="1" stroke-linecap="round"/>
    </g>

    <!-- Layer 3: Dynamic 3D Chevron Checkmark -->
    <!-- Left Wing Solid Fill -->
    <path d="M19 22.5L24.5 33.5L24.5 25.5L20 21.5Z" fill="url(#tt-facet-left)" fill-opacity="0.95"/>
    <!-- Right Wing Solid Fill -->
    <path d="M24.5 33.5L37.5 12.5L27 22L24.5 25.5Z" fill="url(#tt-facet-right)" fill-opacity="0.95"/>
    <!-- Main Precision Edge Stroke -->
    <path d="M18.5 22.5L24.5 34L37.5 12" stroke="url(#tt-stroke)" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Dynamic Velocity Accent Dot -->
    <circle cx="39.5" cy="10" r="1.5" fill="#38BDF8"/>
    <circle cx="39.5" cy="10" r="2.5" stroke="#38BDF8" stroke-width="0.75" stroke-opacity="0.5"/>
</svg>
