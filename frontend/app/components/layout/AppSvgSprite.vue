<template>
  <svg class="svg-sprite" aria-hidden="true" focusable="false">
    <symbol id="menu" viewBox="0 0 24 24">
      <path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="arrow-up" viewBox="0 0 24 24">
      <path d="m12 5-7 7m7-7 7 7m-7-7v14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="user" viewBox="0 0 24 24">
      <circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="M4 21c1.5-4.1 4.2-6 8-6s6.5 1.9 8 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="info" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="M12 10v6M12 7h.01" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
    </symbol>
    <symbol id="building" viewBox="0 0 24 24">
      <path d="M4 21V7h5V3h6v7h5v11H4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M8 11h2M8 15h2M14 7h2M14 11h2M14 15h2M10 21v-4h4v4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="road" viewBox="0 0 24 24">
      <path d="M8 21 11 3M16 21 13 3M12 8v2M12 14v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="medical" viewBox="0 0 24 24">
      <path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
      <rect x="4" y="4" width="16" height="16" rx="3" fill="none" stroke="currentColor" stroke-width="2" />
    </symbol>
    <symbol id="education" viewBox="0 0 24 24">
      <path d="m3 8 9-4 9 4-9 4-9-4ZM7 11v5c2.7 2 7.3 2 10 0v-5M20 9v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="file" viewBox="0 0 24 24">
      <path d="M6 3h8l4 4v14H6V3z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M14 3v5h5M9 13h6M9 17h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="grid" viewBox="0 0 24 24">
      <rect x="3" y="3" width="7" height="7" rx="1.6" fill="none" stroke="currentColor" stroke-width="2" />
      <rect x="14" y="3" width="7" height="7" rx="1.6" fill="none" stroke="currentColor" stroke-width="2" />
      <rect x="3" y="14" width="7" height="7" rx="1.6" fill="none" stroke="currentColor" stroke-width="2" />
      <rect x="14" y="14" width="7" height="7" rx="1.6" fill="none" stroke="currentColor" stroke-width="2" />
    </symbol>
    <symbol id="users" viewBox="0 0 24 24">
      <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM2.5 21c1-4 3-6 5.5-6s4.6 2 5.5 6M17 11a3 3 0 1 0 0-6M15.2 15.2c2.5.3 4.2 2.1 5.3 5.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="shop" viewBox="0 0 24 24">
      <path d="M4 10h16l-2-6H6l-2 6ZM6 10v10h12V10M9 20v-5h6v5" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="volume" viewBox="0 0 24 24">
      <path d="M4 15V9h4l6-4v14l-6-4H4ZM17 9c1 .9 1.5 1.9 1.5 3S18 14.1 17 15M19.5 6.5A7.7 7.7 0 0 1 22 12a7.7 7.7 0 0 1-2.5 5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="parking" viewBox="0 0 24 24">
      <rect x="4" y="4" width="16" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="M9 17V7h4a3 3 0 1 1 0 6H9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="tree" viewBox="0 0 24 24">
      <path d="M12 21v-6M8 17h8M6 15a4 4 0 0 1 2-7 4.5 4.5 0 0 1 8 0 4 4 0 0 1 2 7H6Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="hand-heart" viewBox="0 0 24 24">
      <path d="M4 15c3 0 4 2 7 2h3c1.3 0 2-.7 2-1.6S15.3 14 14 14h-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      <path d="M4 19h8.5c2.8 0 5.3-1.7 7.5-4M4 12v9M14.5 4.5A3.2 3.2 0 0 1 20 6.8c0 3.2-5.5 6.2-5.5 6.2S9 10 9 6.8a3.2 3.2 0 0 1 5.5-2.3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="star" viewBox="0 0 24 24">
      <path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.8 1-6.1-4.4-4.3 6.1-.9L12 3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="wallet" viewBox="0 0 24 24">
      <path d="M4 7h15a2 2 0 0 1 2 2v10H4V7ZM4 7l11-3 1 3" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M16 13h5" fill="none" stroke="currentColor" stroke-width="2" />
    </symbol>
    <symbol id="more" viewBox="0 0 24 24">
      <circle cx="6" cy="12" r="1.6" fill="currentColor" />
      <circle cx="12" cy="12" r="1.6" fill="currentColor" />
      <circle cx="18" cy="12" r="1.6" fill="currentColor" />
    </symbol>
    <symbol id="plus" viewBox="0 0 24 24">
      <path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
    </symbol>
    <symbol id="trash" viewBox="0 0 24 24">
      <path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="camera" viewBox="0 0 24 24">
      <path d="M5 7h3l1.7-2h4.6L16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <circle cx="12" cy="13" r="4" fill="none" stroke="currentColor" stroke-width="2" />
    </symbol>
    <symbol id="play" viewBox="0 0 24 24">
      <path d="M8 5v14l11-7L8 5z" fill="currentColor" />
    </symbol>
    <symbol id="shield" viewBox="0 0 24 24">
      <path d="M12 3 20 6v6.2c0 4.7-3 7.8-8 9.8-5-2-8-5.1-8-9.8V6l8-3z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="m8.5 12 2.4 2.4 4.8-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="book" viewBox="0 0 24 24">
      <path d="M5 4h11a3 3 0 0 1 3 3v13H8a3 3 0 0 0-3-3V4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="M5 17V7a3 3 0 0 1 3-3M9 8h6M9 12h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="bell" viewBox="0 0 24 24">
      <path d="M18 10a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9zM10 21h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="clock" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="M12 7v6l4 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="refresh" viewBox="0 0 24 24">
      <path d="M20 12a8 8 0 0 1-13.6 5.7M4 12A8 8 0 0 1 17.6 6.3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      <path d="M4 18v-5h5M20 6v5h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="bolt" viewBox="0 0 24 24">
      <path d="m13 2-9 12h7l-1 8 10-13h-7l1-7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="trend" viewBox="0 0 24 24">
      <path d="m4 16 5-5 4 4 7-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M16 7h4v4M4 21h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="heart" viewBox="0 0 24 24">
      <path d="M12 21S4 16.2 4 9.8A4.7 4.7 0 0 1 12 6a4.7 4.7 0 0 1 8 3.8C20 16.2 12 21 12 21z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="home" viewBox="0 0 24 24">
      <path d="m3 11 9-8 9 8v10h-6v-6H9v6H3V11z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="comment" viewBox="0 0 24 24">
      <path d="M4 5h16v11H8l-4 4V5z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="upload" viewBox="0 0 24 24">
      <path d="M12 16V4M7 9l5-5 5 5M5 20h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="arrow" viewBox="0 0 24 24">
      <path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="back" viewBox="0 0 24 24">
      <path d="M19 12H5M11 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="flag" viewBox="0 0 24 24">
      <path d="M5 21V4M5 4h12l-1.5 4L17 12H5" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="scale" viewBox="0 0 24 24">
      <path d="M12 4v17M5 7h14M7 7l-4 7h8L7 7zM17 7l-4 7h8l-4-7z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="edit" viewBox="0 0 24 24">
      <path d="M4 20h4l11-11-4-4L4 16v4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
      <path d="m14 6 4 4M12 20h8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="phone" viewBox="0 0 24 24">
      <path d="M7 4 5 6c-1 1-.6 5.5 4.2 10.3C14 21.1 18.5 21.5 19.5 20.5l2-2-4-4-2 2c-1.3-.5-2.7-1.4-4-2.7s-2.2-2.7-2.7-4l2-2L7 4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    </symbol>
    <symbol id="mail" viewBox="0 0 24 24">
      <rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="pin" viewBox="0 0 24 24">
      <path d="M12 22s7-6.2 7-12A7 7 0 1 0 5 10c0 5.8 7 12 7 12z" fill="none" stroke="currentColor" stroke-width="2" />
      <circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="2" />
    </symbol>
    <symbol id="check" viewBox="0 0 24 24">
      <path d="m5 12 4.5 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="lock" viewBox="0 0 24 24">
      <rect x="5" y="10" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="M8 10V8a4 4 0 0 1 8 0v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="eye" viewBox="0 0 24 24">
      <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" fill="none" stroke="currentColor" stroke-width="2" />
      <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2" />
    </symbol>
    <symbol id="share" viewBox="0 0 24 24">
      <circle cx="18" cy="5" r="3" fill="none" stroke="currentColor" stroke-width="2" />
      <circle cx="6" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2" />
      <circle cx="18" cy="19" r="3" fill="none" stroke="currentColor" stroke-width="2" />
      <path d="m8.7 10.7 6.6-4.4M8.7 13.3l6.6 4.4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
    </symbol>
    <symbol id="social-vk" viewBox="0 0 24 24">
      <path d="M4.9 7.5c.1 5.8 3 9.2 8.1 9.2h.3v-3.3c1.8.2 3.1 1.5 3.6 3.3h2.7a7.1 7.1 0 0 0-3.6-4.8 6.9 6.9 0 0 0 3.1-4.4h-2.5c-.5 1.9-2 3.6-3.3 3.8V7.5h-2.5v6.7c-1.4-.3-3.1-2.2-3.2-6.7H4.9Z" fill="currentColor" />
    </symbol>
    <symbol id="social-ok" viewBox="0 0 24 24">
      <circle cx="12" cy="6.4" r="3.1" fill="currentColor" />
      <path d="M16 11.2a1.3 1.3 0 0 0-1.8-.1 3.6 3.6 0 0 1-4.4 0 1.3 1.3 0 0 0-1.8 1.8 6.1 6.1 0 0 0 2.2 1.2l-2 2.1a1.3 1.3 0 1 0 1.9 1.8l1.9-2 1.9 2a1.3 1.3 0 0 0 1.9-1.8l-2-2.1A6.1 6.1 0 0 0 16 13a1.3 1.3 0 0 0 0-1.8Z" fill="currentColor" />
    </symbol>
    <symbol id="social-max" viewBox="0 0 64 64">
      <defs>
        <linearGradient id="max-logo-gradient" x1="5" y1="53" x2="59" y2="11" gradientUnits="userSpaceOnUse">
          <stop offset="0" stop-color="#42c7f3" />
          <stop offset=".46" stop-color="#3153d6" />
          <stop offset="1" stop-color="#9b2ee8" />
        </linearGradient>
      </defs>
      <rect width="64" height="64" rx="15" fill="url(#max-logo-gradient)" />
      <path d="M31.9 7.6c-12.7 0-23 9.9-23 22.2 0 4.3 1.1 8.5 2.2 12.6.8 3.1 1.7 6.3 1.7 9.5 0 3.6 2.3 4.9 5.5 4.9 3.5 0 7.9-1.9 11.2-5.4 1.5.8 3.5 1.5 6 1.9 11.1 1.8 22.2-6.2 22.2-18.6 0-13.6-11.5-27.1-25.8-27.1Zm-.3 12.1c8.2 0 15 6.5 15 14.2 0 7.2-6.5 12.7-13.4 12.7-4.1 0-6.9-1.2-9.3-3.1-1.4 1.6-3.1 3.5-5.3 3.9.2-4-2.6-10-2.6-14.8 0-7.2 6.8-12.9 15.6-12.9Z" fill="#fff" />
    </symbol>
  </svg>
</template>
