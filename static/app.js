(() => {
  const commonSymbols = {
    menu: '<symbol id="menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>',
    "arrow-up": '<symbol id="arrow-up" viewBox="0 0 24 24"><path d="m12 5-7 7m7-7 7 7m-7-7v14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></symbol>',
    user: '<symbol id="user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 21c1.5-4.1 4.2-6 8-6s6.5 1.9 8 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>',
    edit: '<symbol id="edit" viewBox="0 0 24 24"><path d="M4 20h4l11-11-4-4L4 16v4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m14 6 4 4M12 20h8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>',
    phone: '<symbol id="phone" viewBox="0 0 24 24"><path d="M7 4 5 6c-1 1-.6 5.5 4.2 10.3C14 21.1 18.5 21.5 19.5 20.5l2-2-4-4-2 2c-1.3-.5-2.7-1.4-4-2.7s-2.2-2.7-2.7-4l2-2L7 4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>',
    mail: '<symbol id="mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>',
    pin: '<symbol id="pin" viewBox="0 0 24 24"><path d="M12 22s7-6.2 7-12A7 7 0 1 0 5 10c0 5.8 7 12 7 12Z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="2"/></symbol>',
    check: '<symbol id="check" viewBox="0 0 24 24"><path d="m5 12 4.5 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></symbol>',
    "social-vk": '<symbol id="social-vk" viewBox="0 0 24 24"><path d="M4.9 7.5c.1 5.8 3 9.2 8.1 9.2h.3v-3.3c1.8.2 3.1 1.5 3.6 3.3h2.7a7.1 7.1 0 0 0-3.6-4.8 6.9 6.9 0 0 0 3.1-4.4h-2.5c-.5 1.9-2 3.6-3.3 3.8V7.5h-2.5v6.7c-1.4-.3-3.1-2.2-3.2-6.7H4.9Z" fill="currentColor"/></symbol>',
    "social-ok": '<symbol id="social-ok" viewBox="0 0 24 24"><circle cx="12" cy="6.4" r="3.1" fill="currentColor"/><path d="M16 11.2a1.3 1.3 0 0 0-1.8-.1 3.6 3.6 0 0 1-4.4 0 1.3 1.3 0 0 0-1.8 1.8 6.1 6.1 0 0 0 2.2 1.2l-2 2.1a1.3 1.3 0 1 0 1.9 1.8l1.9-2 1.9 2a1.3 1.3 0 0 0 1.9-1.8l-2-2.1A6.1 6.1 0 0 0 16 13a1.3 1.3 0 0 0 0-1.8Z" fill="currentColor"/></symbol>',
    "social-max": '<symbol id="social-max" viewBox="0 0 64 64"><defs><linearGradient id="shell-max-logo-gradient" x1="5" y1="53" x2="59" y2="11" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#42c7f3"/><stop offset=".46" stop-color="#3153d6"/><stop offset="1" stop-color="#9b2ee8"/></linearGradient></defs><rect width="64" height="64" rx="15" fill="url(#shell-max-logo-gradient)"/><path d="M31.9 7.6c-12.7 0-23 9.9-23 22.2 0 4.3 1.1 8.5 2.2 12.6.8 3.1 1.7 6.3 1.7 9.5 0 3.6 2.3 4.9 5.5 4.9 3.5 0 7.9-1.9 11.2-5.4 1.5.8 3.5 1.5 6 1.9 11.1 1.8 22.2-6.2 22.2-18.6 0-13.6-11.5-27.1-25.8-27.1Zm-.3 12.1c8.2 0 15 6.5 15 14.2 0 7.2-6.5 12.7-13.4 12.7-4.1 0-6.9-1.2-9.3-3.1-1.4 1.6-3.1 3.5-5.3 3.9.2-4-2.6-10-2.6-14.8 0-7.2 6.8-12.9 15.6-12.9Z" fill="#fff"/></symbol>'
  };

  const ensureCommonSymbols = () => {
    const missingSymbols = Object.entries(commonSymbols)
      .filter(([id]) => !document.getElementById(id))
      .map(([, symbol]) => symbol);

    if (!missingSymbols.length) return;

    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("class", "svg-sprite shell-sprite");
    svg.setAttribute("aria-hidden", "true");
    svg.innerHTML = missingSymbols.join("");
    document.body.prepend(svg);
  };

  const accountHref = document.body.classList.contains("cabinet-page") ? "dashboard.html" : "login.html";

  const headerTemplate = `
    <header class="site-header auth-header shell-header">
      <div class="container header-inner auth-header-inner">
        <a class="brand" href="index.html" aria-label="Рука добра">
          <img class="brand-logo" src="assets/logo-ruka-dobra.svg" alt="НКО Рука добра" width="292" height="71">
        </a>

        <button class="menu-toggle" type="button" aria-label="Открыть меню" aria-expanded="false">
          <svg aria-hidden="true"><use href="#menu"></use></svg>
        </button>

        <nav class="main-nav auth-nav" aria-label="Основная навигация">
          <a href="index.html#about">О нас</a>
          <a href="feed.html">Обращения и жалобы</a>
          <a href="index.html#how">Как это работает</a>
          <a href="news.html">Новости</a>
          <a href="index.html#contacts">Контакты</a>
          <div class="mobile-nav-actions">
            <a class="header-cta auth-submit-link" href="appeal.html">
              <svg aria-hidden="true"><use href="#edit"></use></svg>
              Подать обращение / жалобу
            </a>
            <a class="header-login auth-account-link auth-account-link-mobile" href="${accountHref}">
              <svg aria-hidden="true"><use href="#user"></use></svg>
              Личный кабинет
            </a>
          </div>
        </nav>

        <div class="header-actions auth-header-actions">
          <a class="header-cta auth-submit-link" href="appeal.html">
            <svg aria-hidden="true"><use href="#edit"></use></svg>
            Подать обращение / жалобу
          </a>
          <a class="header-login auth-account-link" href="${accountHref}" aria-label="Личный кабинет">
            <svg aria-hidden="true"><use href="#user"></use></svg>
          </a>
        </div>
      </div>
    </header>
  `;

  const footerTemplate = `
    <footer class="site-footer shell-footer" id="contacts">
      <div class="container footer-grid">
        <div class="footer-about">
          <a class="brand footer-brand" href="index.html" aria-label="Рука добра">
            <img class="brand-logo" src="assets/logo-ruka-dobra.svg" alt="НКО Рука добра" width="238" height="58">
          </a>
          <p>Общественная помощь гражданам с обращениями и жалобами. Работаем ради справедливости и реальных изменений в России.</p>
          <div class="socials">
            <a class="social-vk" href="#" aria-label="ВКонтакте">
              <svg aria-hidden="true"><use href="#social-vk"></use></svg>
            </a>
            <a class="social-ok" href="#" aria-label="Одноклассники">
              <svg aria-hidden="true"><use href="#social-ok"></use></svg>
            </a>
            <a class="social-max" href="#" aria-label="MAX">
              <svg aria-hidden="true"><use href="#social-max"></use></svg>
            </a>
          </div>
        </div>

        <nav class="footer-col" aria-label="Навигация">
          <h3>Навигация</h3>
          <a href="index.html#about">О нас</a>
          <a href="feed.html">Обращения и жалобы</a>
          <a href="index.html#how">Как это работает</a>
          <a href="news.html">Новости</a>
          <a href="index.html#contacts">Контакты</a>
        </nav>

        <nav class="footer-col" aria-label="Категории">
          <h3>Категории</h3>
          <a href="appeal.html">ЖКХ</a>
          <a href="appeal.html">Дороги</a>
          <a href="appeal.html">Здравоохранение</a>
          <a href="appeal.html">Образование</a>
          <a href="categories.html">Все категории</a>
        </nav>

        <address class="footer-col footer-contacts">
          <h3>Контакты</h3>
          <a href="tel:+79102357746"><svg aria-hidden="true"><use href="#phone"></use></svg>8 910 235-77-46</a>
          <a href="mailto:info@rukadobra.ru"><svg aria-hidden="true"><use href="#mail"></use></svg>info@rukadobra.ru</a>
          <span><svg aria-hidden="true"><use href="#pin"></use></svg>г. Москва</span>
          <span><svg aria-hidden="true"><use href="#check"></use></svg>Ежедневно с 9:00 до 20:00</span>
        </address>

        <form class="subscribe" action="#" method="post">
          <h3>Подпишитесь на новости</h3>
          <p>Будьте в курсе важных новостей и результатов нашей работы.</p>
          <label>
            <span class="sr-only">Ваш e-mail</span>
            <input type="email" name="email" placeholder="Ваш e-mail">
          </label>
          <button type="submit">Подписаться</button>
        </form>
      </div>

      <div class="container footer-bottom">
        <span>© 2026 НКО «Рука добра». Все права защищены.</span>
        <a href="privacy.html">Политика конфиденциальности</a>
        <a href="agreement.html">Пользовательское соглашение</a>
      </div>
      <div class="footer-ribbon" aria-hidden="true"></div>
    </footer>
  `;

  const replaceDirectShell = (tagName, template, placement) => {
    const current = Array.from(document.body.children).find((element) => element.tagName === tagName);
    const fragment = document.createRange().createContextualFragment(template.trim());

    if (current) {
      current.replaceWith(fragment);
      return;
    }

    if (placement === "prepend") {
      document.body.prepend(fragment);
      return;
    }

    const script = document.querySelector('script[src="app.js"]');
    if (script) {
      script.before(fragment);
    } else {
      document.body.append(fragment);
    }
  };

  ensureCommonSymbols();
  replaceDirectShell("HEADER", headerTemplate, "prepend");

  const pageFooters = Array.from(document.body.children).filter((element) => element.tagName === "FOOTER");
  const footerFragment = document.createRange().createContextualFragment(footerTemplate.trim());
  if (pageFooters.length) {
    pageFooters[pageFooters.length - 1].replaceWith(footerFragment);
  } else {
    replaceDirectShell("FOOTER", footerTemplate, "append");
  }
})();

const menuToggle = document.querySelector(".menu-toggle");
const mainNav = document.querySelector(".main-nav");

const closeMenu = () => {
  if (!menuToggle || !mainNav) return;

  mainNav.classList.remove("is-open");
  document.body.classList.remove("menu-open");
  menuToggle.setAttribute("aria-expanded", "false");
};

if (menuToggle && mainNav) {
  menuToggle.addEventListener("click", () => {
    const isOpen = mainNav.classList.toggle("is-open");
    document.body.classList.toggle("menu-open", isOpen);
    menuToggle.setAttribute("aria-expanded", String(isOpen));
  });

  mainNav.addEventListener("click", (event) => {
    if (event.target.closest("a")) {
      closeMenu();
    }
  });

  document.addEventListener("click", (event) => {
    if (!document.body.classList.contains("menu-open")) return;
    if (event.target.closest(".main-nav, .menu-toggle")) return;

    closeMenu();
  });
}

const pressButton = (button) => {
  if (!button || !button.animate) return;

  button.animate(
    [
      { transform: "scale(1)" },
      { transform: "scale(0.94)" },
      { transform: "scale(1)" }
    ],
    { duration: 170, easing: "ease-out" }
  );
};

const initScrollTopButton = () => {
  if (document.querySelector("[data-scroll-top]")) return;

  const button = document.createElement("button");
  button.className = "scroll-top-button";
  button.type = "button";
  button.dataset.scrollTop = "";
  button.tabIndex = -1;
  button.setAttribute("aria-label", "\u041d\u0430\u0432\u0435\u0440\u0445");
  button.setAttribute("aria-hidden", "true");
  button.innerHTML = '<svg aria-hidden="true"><use href="#arrow-up"></use></svg>';
  document.body.append(button);

  let isTicking = false;

  const updateVisibility = () => {
    isTicking = false;
    const isVisible = window.scrollY > 420;

    button.classList.toggle("is-visible", isVisible);
    button.tabIndex = isVisible ? 0 : -1;
    button.setAttribute("aria-hidden", String(!isVisible));
  };

  const requestVisibilityUpdate = () => {
    if (isTicking) return;

    isTicking = true;
    window.requestAnimationFrame(updateVisibility);
  };

  button.addEventListener("click", () => {
    pressButton(button);

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    window.scrollTo({
      top: 0,
      behavior: prefersReducedMotion ? "auto" : "smooth"
    });
  });

  window.addEventListener("scroll", requestVisibilityUpdate, { passive: true });
  updateVisibility();
};

initScrollTopButton();

document.querySelectorAll("[data-password-toggle]").forEach((button) => {
  button.addEventListener("click", () => {
    const input = button.closest(".auth-input")?.querySelector("input");
    if (!input) return;

    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    button.setAttribute("aria-label", isPassword ? "Скрыть пароль" : "Показать пароль");
  });
});

const loginForm = document.querySelector(".auth-card-login .auth-form");

if (loginForm) {
  loginForm.addEventListener("submit", (event) => {
    event.preventDefault();
    window.location.href = "dashboard.html";
  });
}

const hero = document.querySelector(".hero");
const heroTitle = document.querySelector("[data-hero-title]");
const heroLead = document.querySelector("[data-hero-lead]");
const heroLabel = document.querySelector("[data-hero-label]");
const heroNote = document.querySelector("[data-hero-note]");
const heroDots = Array.from(document.querySelectorAll(".slider-dots button"));
const heroPrev = document.querySelector(".slider-prev");
const heroNext = document.querySelector(".slider-next");

const heroSlides = [
  {
    label: "Платформа обращений и жалоб граждан",
    title: "Вместе решаем проблемы людей и защищаем их права",
    lead: "Помогаем гражданам с обращениями и жалобами, добиваемся справедливости и поддержки, защищаем права и интересы людей.",
    note: "Лично доставляем жалобы депутатам Госдумы РФ, руководителям МВД, АП, губернаторам, сенаторам. С представителем правозащитников в суд.",
    image: "assets/hero-civic-flag.png"
  },
  {
    label: "Общественный контроль и поддержка",
    title: "Помогаем обращениям получить официальный ход",
    lead: "Фиксируем проблему, готовим документы и сопровождаем обращение до понятного статуса и результата.",
    note: "Проверяем факты, собираем подтверждения и направляем жалобы в профильные ведомства и общественные приёмные.",
    image: "assets/hero-legal-consultation.png"
  },
  {
    label: "Прозрачный реестр обращений",
    title: "Показываем ход работы и результаты решений",
    lead: "Публикуем проверенные обращения, статусы и важные обновления, чтобы граждане видели движение по проблеме.",
    note: "Каждое обращение проходит модерацию, получает категорию и становится частью открытой системы общественного контроля.",
    image: "assets/hero-community-meeting.png"
  }
];

let currentHeroSlide = 0;

const setHeroSlide = (index) => {
  if (!hero || !heroTitle || !heroLead || !heroLabel || !heroNote) return;

  currentHeroSlide = (index + heroSlides.length) % heroSlides.length;
  const slide = heroSlides[currentHeroSlide];

  hero.classList.remove("is-changing");
  void hero.offsetWidth;
  hero.classList.add("is-changing");
  hero.style.setProperty("--hero-image", `url("${slide.image}")`);
  heroLabel.textContent = slide.label;
  heroTitle.textContent = slide.title;
  heroLead.textContent = slide.lead;
  heroNote.textContent = slide.note;

  heroDots.forEach((dot, dotIndex) => {
    const isActive = dotIndex === currentHeroSlide;
    dot.classList.toggle("active", isActive);
    dot.setAttribute("aria-current", isActive ? "true" : "false");
  });
};

if (hero && heroSlides.length) {
  if (heroPrev) {
    heroPrev.addEventListener("click", () => {
      pressButton(heroPrev);
      setHeroSlide(currentHeroSlide - 1);
    });
  }

  if (heroNext) {
    heroNext.addEventListener("click", () => {
      pressButton(heroNext);
      setHeroSlide(currentHeroSlide + 1);
    });
  }

  heroDots.forEach((dot, index) => {
    dot.addEventListener("click", () => {
      pressButton(dot);
      setHeroSlide(index);
    });
  });
}

const adRotator = document.querySelector("[data-ad-rotator]");
const adImage = adRotator ? adRotator.querySelector("[data-ad-image]") : null;
const adTargetUrl = "https://contract.gosuslugi.ru/";
const adBanners = [
  {
    src: "assets/114a584a-17bb-4ecb-95fd-c338df16704e.png",
    alt: "Поступай на контрактную службу в Вооруженные силы России"
  },
  {
    src: "assets/6c216b42-b479-4748-a9d2-e67cfe37976d.png",
    alt: "Время служить Родине, контрактная служба в Вооруженных силах Российской Федерации"
  },
  {
    src: "assets/ec8b05b4-0bb3-4ce5-bdaf-6a964aeb2438.png",
    alt: "Служи России, защищай Родину"
  }
];
let currentAdBanner = 0;

const getRandomAdBannerIndex = () => {
  if (adBanners.length <= 1) return 0;

  let nextIndex = currentAdBanner;
  while (nextIndex === currentAdBanner) {
    nextIndex = Math.floor(Math.random() * adBanners.length);
  }

  return nextIndex;
};

const setAdBanner = (index) => {
  if (!adRotator || !adImage || !adBanners.length) return;

  currentAdBanner = (index + adBanners.length) % adBanners.length;
  const banner = adBanners[currentAdBanner];

  adRotator.href = adTargetUrl;
  adRotator.setAttribute("aria-label", `${banner.alt}. Откроется в новом окне`);
  adRotator.classList.remove("is-changing");
  void adRotator.offsetWidth;
  adRotator.classList.add("is-changing");
  adImage.src = banner.src;
  adImage.alt = banner.alt;
};

if (adRotator && adImage && adBanners.length) {
  adBanners.forEach((banner) => {
    const preloadImage = new Image();
    preloadImage.src = banner.src;
  });

  setAdBanner(Math.floor(Math.random() * adBanners.length));

  window.setInterval(() => {
    setAdBanner(getRandomAdBannerIndex());
  }, 15000);

  adRotator.addEventListener("click", (event) => {
    const shouldOpen = window.confirm("Вы действительно хотите перейти на сайт рекламодателя?");
    if (!shouldOpen) {
      event.preventDefault();
    }
  });
}

const appealCarousel = document.querySelector("[data-appeal-carousel]");
const appealTrack = appealCarousel ? appealCarousel.querySelector("[data-appeal-track]") : null;
const appealCards = appealTrack ? Array.from(appealTrack.querySelectorAll(".appeal-card")) : [];
const appealPrev = document.querySelector("[data-appeal-prev]");
const appealNext = document.querySelector("[data-appeal-next]");
const appealDots = document.querySelector("[data-appeal-dots]");
let appealPage = 0;

const getAppealStep = () => {
  if (appealCards.length > 1) {
    return appealCards[1].offsetLeft - appealCards[0].offsetLeft;
  }

  return appealCards[0] ? appealCards[0].getBoundingClientRect().width : 0;
};

const getAppealVisibleCount = () => {
  const step = getAppealStep();
  if (!appealTrack || !step) return 1;

  return Math.max(1, Math.round(appealTrack.clientWidth / step));
};

const getAppealPageCount = () => {
  if (!appealCards.length) return 1;

  return Math.max(1, Math.ceil(appealCards.length / getAppealVisibleCount()));
};

const scrollAppealToPage = (page, behavior = "smooth") => {
  if (!appealTrack || !appealCards.length) return;

  const visibleCount = getAppealVisibleCount();
  const pageCount = getAppealPageCount();
  appealPage = Math.max(0, Math.min(page, pageCount - 1));
  const targetIndex = Math.min(appealPage * visibleCount, appealCards.length - 1);

  appealTrack.scrollTo({
    left: appealCards[targetIndex].offsetLeft - appealCards[0].offsetLeft,
    behavior
  });
};

const syncAppealCarousel = () => {
  if (!appealTrack || !appealCards.length) return;

  const step = getAppealStep();
  const visibleCount = getAppealVisibleCount();
  const pageCount = getAppealPageCount();
  const activeIndex = step ? Math.round(appealTrack.scrollLeft / step) : 0;
  appealPage = Math.max(0, Math.min(Math.round(activeIndex / visibleCount), pageCount - 1));
  const atStart = appealTrack.scrollLeft <= 2;
  const atEnd = appealTrack.scrollLeft + appealTrack.clientWidth >= appealTrack.scrollWidth - 2;

  if (appealPrev) {
    appealPrev.disabled = pageCount <= 1 || atStart;
  }

  if (appealNext) {
    appealNext.disabled = pageCount <= 1 || atEnd;
  }

  if (appealDots) {
    Array.from(appealDots.children).forEach((dot, index) => {
      const isActive = index === appealPage;
      dot.classList.toggle("active", isActive);
      dot.setAttribute("aria-current", isActive ? "true" : "false");
    });
  }
};

const renderAppealDots = () => {
  if (!appealDots) return;

  const pageCount = getAppealPageCount();
  appealDots.replaceChildren();

  if (pageCount <= 1) {
    syncAppealCarousel();
    return;
  }

  for (let index = 0; index < pageCount; index += 1) {
    const dot = document.createElement("button");
    dot.type = "button";
    dot.setAttribute("aria-label", `Страница ленты ${index + 1}`);
    dot.addEventListener("click", () => {
      pressButton(dot);
      scrollAppealToPage(index);
    });
    appealDots.appendChild(dot);
  }

  syncAppealCarousel();
};

if (appealTrack && appealCards.length) {
  if (appealPrev) {
    appealPrev.addEventListener("click", () => {
      pressButton(appealPrev);
      scrollAppealToPage(appealPage - 1);
    });
  }

  if (appealNext) {
    appealNext.addEventListener("click", () => {
      pressButton(appealNext);
      scrollAppealToPage(appealPage + 1);
    });
  }

  appealTrack.addEventListener("scroll", () => {
    window.requestAnimationFrame(syncAppealCarousel);
  }, { passive: true });

  appealTrack.addEventListener("keydown", (event) => {
    if (event.key === "ArrowLeft") {
      event.preventDefault();
      scrollAppealToPage(appealPage - 1);
    }

    if (event.key === "ArrowRight") {
      event.preventDefault();
      scrollAppealToPage(appealPage + 1);
    }
  });

  if ("ResizeObserver" in window) {
    const appealResizeObserver = new ResizeObserver(() => {
      renderAppealDots();
      scrollAppealToPage(appealPage, "auto");
    });
    appealResizeObserver.observe(appealTrack);
  } else {
    window.addEventListener("resize", renderAppealDots);
  }

  renderAppealDots();
}

const shareModal = document.querySelector("[data-share-modal]");
const shareModalPanel = shareModal ? shareModal.querySelector(".modal-panel") : null;
const shareModalText = shareModal ? shareModal.querySelector("[data-share-modal-text]") : null;
const copyLinkButton = shareModal ? shareModal.querySelector("[data-copy-link]") : null;
const copyLinkLabel = shareModal ? shareModal.querySelector("[data-copy-label]") : null;
let shareModalReturnFocus = null;
let currentShareLink = "";

const closeShareModal = () => {
  if (!shareModal) return;

  shareModal.hidden = true;
  document.body.classList.remove("modal-open");
  if (shareModalReturnFocus) {
    shareModalReturnFocus.focus();
  }
  shareModalReturnFocus = null;
};

const openShareModalByTitle = (title, link, trigger) => {
  if (!shareModal || !shareModalText) return;

  currentShareLink = link || window.location.href;
  shareModalText.textContent = `Ссылка на обращение «${title || "обращение"}» готова к отправке.`;
  shareModalReturnFocus = trigger || document.activeElement;
  shareModal.hidden = false;
  document.body.classList.add("modal-open");
  if (shareModalPanel) {
    shareModalPanel.focus();
  }
};

const openShareModal = (card, trigger) => {
  const titleNode = card ? card.querySelector("h3") : null;
  const title = titleNode && titleNode.textContent ? titleNode.textContent.trim() : "обращение";
  const linkNode = card ? card.querySelector("a[href]") : null;
  const link = linkNode ? new URL(linkNode.getAttribute("href"), window.location.href).href : `${window.location.href.split("#")[0]}#news`;
  openShareModalByTitle(title, link, trigger);
};

const copyText = async (text) => {
  if (navigator.clipboard && window.isSecureContext) {
    await navigator.clipboard.writeText(text);
    return;
  }

  const input = document.createElement("textarea");
  input.value = text;
  input.setAttribute("readonly", "");
  input.style.position = "fixed";
  input.style.left = "-9999px";
  document.body.appendChild(input);
  input.select();
  document.execCommand("copy");
  document.body.removeChild(input);
};

document.querySelectorAll(".appeal-card footer button").forEach((button) => {
  button.addEventListener("click", () => {
    openShareModal(button.closest(".appeal-card"), button);
  });
});

document.querySelectorAll("[data-share-appeal]").forEach((button) => {
  button.addEventListener("click", () => {
    const titleNode = document.querySelector("[data-share-title]");
    const title = titleNode && titleNode.textContent ? titleNode.textContent.trim() : "обращение";
    openShareModalByTitle(title, window.location.href.split("#")[0], button);
  });
});

if (shareModal) {
  shareModal.querySelectorAll("[data-modal-close]").forEach((button) => {
    button.addEventListener("click", closeShareModal);
  });
}

if (copyLinkButton) {
  copyLinkButton.addEventListener("click", async () => {
    if (!copyLinkLabel) return;

    const defaultText = copyLinkLabel.textContent;

    try {
      await copyText(currentShareLink || window.location.href);
      copyLinkLabel.textContent = "Ссылка скопирована";
    } catch {
      copyLinkLabel.textContent = "Не удалось скопировать";
    }

    window.setTimeout(() => {
      copyLinkLabel.textContent = defaultText;
    }, 1600);
  });
}

const videoModal = document.querySelector("[data-video-modal]");
const videoModalPanel = videoModal ? videoModal.querySelector(".modal-panel") : null;
const videoForm = videoModal ? videoModal.querySelector("[data-video-form]") : null;
const videoFileInput = videoModal ? videoModal.querySelector("[data-video-file]") : null;
const videoFileName = videoModal ? videoModal.querySelector("[data-video-file-name]") : null;
const videoStatus = videoModal ? videoModal.querySelector("[data-video-status]") : null;
const videoSubmitLabel = videoModal ? videoModal.querySelector("[data-video-submit-label]") : null;
const videoFileDefaultText = videoFileName ? videoFileName.textContent : "";
const videoSubmitDefaultText = videoSubmitLabel ? videoSubmitLabel.textContent : "";
const maxVideoFileSize = 100 * 1024 * 1024;
let videoModalReturnFocus = null;

const formatVideoFileSize = (bytes) => {
  if (!Number.isFinite(bytes) || bytes <= 0) return "0 МБ";
  return `${(bytes / (1024 * 1024)).toFixed(1).replace(".", ",")} МБ`;
};

const resetVideoFileState = () => {
  if (videoFileName) {
    videoFileName.textContent = videoFileDefaultText;
  }

  if (videoFileInput) {
    videoFileInput.setCustomValidity("");
  }
};

const closeVideoModal = () => {
  if (!videoModal) return;

  videoModal.hidden = true;
  document.body.classList.remove("modal-open");
  if (videoModalReturnFocus) {
    videoModalReturnFocus.focus();
  }
  videoModalReturnFocus = null;
};

const openVideoModal = (trigger) => {
  if (!videoModal) return;

  videoModalReturnFocus = trigger || document.activeElement;
  videoModal.hidden = false;
  document.body.classList.add("modal-open");

  if (videoStatus) {
    videoStatus.hidden = true;
    videoStatus.textContent = "";
  }

  const firstField = videoModal.querySelector(".video-submit-form input:not([type='file']), .video-submit-form textarea, .video-submit-form button");
  if (firstField) {
    firstField.focus();
  } else if (videoModalPanel) {
    videoModalPanel.focus();
  }
};

document.querySelectorAll("[data-video-modal-open]").forEach((button) => {
  button.addEventListener("click", (event) => {
    event.preventDefault();
    openVideoModal(button);
  });
});

if (videoModal) {
  videoModal.querySelectorAll("[data-video-modal-close]").forEach((button) => {
    button.addEventListener("click", closeVideoModal);
  });
}

if (videoFileInput && videoFileName) {
  videoFileInput.addEventListener("change", () => {
    const file = videoFileInput.files && videoFileInput.files[0];

    if (!file) {
      resetVideoFileState();
      return;
    }

    const isTooLarge = file.size > maxVideoFileSize;
    videoFileName.textContent = `${file.name} • ${formatVideoFileSize(file.size)}${isTooLarge ? " • больше 100 МБ" : ""}`;
    videoFileInput.setCustomValidity(isTooLarge ? "Выберите видео до 100 МБ." : "");
  });
}

if (videoForm) {
  videoForm.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!videoForm.reportValidity()) return;

    videoForm.reset();
    resetVideoFileState();

    if (videoStatus) {
      videoStatus.textContent = "Спасибо, видео отправлено на модерацию. Мы свяжемся с вами после проверки.";
      videoStatus.hidden = false;
    }

    if (videoSubmitLabel) {
      videoSubmitLabel.textContent = "Отправлено";
      window.setTimeout(() => {
        videoSubmitLabel.textContent = videoSubmitDefaultText;
      }, 1800);
    }
  });
}

const appealCommentForm = document.querySelector("[data-comment-form]");
const appealCommentInput = appealCommentForm ? appealCommentForm.querySelector("[data-comment-input]") : null;
const appealCommentCounter = appealCommentForm ? appealCommentForm.querySelector("[data-comment-counter]") : null;
const appealCommentSubmit = appealCommentForm ? appealCommentForm.querySelector('button[type="submit"]') : null;
const appealCommentList = document.querySelector("[data-comment-list]");
const appealCommentFilterButtons = Array.from(document.querySelectorAll("[data-comment-filter]"));
const appealCommentCountNodes = Array.from(document.querySelectorAll("[data-comments-count]"));
const appealCommentWordNodes = Array.from(document.querySelectorAll("[data-comments-word]"));

const getCommentWord = (count) => {
  const mod10 = count % 10;
  const mod100 = count % 100;

  if (mod10 === 1 && mod100 !== 11) return "комментарий";
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "комментария";
  return "комментариев";
};

const getCommentDateLabel = (date) => {
  const months = [
    "января",
    "февраля",
    "марта",
    "апреля",
    "мая",
    "июня",
    "июля",
    "августа",
    "сентября",
    "октября",
    "ноября",
    "декабря"
  ];
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");

  return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}, ${hours}:${minutes}`;
};

const syncCommentCount = (count) => {
  appealCommentCountNodes.forEach((node) => {
    node.textContent = String(count);
  });

  appealCommentWordNodes.forEach((node) => {
    node.textContent = getCommentWord(count);
  });
};

const updateCommentCounter = () => {
  if (!appealCommentInput || !appealCommentCounter) return;

  const length = appealCommentInput.value.length;
  appealCommentCounter.textContent = String(length);

  if (appealCommentSubmit) {
    appealCommentSubmit.disabled = !appealCommentInput.value.trim();
  }
};

const setAppealCommentFilter = (filter) => {
  appealCommentFilterButtons.forEach((button) => {
    button.classList.toggle("is-active", button.dataset.commentFilter === filter);
  });

  if (!appealCommentList) return;

  appealCommentList.querySelectorAll(".appeal-comment").forEach((comment) => {
    const isOfficial = comment.dataset.commentType === "official";
    const hasMedia = comment.dataset.commentMedia === "true";
    const isVisible = filter === "all" || (filter === "official" && isOfficial) || (filter === "media" && hasMedia);
    comment.hidden = !isVisible;
  });
};

document.querySelectorAll("[data-comment-focus]").forEach((button) => {
  button.addEventListener("click", () => {
    if (!appealCommentForm || !appealCommentInput) return;

    pressButton(button);
    appealCommentForm.scrollIntoView({ block: "center", behavior: "smooth" });
    appealCommentInput.focus();
  });
});

appealCommentFilterButtons.forEach((button) => {
  button.addEventListener("click", () => {
    pressButton(button);
    setAppealCommentFilter(button.dataset.commentFilter || "all");
  });
});

if (appealCommentForm && appealCommentInput && appealCommentList) {
  let appealCommentCount = Number((appealCommentCountNodes[0]?.textContent || "").replace(/\D/g, "")) || 0;

  updateCommentCounter();
  syncCommentCount(appealCommentCount);
  setAppealCommentFilter("all");

  appealCommentInput.addEventListener("input", updateCommentCounter);

  appealCommentForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const text = appealCommentInput.value.trim();
    if (!text) {
      appealCommentInput.focus();
      return;
    }

    if (appealCommentSubmit) pressButton(appealCommentSubmit);

    const now = new Date();
    const article = document.createElement("article");
    article.className = "appeal-comment is-new";
    article.dataset.commentType = "public";

    const avatar = document.createElement("div");
    avatar.className = "appeal-detail-avatar";
    avatar.setAttribute("aria-hidden", "true");
    avatar.textContent = "ВЫ";

    const body = document.createElement("div");
    body.className = "appeal-comment-body";

    const meta = document.createElement("div");
    meta.className = "appeal-comment-meta";

    const person = document.createElement("div");
    const title = document.createElement("h3");
    title.textContent = "Ваш комментарий";
    const badge = document.createElement("span");
    badge.className = "cabinet-pill blue";
    badge.textContent = "На проверке";
    person.append(title, badge);

    const time = document.createElement("time");
    time.dateTime = now.toISOString();
    time.textContent = getCommentDateLabel(now);

    const copy = document.createElement("p");
    copy.textContent = text;

    const actions = document.createElement("footer");
    actions.className = "appeal-comment-actions";
    const replyButton = document.createElement("button");
    replyButton.type = "button";
    replyButton.textContent = "Ответить";
    const supportButton = document.createElement("button");
    supportButton.type = "button";
    supportButton.textContent = "Поддержать";
    actions.append(replyButton, supportButton);

    meta.append(person, time);
    body.append(meta, copy, actions);
    article.append(avatar, body);
    appealCommentList.prepend(article);
    setAppealCommentFilter("all");

    appealCommentInput.value = "";
    updateCommentCounter();

    appealCommentCount += 1;
    syncCommentCount(appealCommentCount);

    if (article.animate) {
      article.animate(
        [
          { opacity: 0, transform: "translateY(-8px)" },
          { opacity: 1, transform: "translateY(0)" }
        ],
        { duration: 180, easing: "ease-out" }
      );
    }
  });
}

const feedPage = document.querySelector("[data-feed-page]");

const getPaginationPages = (currentPage, pageCount) => {
  if (pageCount <= 7) {
    return Array.from({ length: pageCount }, (_, index) => index + 1);
  }

  const pages = [1];
  const start = Math.max(2, currentPage - 1);
  const end = Math.min(pageCount - 1, currentPage + 1);

  if (start > 2) {
    pages.push("...");
  }

  for (let page = start; page <= end; page += 1) {
    pages.push(page);
  }

  if (end < pageCount - 1) {
    pages.push("...");
  }

  pages.push(pageCount);
  return pages;
};

const renderListPagination = ({
  container,
  currentPage,
  pageCount,
  totalCount,
  startIndex,
  endIndex,
  onPageChange
}) => {
  if (!container) return;

  container.replaceChildren();
  container.hidden = pageCount <= 1 || totalCount <= 0;

  if (container.hidden) return;

  const status = document.createElement("span");
  status.className = "list-pagination-status";
  status.setAttribute("aria-live", "polite");
  status.textContent = `Показано ${startIndex}-${endIndex} из ${totalCount}`;

  const controls = document.createElement("div");
  controls.className = "list-pagination-controls";

  const createButton = (label, page, options = {}) => {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = label;
    button.disabled = Boolean(options.disabled);

    if (options.className) {
      button.className = options.className;
    }

    if (options.current) {
      button.classList.add("is-active");
      button.setAttribute("aria-current", "page");
    }

    if (options.ariaLabel) {
      button.setAttribute("aria-label", options.ariaLabel);
    }

    if (!button.disabled) {
      button.addEventListener("click", () => {
        pressButton(button);
        onPageChange(page);
      });
    }

    return button;
  };

  controls.append(
    createButton("Назад", currentPage - 1, {
      className: "pagination-step",
      disabled: currentPage === 1,
      ariaLabel: "Предыдущая страница"
    })
  );

  getPaginationPages(currentPage, pageCount).forEach((entry) => {
    if (entry === "...") {
      const spacer = document.createElement("span");
      spacer.className = "list-pagination-ellipsis";
      spacer.textContent = "...";
      controls.append(spacer);
      return;
    }

    controls.append(
      createButton(String(entry), entry, {
        current: entry === currentPage,
        ariaLabel: `Страница ${entry}`
      })
    );
  });

  controls.append(
    createButton("Вперед", currentPage + 1, {
      className: "pagination-step",
      disabled: currentPage === pageCount,
      ariaLabel: "Следующая страница"
    })
  );

  container.append(status, controls);
};

if (feedPage) {
  const feedList = feedPage.querySelector("[data-feed-list]");
  const feedCards = feedList ? Array.from(feedList.querySelectorAll("[data-feed-card]")) : [];
  const feedSearch = feedPage.querySelector("[data-feed-search]");
  const feedStatusButtons = Array.from(feedPage.querySelectorAll("[data-feed-filter]"));
  const feedCitySelect = feedPage.querySelector('[data-feed-select="city"]');
  const feedCategorySelect = feedPage.querySelector('[data-feed-select="category"]');
  const feedSortSelect = feedPage.querySelector("[data-feed-sort]");
  const feedCount = feedPage.querySelector("[data-feed-count]");
  const feedEmpty = feedPage.querySelector("[data-feed-empty]");
  const feedToolbar = feedPage.querySelector("[data-feed-toolbar]");
  const feedPagination = feedPage.querySelector('[data-pagination="feed"]');
  const feedPageSize = 6;
  let activeFeedStatus = "all";
  let currentFeedPage = 1;

  const normalizeFeedText = (value) => String(value || "").toLocaleLowerCase("ru-RU").trim();

  const getAppealWord = (count) => {
    const mod10 = count % 10;
    const mod100 = count % 100;

    if (mod10 === 1 && mod100 !== 11) return "обращение";
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) return "обращения";
    return "обращений";
  };

  const sortFeedCards = () => {
    if (!feedList) return [...feedCards];

    const sortMode = feedSortSelect ? feedSortSelect.value : "new";
    const sortedCards = [...feedCards].sort((first, second) => {
      if (sortMode === "support") {
        return Number(second.dataset.support || 0) - Number(first.dataset.support || 0);
      }

      if (sortMode === "views") {
        return Number(second.dataset.views || 0) - Number(first.dataset.views || 0);
      }

      return new Date(second.dataset.date || 0).getTime() - new Date(first.dataset.date || 0).getTime();
    });

    sortedCards.forEach((card) => feedList.appendChild(card));
    return sortedCards;
  };

  const renderFeed = (options = {}) => {
    const query = normalizeFeedText(feedSearch ? feedSearch.value : "");
    const city = feedCitySelect ? feedCitySelect.value : "all";
    const category = feedCategorySelect ? feedCategorySelect.value : "all";
    const matchedCards = [];

    const orderedCards = sortFeedCards();

    orderedCards.forEach((card) => {
      const searchableText = normalizeFeedText([
        card.dataset.title,
        card.dataset.city,
        card.dataset.category,
        card.textContent
      ].join(" "));
      const matchesStatus = activeFeedStatus === "all" || card.dataset.status === activeFeedStatus;
      const matchesCity = city === "all" || card.dataset.city === city;
      const matchesCategory = category === "all" || card.dataset.category === category;
      const matchesSearch = !query || searchableText.includes(query);
      const isVisible = matchesStatus && matchesCity && matchesCategory && matchesSearch;

      if (isVisible) {
        matchedCards.push(card);
      }
    });

    if (options.resetPage) {
      currentFeedPage = 1;
    }

    const visibleCount = matchedCards.length;
    const pageCount = Math.max(1, Math.ceil(visibleCount / feedPageSize));
    currentFeedPage = Math.max(1, Math.min(currentFeedPage, pageCount));

    const pageStart = (currentFeedPage - 1) * feedPageSize;
    const pageEnd = pageStart + feedPageSize;

    feedCards.forEach((card) => {
      card.hidden = true;
    });

    matchedCards.forEach((card, index) => {
      card.hidden = index < pageStart || index >= pageEnd;
    });

    if (feedCount) {
      feedCount.textContent = `${visibleCount} ${getAppealWord(visibleCount)}`;
    }

    if (feedEmpty) {
      feedEmpty.hidden = visibleCount > 0;
    }

    renderListPagination({
      container: feedPagination,
      currentPage: currentFeedPage,
      pageCount,
      totalCount: visibleCount,
      startIndex: visibleCount ? pageStart + 1 : 0,
      endIndex: Math.min(pageEnd, visibleCount),
      onPageChange: (page) => {
        currentFeedPage = page;
        renderFeed();
        feedList?.scrollIntoView({ block: "start", behavior: "smooth" });
      }
    });
  };

  feedStatusButtons.forEach((button) => {
    button.addEventListener("click", () => {
      activeFeedStatus = button.dataset.feedFilter || "all";
      feedStatusButtons.forEach((item) => {
        const isActive = item === button;
        item.classList.toggle("is-active", isActive);
        item.setAttribute("aria-pressed", String(isActive));
      });
      pressButton(button);
      renderFeed({ resetPage: true });
    });
  });

  [feedSearch, feedCitySelect, feedCategorySelect, feedSortSelect].forEach((field) => {
    if (!field) return;
    field.addEventListener("input", () => renderFeed({ resetPage: true }));
    field.addEventListener("change", () => renderFeed({ resetPage: true }));
  });

  if (feedToolbar) {
    feedToolbar.addEventListener("submit", (event) => {
      event.preventDefault();
      renderFeed({ resetPage: true });
    });
  }

  renderFeed();
}

const newsGrid = document.querySelector(".news-grid");

if (newsGrid) {
  const newsCards = Array.from(newsGrid.querySelectorAll(".news-list-card"));
  const newsPagination = document.querySelector('[data-pagination="news"]');
  const newsPageSize = 3;
  let currentNewsPage = 1;

  const renderNewsPage = () => {
    const totalCount = newsCards.length;
    const pageCount = Math.max(1, Math.ceil(totalCount / newsPageSize));
    currentNewsPage = Math.max(1, Math.min(currentNewsPage, pageCount));

    const pageStart = (currentNewsPage - 1) * newsPageSize;
    const pageEnd = pageStart + newsPageSize;

    newsCards.forEach((card, index) => {
      card.hidden = index < pageStart || index >= pageEnd;
    });

    renderListPagination({
      container: newsPagination,
      currentPage: currentNewsPage,
      pageCount,
      totalCount,
      startIndex: totalCount ? pageStart + 1 : 0,
      endIndex: Math.min(pageEnd, totalCount),
      onPageChange: (page) => {
        currentNewsPage = page;
        renderNewsPage();
        newsGrid.scrollIntoView({ block: "start", behavior: "smooth" });
      }
    });
  };

  renderNewsPage();
}

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeMenu();
    closeShareModal();
    closeVideoModal();
  }
});

const appealWizardPage = document.querySelector("[data-appeal-page]");

if (appealWizardPage) {
  const wizardScreen = appealWizardPage.querySelector("[data-wizard-screen]");
  const wizardPanels = Array.from(appealWizardPage.querySelectorAll("[data-appeal-step]"));
  const progressItems = Array.from(appealWizardPage.querySelectorAll("[data-progress-step]"));
  const resultScreens = Array.from(appealWizardPage.querySelectorAll("[data-result-screen]"));
  const sideImage = appealWizardPage.querySelector("[data-side-image]");
  const sideTitle = appealWizardPage.querySelector("[data-side-title]");
  const sideText = appealWizardPage.querySelector("[data-side-text]");
  const sideBenefits = appealWizardPage.querySelector("[data-side-benefits]");

  const sideContent = {
    1: {
      image: "assets/verification-illustration.png",
      title: "Ваш голос важен для нашего города и страны",
      text: "Мы работаем для того, чтобы решить проблемы граждан и сделать жизнь лучше. Каждое обращение получает внимание и контроль.",
      items: [
        ["shield", "Защита ваших прав", "Мы поможем защитить ваши законные права и интересы."],
        ["file", "Контроль и результат", "Каждое обращение рассматривается, а результаты доступны вам."],
        ["users", "Вместе мы сильнее", "Объединяя усилия, мы делаем города безопаснее."]
      ]
    },
    2: {
      image: "assets/verification-illustration.png",
      title: "Ваша проблема важна для нас",
      text: "Чем подробнее вы опишете ситуацию, тем быстрее мы сможем её понять и предложить решение.",
      items: [
        ["shield", "Быстрое рассмотрение", "Подробное описание помогает нам быстрее определить суть проблемы."],
        ["users", "Эффективное решение", "Точные детали позволяют подобрать наиболее подходящее решение."],
        ["check", "Реальные изменения", "Ваши обращения помогают делать город и страну лучше."]
      ]
    },
    3: {
      image: "assets/welcome-illustration.png",
      title: "Доказательства помогают ускорить решение проблемы",
      text: "Чем больше подтверждающих материалов, тем быстрее мы сможем разобраться в ситуации и повлиять на её решение.",
      items: [
        ["image", "Фото и видео", "Показывают проблему на месте и помогают оценить масштаб."],
        ["file", "Документы", "Заявления, ответы, квитанции и другие бумаги усиливают обращение."],
        ["shield", "Проверка и модерация", "Все материалы проверяются перед публикацией."]
      ]
    },
    4: {
      image: "assets/verification-illustration.png",
      title: "Точность имеет значение",
      text: "Чем точнее указано место, тем быстрее мы сможем направить обращение в компетентный орган.",
      items: [
        ["pin", "Точное место", "Адрес помогает проверить факты и найти ответственную организацию."],
        ["lock", "Данные под защитой", "Мы защищаем вашу конфиденциальность."],
        ["users", "Вместе мы сильнее", "Объединяя усилия, мы делаем города лучше."]
      ]
    },
    5: {
      image: "assets/welcome-illustration.png",
      title: "Ваши данные защищены и используются только для помощи",
      text: "Мы гарантируем конфиденциальность ваших данных и используем их исключительно для рассмотрения обращения.",
      items: [
        ["shield", "Защита и конфиденциальность", "Ваши данные шифруются и не передаются третьим лицам."],
        ["file", "Определяем и решаем быстрее", "С контактами мы можем уточнить детали и быстрее найти решение."],
        ["users", "История обращений", "Все обращения и ответы сохраняются в одном месте."]
      ]
    },
    6: {
      image: "assets/verification-illustration.png",
      title: "Ваш голос важен для нашего города и страны",
      text: "Мы защищаем платформу от спама и автоматических отправок, чтобы каждое обращение было реальным и получало внимание.",
      items: [
        ["shield", "Защита от спама и ботов", "SmartCaptcha помогает фильтровать автоматические отправки."],
        ["lock", "Безопасность ваших данных", "Мы не передаём ваши данные третьим лицам."],
        ["users", "Реальные обращения", "Ваше сообщение увидят живые люди и примут меры."]
      ]
    },
    7: {
      image: "assets/verification-illustration.png",
      title: "Что будет дальше",
      text: "После отправки обращение пройдёт модерацию, затем будет опубликовано или направлено ответственным органам.",
      items: [
        ["send", "Отправка на модерацию", "Модератор проверит обращение на соответствие правилам платформы."],
        ["search", "Проверка и публикация", "После проверки обращение может быть опубликовано на платформе."],
        ["bell", "Уведомления", "О всех изменениях статуса вы получите уведомления."]
      ]
    }
  };

  let currentAppealStep = 1;

  const renderSideContent = (step) => {
    const content = sideContent[step] || sideContent[1];
    if (sideImage) sideImage.src = content.image;
    if (sideTitle) sideTitle.textContent = content.title;
    if (sideText) sideText.textContent = content.text;
    if (!sideBenefits) return;

    sideBenefits.innerHTML = content.items.map(([icon, title, text]) => (
      `<article><span><svg aria-hidden="true"><use href="#${icon}"></use></svg></span><div><strong>${title}</strong><p>${text}</p></div></article>`
    )).join("");
  };

  const syncAppealHash = (hash) => {
    if (window.location.hash === hash) return;
    history.pushState(null, "", hash);
  };

  const scrollAppealTop = () => {
    const target = appealWizardPage.querySelector(".appeal-frame:not([hidden]), .appeal-result-screen:not([hidden])");
    if (!target) return;
    target.scrollIntoView({ block: "start", behavior: "smooth" });
  };

  const showAppealStep = (step, options = {}) => {
    const nextStep = Math.max(1, Math.min(Number(step) || 1, wizardPanels.length || 1));
    currentAppealStep = nextStep;

    if (wizardScreen) wizardScreen.hidden = false;
    resultScreens.forEach((screen) => {
      screen.hidden = true;
    });

    wizardPanels.forEach((panel) => {
      panel.hidden = Number(panel.dataset.appealStep) !== nextStep;
    });

    progressItems.forEach((item) => {
      const itemStep = Number(item.dataset.progressStep);
      item.classList.toggle("is-active", itemStep === nextStep);
      item.classList.toggle("is-done", itemStep < nextStep);
    });

    renderSideContent(nextStep);

    if (options.hash !== false) {
      syncAppealHash(`#step-${nextStep}`);
    }

    if (options.scroll !== false) {
      scrollAppealTop();
    }
  };

  const showAppealResult = (name, options = {}) => {
    const targetScreen = resultScreens.find((screen) => screen.dataset.resultScreen === name) || resultScreens[0];
    if (!targetScreen) return;

    if (wizardScreen) wizardScreen.hidden = true;
    resultScreens.forEach((screen) => {
      screen.hidden = screen !== targetScreen;
    });

    if (options.hash !== false) {
      syncAppealHash(`#${targetScreen.dataset.resultScreen}`);
    }

    if (options.scroll !== false) {
      scrollAppealTop();
    }
  };

  const renderAppealRoute = (options = {}) => {
    const hash = window.location.hash.replace("#", "");
    const stepMatch = hash.match(/^step-(\d+)$/);

    if (stepMatch) {
      showAppealStep(Number(stepMatch[1]), { ...options, hash: false });
      return;
    }

    if (["sent", "moderation", "published"].includes(hash)) {
      showAppealResult(hash, { ...options, hash: false });
      return;
    }

    showAppealStep(currentAppealStep, { ...options, hash: false });
  };

  appealWizardPage.querySelectorAll("[data-next-step]").forEach((button) => {
    button.addEventListener("click", () => {
      pressButton(button);
      showAppealStep(currentAppealStep + 1);
    });
  });

  appealWizardPage.querySelectorAll("[data-prev-step]").forEach((button) => {
    button.addEventListener("click", () => {
      pressButton(button);
      showAppealStep(currentAppealStep - 1);
    });
  });

  appealWizardPage.querySelectorAll("[data-go-step]").forEach((button) => {
    button.addEventListener("click", () => {
      pressButton(button);
      showAppealStep(Number(button.dataset.goStep));
    });
  });

  appealWizardPage.querySelectorAll("[data-show-result]").forEach((button) => {
    button.addEventListener("click", () => {
      pressButton(button);
      showAppealResult(button.dataset.showResult);
    });
  });

  appealWizardPage.querySelectorAll("[data-select-card]").forEach((button) => {
    button.addEventListener("click", () => {
      const group = button.closest(".choice-grid, .category-choice-grid, .pill-choice-row");
      if (group) {
        group.querySelectorAll("[data-select-card]").forEach((item) => {
          item.classList.remove("is-selected");
        });
      }
      button.classList.add("is-selected");
    });
  });

  appealWizardPage.querySelectorAll("[data-count-source]").forEach((field) => {
    const output = appealWizardPage.querySelector(`[data-count-output="${field.dataset.countSource}"]`);
    const updateCount = () => {
      if (output) output.textContent = String(field.value.length);
    };

    field.addEventListener("input", updateCount);
    updateCount();
  });

  window.addEventListener("hashchange", () => renderAppealRoute({ scroll: false }));
  window.addEventListener("popstate", () => renderAppealRoute({ scroll: false }));
  renderAppealRoute({ scroll: false });
}

const dashboardPage = document.querySelector("[data-dashboard-page]");

if (dashboardPage) {
  const dashboardScreens = Array.from(dashboardPage.querySelectorAll("[data-dashboard-screen]"));
  const dashboardButtons = Array.from(dashboardPage.querySelectorAll("[data-dashboard-target]"));
  const dashboardMenuButtons = Array.from(dashboardPage.querySelectorAll(".cabinet-menu [data-dashboard-target]"));
  const dashboardScreenNames = dashboardScreens.map((screen) => screen.dataset.dashboardScreen);
  const avatarInput = dashboardPage.querySelector("[data-avatar-input]");
  const avatarRemove = dashboardPage.querySelector("[data-avatar-remove]");
  const avatarStatus = dashboardPage.querySelector("[data-avatar-status]");
  const avatarPreviews = Array.from(dashboardPage.querySelectorAll("[data-avatar-preview]"));
  const profileCompletion = dashboardPage.querySelector("[data-profile-completion]");
  const profileCompletionNote = dashboardPage.querySelector("[data-profile-completion-note]");
  const avatarStorageKey = "rukaDobraProfileAvatar";
  const avatarMaxSize = 2 * 1024 * 1024;
  const avatarTypes = ["image/jpeg", "image/png", "image/webp"];
  let currentDashboardScreen = "dashboard";

  const setAvatarStatus = (message, state = "") => {
    if (!avatarStatus) return;

    avatarStatus.textContent = message;
    avatarStatus.classList.toggle("is-error", state === "error");
    avatarStatus.classList.toggle("is-success", state === "success");
  };

  const renderAvatar = (src) => {
    avatarPreviews.forEach((preview) => {
      preview.textContent = "";
      preview.classList.add("has-image");

      const image = document.createElement("img");
      image.src = src;
      image.alt = "";
      image.decoding = "async";
      preview.append(image);
    });

    if (avatarRemove) avatarRemove.hidden = false;
    if (profileCompletion) profileCompletion.textContent = "100% заполнено";
    if (profileCompletionNote) profileCompletionNote.textContent = "Фото профиля добавлено.";
    setAvatarStatus("Фото профиля загружено", "success");
  };

  const resetAvatar = (message = "Фото ещё не загружено") => {
    avatarPreviews.forEach((preview) => {
      preview.replaceChildren();
      preview.classList.remove("has-image");
      preview.textContent = "АИ";
    });

    if (avatarRemove) avatarRemove.hidden = true;
    if (profileCompletion) profileCompletion.textContent = "92% заполнено";
    if (profileCompletionNote) profileCompletionNote.textContent = "Добавьте фото, чтобы завершить профиль.";
    setAvatarStatus(message);
  };

  const getStoredAvatar = () => {
    try {
      return localStorage.getItem(avatarStorageKey);
    } catch (error) {
      return "";
    }
  };

  const storeAvatar = (src) => {
    try {
      localStorage.setItem(avatarStorageKey, src);
      return true;
    } catch (error) {
      return false;
    }
  };

  const removeStoredAvatar = () => {
    try {
      localStorage.removeItem(avatarStorageKey);
    } catch (error) {
      return;
    }
  };

  const showDashboardScreen = (name, options = {}) => {
    const nextScreen = dashboardScreenNames.includes(name) ? name : "dashboard";
    currentDashboardScreen = nextScreen;

    dashboardScreens.forEach((screen) => {
      const isActive = screen.dataset.dashboardScreen === nextScreen;
      screen.hidden = !isActive;
      screen.classList.toggle("is-active", isActive);
      screen.setAttribute("aria-hidden", isActive ? "false" : "true");
    });

    dashboardMenuButtons.forEach((button) => {
      const isActive = button.dataset.dashboardTarget === nextScreen;
      button.classList.toggle("is-active", isActive);
      if (isActive) {
        button.setAttribute("aria-current", "page");
      } else {
        button.removeAttribute("aria-current");
      }
    });

    if (options.hash !== false) {
      const nextHash = `#${nextScreen}`;
      if (window.location.hash !== nextHash) {
        history.pushState(null, "", nextHash);
      }
    }

    if (options.scroll !== false) {
      dashboardPage.scrollIntoView({ block: "start", behavior: "smooth" });
    }
  };

  const renderDashboardRoute = (options = {}) => {
    const hash = window.location.hash.replace("#", "");
    if (!hash) {
      showDashboardScreen(currentDashboardScreen || "dashboard", { ...options, hash: false });
      return;
    }

    if (dashboardScreenNames.includes(hash)) {
      showDashboardScreen(hash, { ...options, hash: false });
    }
  };

  dashboardButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const target = button.dataset.dashboardTarget;
      if (!dashboardScreenNames.includes(target)) return;

      pressButton(button);
      showDashboardScreen(target);
      closeMenu();
    });
  });

  if (avatarInput && avatarPreviews.length) {
    const storedAvatar = getStoredAvatar();

    if (storedAvatar) {
      renderAvatar(storedAvatar);
    } else {
      resetAvatar();
    }

    avatarInput.addEventListener("change", () => {
      const [file] = avatarInput.files || [];
      if (!file) return;

      if (!avatarTypes.includes(file.type)) {
        setAvatarStatus("Выберите JPG, PNG или WebP", "error");
        avatarInput.value = "";
        return;
      }

      if (file.size > avatarMaxSize) {
        setAvatarStatus("Файл больше 2 МБ", "error");
        avatarInput.value = "";
        return;
      }

      const reader = new FileReader();

      reader.addEventListener("load", () => {
        const result = typeof reader.result === "string" ? reader.result : "";
        if (!result) {
          setAvatarStatus("Не удалось загрузить фото", "error");
          avatarInput.value = "";
          return;
        }

        renderAvatar(result);
        if (!storeAvatar(result)) {
          setAvatarStatus("Фото добавлено, но не сохранится после перезагрузки", "error");
        }
        avatarInput.value = "";
      });

      reader.addEventListener("error", () => {
        setAvatarStatus("Не удалось загрузить фото", "error");
        avatarInput.value = "";
      });

      reader.readAsDataURL(file);
    });

    if (avatarRemove) {
      avatarRemove.addEventListener("click", () => {
        removeStoredAvatar();
        resetAvatar("Фото удалено");
        avatarInput.value = "";
      });
    }
  }

  window.addEventListener("hashchange", () => renderDashboardRoute({ scroll: false }));
  window.addEventListener("popstate", () => renderDashboardRoute({ scroll: false }));
  renderDashboardRoute({ scroll: false });
}
