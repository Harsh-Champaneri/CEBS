<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us - CEBS</title>

  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <!-- Icons -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
    rel="stylesheet" />
  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    crossorigin="anonymous" />

  <link rel="stylesheet" href="style.css" />

  <style>
    /* Page Layout */
    body {
      background:
        radial-gradient(ellipse 120% 60% at 20% 10%,
          #bfdbfe 0%,
          transparent 60%),
        radial-gradient(ellipse 90% 50% at 80% 30%,
          #fbcfe8 0%,
          transparent 55%),
        radial-gradient(ellipse 70% 40% at 50% 80%,
          #7dd3fc 0%,
          transparent 60%);
      background-color: var(--charcoal);
      position: relative;
    }

    /* Floating Orbs */
    .orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(90px);
      pointer-events: none;
      z-index: 0;
      animation: floatOrb 10s ease-in-out infinite;
    }

    .orb-1 {
      width: 500px;
      height: 500px;
      background: radial-gradient(circle,
          rgba(14, 165, 233, 0.28),
          transparent 70%);
      top: -120px;
      left: -100px;
    }

    .orb-2 {
      width: 400px;
      height: 400px;
      background: radial-gradient(circle,
          rgba(244, 63, 94, 0.24),
          transparent 70%);
      bottom: -80px;
      right: -60px;
      animation-delay: 3s;
    }

    @keyframes floatOrb {

      0%,
      100% {
        transform: translate(0, 0) scale(1);
      }

      50% {
        transform: translate(30px, -40px) scale(1.08);
      }
    }

    /* Hero Section */
    .about-hero {
      padding: 40px 0 30px;
      position: relative;
      text-align: center;
    }

    .about-hero h1 {
      font-family: "Playfair Display", serif;
      font-weight: 800;
      font-size: 4rem;
      background: linear-gradient(135deg, #0ea5e9, #f43f5e);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 20px;
      animation: fadeInUp 0.8s ease;
    }

    .about-hero p {
      font-size: 1.3rem;
      color: #475569;
      max-width: 800px;
      margin: 0 auto 30px;
      line-height: 1.9;
      animation: fadeInUp 1s ease;
    }

    .hero-divider {
      width: 100px;
      height: 4px;
      background: linear-gradient(90deg, #0ea5e9, #f43f5e);
      margin: 0 auto 40px;
      border-radius: 4px;
      animation: fadeInUp 1.2s ease;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* What is CEBS Section */
    .what-is-cebs {
      padding: 30px 0;
      position: relative;
    }

    .cebs-intro-card {
      background: var(--card-bg);
      border: 1px solid var(--card-bdr);
      border-radius: 24px;
      padding: 60px 50px;
      backdrop-filter: blur(12px);
      box-shadow: 0 20px 50px rgba(14, 165, 233, 0.15);
      margin-bottom: 50px;
    }

    .cebs-intro-card h2 {
      font-family: "Playfair Display", serif;
      font-weight: 700;
      font-size: 2.5rem;
      color: #1e293b;
      margin-bottom: 25px;
    }

    .cebs-intro-card p {
      font-size: 1.1rem;
      color: #64748b;
      line-height: 1.9;
      margin-bottom: 20px;
    }

    /* Features Section */
    .features-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 25px;
    }

    .feature-box {
      background: var(--card-bg);
      border: 1px solid var(--card-bdr);
      border-radius: 18px;
      padding: 35px;
      backdrop-filter: blur(12px);
      box-shadow: 0 10px 30px rgba(14, 165, 233, 0.12);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .feature-box::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #0ea5e9, #f43f5e);
      transform: scaleX(0);
      transition: transform 0.4s ease;
    }

    .feature-box:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 45px rgba(14, 165, 233, 0.25);
      border-color: rgba(14, 165, 233, 0.3);
    }

    .feature-box:hover::before {
      transform: scaleX(1);
    }

    .feature-icon-wrapper {
      width: 70px;
      height: 70px;
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.2),
          rgba(56, 189, 248, 0.12));
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }

    .feature-box:hover .feature-icon-wrapper {
      transform: scale(1.1) rotate(5deg);
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.3),
          rgba(56, 189, 248, 0.2));
    }

    .feature-icon-wrapper i {
      font-size: 1.8rem;
      color: #0ea5e9;
    }

    .feature-box h3 {
      font-weight: 600;
      font-size: 1.2rem;
      color: #1e293b;
      margin-bottom: 12px;
    }

    .feature-box p {
      font-size: 0.95rem;
      color: #64748b;
      line-height: 1.7;
      margin: 0;
    }

    /* Developers Section */
    .developers-section {
      padding: 50px 0;
      position: relative;
    }

    .section-title {
      text-align: center;
      margin-bottom: 70px;
    }

    .section-title h2 {
      font-family: "Playfair Display", serif;
      font-weight: 800;
      font-size: 3rem;
      color: #1e293b;
      margin-bottom: 15px;
    }

    .section-title p {
      font-size: 1.15rem;
      color: #64748b;
    }

    .section-title .hero-divider {
      margin: 20px auto;
    }

    .developers-grid {
      display: grid;

      gap: 35px;
    }

    /* Developer Card - Glass Morphism with Theme Colors */
    .developer-card {
      background: rgba(255, 255, 255, 0.75);
      border: 1px solid rgba(14, 165, 233, 0.2);
      border-radius: 24px;
      padding: 35px;
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      box-shadow:
        0 8px 32px rgba(14, 165, 233, 0.15),
        inset 0 1px 1px rgba(255, 255, 255, 0.6);
      position: relative;
      overflow: hidden;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Gradient border animation */
    .developer-card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg,
          #0ea5e9,
          #38bdf8,
          #f43f5e,
          #fb7185,
          #0ea5e9);
      background-size: 200% 100%;
      animation: gradientMove 3s linear infinite;
    }

    @keyframes gradientMove {
      0% {
        background-position: 0% 50%;
      }

      100% {
        background-position: 200% 50%;
      }
    }

    /* Glass shine effect */
    .developer-card::after {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(45deg,
          transparent,
          rgba(255, 255, 255, 0.1),
          transparent);
      transform: rotate(45deg);
      transition: all 0.5s;
      opacity: 0;
    }

    .developer-card:hover {
      transform: translateY(-12px) scale(1.02);
      box-shadow:
        0 20px 60px rgba(14, 165, 233, 0.3),
        0 0 0 1px rgba(14, 165, 233, 0.3),
        inset 0 1px 1px rgba(255, 255, 255, 0.8);
      border-color: rgba(14, 165, 233, 0.4);
    }

    .developer-card:hover::after {
      opacity: 1;
      top: 100%;
      left: 100%;
    }

    .card-layout {
      display: flex;
      gap: 25px;
      align-items: flex-start;
      position: relative;
      z-index: 1;
    }

    /* Developer Photo Circle */
    .developer-photo {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.25),
          rgba(56, 189, 248, 0.15));
      border: 3px solid rgba(14, 165, 233, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 0.9rem;
      font-weight: 600;
      color: #0369a1;
      text-align: center;
      padding: 20px;
      line-height: 1.3;
      box-shadow:
        0 8px 24px rgba(14, 165, 233, 0.2),
        inset 0 1px 1px rgba(255, 255, 255, 0.5);
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
    }

    .developer-photo::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg,
          transparent,
          rgba(255, 255, 255, 0.3),
          transparent);
      transform: translateX(-100%) rotate(45deg);
      transition: transform 0.6s;
    }

    .developer-card:hover .developer-photo {
      transform: scale(1.08) rotate(5deg);
      border-color: rgba(244, 63, 94, 0.4);
      box-shadow:
        0 12px 32px rgba(14, 165, 233, 0.3),
        inset 0 1px 1px rgba(255, 255, 255, 0.7);
    }

    .developer-card:hover .developer-photo::before {
      transform: translateX(100%) rotate(45deg);
    }

    /* Developer Details */
    .developer-details {
      flex: 1;
    }

    .dev-basic-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
      gap: 18px;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(9, 130, 186, 0.4);
    }

    .dev-info-item {
      background: linear-gradient(135deg,
          rgba(14, 164, 233, 0.185),
          rgba(56, 191, 248, 0.101));
      padding: 12px;
      border-radius: 10px;
      border: 1px solid rgba(14, 165, 233, 0.1);
      transition: all 0.3s ease;
    }

    .dev-info-item:hover {
      background: linear-gradient(135deg,
          rgba(14, 164, 233, 0.27),
          rgba(56, 191, 248, 0.197));
      border-color: rgba(14, 165, 233, 0.2);
      transform: translateY(-2px);
    }

    .dev-info-label {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #64748b;
      margin-bottom: 4px;
      font-weight: 600;
    }

    .dev-info-value {
      font-size: 0.95rem;
      color: #1e293b;
      font-weight: 600;
    }

    /* Key Contributions - Restructured */
    .dev-section {
      margin-bottom: 25px;
    }

    .dev-section-title {
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #0ea5e9;
      margin-bottom: 15px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .dev-section-title i {
      font-size: 1rem;
    }

    .contributions-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px 15px;
    }

    .contribution-item {
      background: linear-gradient(135deg,
          rgba(240, 52, 83, 0.182),
          rgba(251, 113, 134, 0.164));
      padding: 12px 16px;
      border-radius: 10px;
      border-left: 3px solid #f43f5e;
      color: #1e293b;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .contribution-item::before {
      content: "";
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 3px;
      background: linear-gradient(180deg, #f43757, #ff647b);
      transition: width 0.3s ease;
    }

    .contribution-item:hover {
      background: linear-gradient(135deg,
          rgba(244, 63, 94, 0.15),
          rgba(251, 113, 133, 0.1));
      transform: translateX(5px);
      box-shadow: 0 4px 12px rgba(244, 63, 94, 0.15);
    }

    .contribution-item:hover::before {
      width: 100%;
      opacity: 0.1;
    }

    /* Language Proficiency - Restructured */
    .languages-showcase {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
      gap: 10px;
    }

    .tools-showcase {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
      gap: 10px;
    }

    .language-badge {
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.12),
          rgba(56, 189, 248, 0.08));
      padding: 10px 14px;
      border-radius: 12px;
      text-align: center;
      font-size: 0.85rem;
      color: #0369a1;
      font-weight: 600;
      border: 1px solid rgba(14, 165, 233, 0.2);
      transition: all 0.3s ease;
      cursor: default;
      position: relative;
      overflow: hidden;
    }

    .language-badge::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.2),
          rgba(56, 189, 248, 0.1));
      transform: translateY(100%);
      transition: transform 0.3s ease;
    }

    .language-badge:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 6px 16px rgba(14, 165, 233, 0.25);
      border-color: rgba(14, 165, 233, 0.4);
    }

    .language-badge:hover::before {
      transform: translateY(0);
    }

    .language-badge span {
      position: relative;
      z-index: 1;
    }

    /* Social Media Icons */
    .dev-social {
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid rgba(9, 130, 186, 0.4);
      display: flex;
      gap: 12px;
      justify-content: flex-start;
      flex-wrap: wrap;
    }

    .social-icon {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, rgba(14, 165, 233, .15), rgba(56, 189, 248, .1));
      border: 1px solid rgba(14, 165, 233, .25);
      color: #0ea5e9;
      text-decoration: none;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(14, 165, 233, .1);
    }

    .social-icon:hover {
      transform: translateY(-4px) scale(1.1);
      box-shadow: 0 8px 20px rgba(14, 165, 233, .25);
      background: linear-gradient(135deg, #0ea5e9, #38bdf8);
      color: #ffffff;
      border-color: rgba(14, 165, 233, .4);
    }

    .social-icon.instagram:hover {
      background: linear-gradient(135deg, #e11d48, #f43f5e);
      border-color: rgba(244, 63, 94, .4);
    }

    .social-icon.github:hover {
      background: linear-gradient(135deg, #1e293b, #334155);
      border-color: rgba(30, 41, 59, .4);
    }

    .social-icon.linkedin:hover {
      background: linear-gradient(135deg, #0284c7, #0ea5e9);
      border-color: rgba(2, 132, 199, .4);
    }

    .social-icon.gmail:hover {
      background: linear-gradient(135deg, #dc2626, #ef4444);
      border-color: rgba(220, 38, 38, .4);
    }

    .developer-photo img {
      width: 20rem;
      height: 20rem;
      object-fit: cover;
      border-radius: 50%;
    }

    #ayush-img {
      width: 17rem;
      height: 17rem;
      object-fit: contain;
      border-radius: 50%;
      margin-left: 1rem;
      margin-bottom: 2.50rem;
    }

    #harsh-img {
      width: 27rem;
      height: 27rem;
      object-fit: contain;
      border-radius: 50%;
      margin-left: 1rem;
      margin-bottom: 2.30rem;
      margin-top: 0.5rem;
    }

    #krupa-img {
      width: 13rem;
      height: 13rem;
      margin-right: 2rem;
      margin-left: 1rem;
      margin-bottom: 2rem;
      margin-top: 0.5rem;
    }

    #mansi-img {
      width: 9rem;
      height: 9rem;
    }

    .mission-section {
      padding: 0px 0;
    }

    .mission-card {
      background: var(--card-bg);
      border: 1px solid var(--card-bdr);
      border-radius: 18px;
      padding: 40px;
      backdrop-filter: blur(12px);
      box-shadow: 0 10px 30px rgba(14, 165, 233, .15);
      height: 100%;
      transition: transform .3s, box-shadow .3s;
    }

    .mission-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 40px rgba(14, 165, 233, .25);
    }

    .mission-icon {
      width: 70px;
      height: 70px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      margin-bottom: 24px;
    }

    .mission-card h3 {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      font-size: 1.6rem;
      color: #1e293b;
      margin-bottom: 16px;
    }

    .mission-card p {
      color: #64748b;
      line-height: 1.8;
      margin: 0;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
      .developers-grid {
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      }
    }

    @media (max-width: 991px) {
      .about-hero h1 {
        font-size: 3rem;
      }

      .section-title h2 {
        font-size: 2.5rem;
      }
    }

    @media (max-width: 768px) {
      .about-hero h1 {
        font-size: 2.5rem;
      }

      .about-hero p {
        font-size: 1.1rem;
      }

      .cebs-intro-card {
        padding: 40px 30px;
      }

      .section-title h2 {
        font-size: 2rem;
      }

      .card-layout {
        flex-direction: column;
        align-items: center;
      }

      .developer-photo {
        width: 120px;
        height: 120px;
      }

      .dev-basic-info {
        grid-template-columns: 1fr;
      }

      .contributions-grid {
        grid-template-columns: 1fr;
      }

      .languages-showcase {
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
      }

      .dev-social {
        justify-content: center;
      }
    }

    @media (max-width: 576px) {
      .about-hero {
        padding: 80px 0 60px;
      }

      .about-hero h1 {
        font-size: 2rem;
      }

      .features-container {
        grid-template-columns: 1fr;
      }

      .developers-grid {
        grid-template-columns: 1fr;
      }

      .developer-card {
        padding: 25px;
      }
    }

    /* Scroll Reveal */
    .reveal {
      opacity: 0;
      transform: translateY(50px);
      transition: all 0.8s ease;
    }

    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>

<body>
  <?php
  $activePage = "AboutUs";
  $showNavbar = true;
  include "header.php";
  ?>

  <!-- Floating Orbs -->
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>

  <!-- 🔹 Hero Section -->
  <section class="about-hero reveal">
    <div class="container">
      <h1>About CEBS</h1>
      <div class="hero-divider"></div>
      <p>
        College Event Booking System - A modern platform revolutionizing how
        college events are managed, booked, and experienced by students and
        faculty.
      </p>
    </div>
  </section>

  <!-- 🔹 What is CEBS Section -->
  <section class="what-is-cebs reveal">
    <div class="container">
      <div class="cebs-intro-card">
        <h2>What is CEBS?</h2>
        <p>
          The <strong>College Event Booking System (CEBS)</strong> is an
          innovative web-based solution designed to streamline the entire
          lifecycle of college events. From creation to execution, our
          platform handles registrations, payments, notifications, and
          attendance tracking seamlessly.
        </p>
        <p>
          Built with modern web technologies and a user-first approach, CEBS
          eliminates traditional paperwork and manual processes, providing a
          centralized digital hub for the entire college community.
        </p>
        <p>
          Whether you're a student looking to participate in events, a faculty
          member organizing activities, or an administrator managing
          campus-wide programs, CEBS offers tailored features and intuitive
          interfaces to make event management effortless.
        </p>
      </div>

      <section class="mission-section reveal">
        <div class="container">
          <div class="row g-4">
            <div class="col-md-6">
              <div class="mission-card">
                <div class="mission-icon" style="background: linear-gradient(135deg, rgba(14,165,233,.2), rgba(56,189,248,.12)); color: #0284c7;">
                  <i class="bi bi-bullseye"></i>
                </div>
                <h3>Our Mission</h3>
                <p>
                  To revolutionize the way college events are organized and managed by providing
                  a centralized, user-friendly platform that connects students, faculty, and
                  administrators. We aim to eliminate the hassles of traditional event management
                  and create seamless experiences for all participants.
                </p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mission-card">
                <div class="mission-icon" style="background: linear-gradient(135deg, rgba(244,63,94,.2), rgba(251,113,133,.12)); color: #be123c;">
                  <i class="bi bi-lightbulb"></i>
                </div>
                <h3>Our Vision</h3>
                <p>
                  To become the leading event management solution for educational institutions
                  across India, fostering a vibrant campus culture through technology. We envision
                  a future where every student has equal access to opportunities and every event
                  organizer has the tools they need for success.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </section>

  <!-- 🔹 Developers Section -->
  <section class="developers-section reveal">
    <div class="container">
      <div class="section-title">
        <h2>Meet the Developers</h2>
        <div class="hero-divider"></div>
        <p>The team behind CEBS - Pre-Final Year CSE Students</p>
      </div>

      <div class="developers-grid">
        <!-- Developer 1 -->
        <div class="developer-card">
          <div class="card-layout">
            <div class="developer-photo">
              <img src="https://res.cloudinary.com/dcfxlhblb/image/upload/v1777707902/Ayush_vfuxiy.jpg" alt="Developer Photo" id="ayush-img" />
            </div>

            <div class="developer-details">
              <div class="dev-basic-info">
                <div class="dev-info-item">
                  <div class="dev-info-label">Name:</div>
                  <div class="dev-info-value">Ayush Deveshkumar Kansara</div>
                </div>
                <div class="dev-info-item">
                  <div class="dev-info-label">PEN:</div>
                  <div class="dev-info-value">230840131006</div>
                </div>
                <div class="dev-info-item">
                  <div class="dev-info-label">Class:</div>
                  <div class="dev-info-value">CSE 23A</div>
                </div>
              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="bi bi-code-square"></i>
                  Key Contributions:
                </div>
                <div class="contributions-grid">
                  <div class="contribution-item">
                    Designed intuitive and responsive UI/UX for the entire CEBS system
                  </div>

                  <div class="contribution-item">
                    Designed system flow for smooth and efficient operations
                  </div>

                  <div class="contribution-item">
                    Developed responsive layouts optimized for desktop, and mobile devices
                  </div>

                  <div class="contribution-item">
                    Designed a user-friendly navigation system for smooth usability
                  </div>

                  <div class="contribution-item">
                    Built comprehensive student and faculty dashboard interfaces
                  </div>

                  <div class="contribution-item">
                    Designed authentication pages
                  </div>

                  <div class="contribution-item">
                    Developed QR code scanner interface for contactless entry verification
                  </div>

                  <div class="contribution-item">
                    Implemented payment confirmation and invoice generation templates
                  </div>

                  <div class="contribution-item">
                    Developed email templates for event notifications, OTPs , and credentials
                  </div>

                  <div class="contribution-item">
                    Performed end-to-end testing for reliablility and performance
                  </div>

                </div>
              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="bi bi-stars"></i>
                  Language Proficiency:
                </div>
                <div class="languages-showcase">
                  <div class="language-badge"><span>HTML5</span></div>
                  <div class="language-badge"><span>CSS3</span></div>
                  <div class="language-badge"><span>JavaScript</span></div>
                  <div class="language-badge"><span>Bootstrap 5</span></div>
                  <div class="language-badge"><span>SQL</span></div>
                </div>
              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="fa-solid fa-screwdriver-wrench"></i>
                  Tools Used:
                </div>
                <div class="tools-showcase">
                  <div class="language-badge"><span>Bootstrap</span></div>
                  <div class="language-badge"><span>FA Icons</span></div>
                  <div class="language-badge"><span>Google fonts</span></div>
                  <div class="language-badge"><span>Google Maps</span></div>
                </div>
              </div>

              <!-- Social Media -->
              <div class="dev-social">
                <a href="https://www.instagram.com/ayush1212__?igsh=NHJ4MHJiOHQ3cDFn" target="_blank" class="social-icon instagram" title="Instagram">
                  <i class="bi bi-instagram"></i>
                </a>
                <a href="https://github.com/ayush-kansara" target="_blank" class="social-icon github" title="GitHub">
                  <i class="bi bi-github"></i>
                </a>
                <a href="http://www.linkedin.com/in/ayush-kansara-377086285" target="_blank" class="social-icon linkedin" title="LinkedIn">
                  <i class="bi bi-linkedin"></i>
                </a>
                <a href="mailto:aayushkansara2903@gmail.com" target="_blank" class="social-icon gmail" title="Gmail">
                  <i class="bi bi-envelope-fill"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Developer 2 -->
        <div class="developer-card">
          <div class="card-layout">
            <div class="developer-photo">
              <img src="https://res.cloudinary.com/dcfxlhblb/image/upload/v1777707900/Harsh_Profile_ofrde2.jpg" alt="Developer Photo" id="harsh-img" />
            </div>

            <div class="developer-details">
              <div class="dev-basic-info">
                <div class="dev-info-item">
                  <div class="dev-info-label">Name:</div>
                  <div class="dev-info-value">Harsh Rakeshkumar Champaneri</div>
                </div>
                <div class="dev-info-item">
                  <div class="dev-info-label">PEN:</div>
                  <div class="dev-info-value">230840131014</div>
                </div>
                <div class="dev-info-item">
                  <div class="dev-info-label">Class:</div>
                  <div class="dev-info-value">CSE 23A </div>
                </div>
              </div>

              <div class="dev-section">

                <div class="dev-section-title">
                  <i class="bi bi-code-square"></i>
                  Key Contributions:
                </div>

                <div class="contributions-grid">

                  <div class="contribution-item">
                    Designed database tables and data flow for backend operations
                  </div>

                  <div class="contribution-item">
                    Developed backend for all modules and pages
                  </div>

                  <div class="contribution-item">
                    Razorpay payment gateway integration
                  </div>

                  <div class="contribution-item">
                    QR code generation & scanning system
                  </div>

                  <div class="contribution-item">
                    Invoice generation for event registrations and payment transactions
                  </div>

                  <div class="contribution-item">
                    Live search and filtering
                  </div>

                  <div class="contribution-item">
                    Real-time form validation
                  </div>

                  <div class="contribution-item">
                    Integrated Cloudinary for storage, optimization, and fast content delivery
                  </div>

                  <div class="contribution-item">
                    Secure password reset via email with a time-limited link
                  </div>

                  <div class="contribution-item">
                    Secure login & OTP-based registration with expiry and resend support
                  </div>

                </div>

              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="bi bi-stars"></i>
                  Language Proficiency:
                </div>
                <div class="languages-showcase">
                  <div class="language-badge"><span>HTML5</span></div>
                  <div class="language-badge"><span>CSS3</span></div>
                  <div class="language-badge"><span>JavaScript</span></div>
                  <div class="language-badge"><span>PHP</span></div>
                  <div class="language-badge"><span>SQL</span></div>
                </div>
              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="fa-solid fa-screwdriver-wrench"></i>
                  Tools Used:
                </div>
                <div class="tools-showcase">
                  <div class="language-badge"><span>PHPMailer</span></div>
                  <div class="language-badge"><span>Dompdf</span></div>
                  <div class="language-badge"><span>Cloudinary</span></div>
                  <div class="language-badge"><span>Razorpay</span></div>
                  <div class="language-badge"><span>Phpqrcode</span></div>
                  <div class="language-badge"><span>AJAX</span></div>
                  <div class="language-badge"><span>jQuery</span></div>
                  <!-- <div class="language-badge" id="test"><span>HTML5 QR Code Scanner</span></div> -->
                </div>
              </div>

              <!-- Social Media -->
              <div class="dev-social">
                <a href="https://www.instagram.com/harsh.champ2005?igsh=MWpvYnZjcGZidDhlcw==" target="_blank" class="social-icon instagram" title="Instagram">
                  <i class="bi bi-instagram"></i>
                </a>
                <a href="https://github.com/Harsh-3108" target="_blank" class="social-icon github" title="GitHub">
                  <i class="bi bi-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/harsh-rakeshkumar-champaneri-a04523315?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app" target="_blank" class="social-icon linkedin" title="LinkedIn">
                  <i class="bi bi-linkedin"></i>
                </a>
                <a href="mailto:champharsh.2005@gmail.com" target="_blank" class="social-icon gmail" title="Gmail">
                  <i class="bi bi-envelope-fill"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Developer 3 -->
        <div class="developer-card">
          <div class="card-layout">
            <div class="developer-photo">
              <img src="https://res.cloudinary.com/dcfxlhblb/image/upload/v1778131920/mansi_vgck6e.jpg" alt="Developer Photo" id="mansi-img" />
            </div>

            <div class="developer-details">
              <div class="dev-basic-info">
                <div class="dev-info-item">
                  <div class="dev-info-label">Name:</div>
                  <div class="dev-info-value">Mansi Manojkumar Jethwani</div>
                </div>
                <div class="dev-info-item">
                  <div class="dev-info-label">PEN:</div>
                  <div class="dev-info-value">230840131032</div>
                </div>
                <div class="dev-info-item">
                  <div class="dev-info-label">Class:</div>
                  <div class="dev-info-value">CSE 23A</div>
                </div>
              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="bi bi-code-square"></i>
                  Key Contributions:
                </div>

                <div class="contributions-grid">
                  <div class="contribution-item">
                    Integrated third-party APIs for automated WhatsApp messaging
                  </div>
                  <div class="contribution-item">
                    Conducted end-to-end testing to ensure complete system functionality
                  </div>
                </div>

              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="bi bi-stars"></i>
                  Language Proficiency:
                </div>
                <div class="languages-showcase">
                  <div class="language-badge"><span>PHP</span></div>
                  <div class="language-badge"><span>SQL</span></div>
                </div>
              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="fa-solid fa-screwdriver-wrench"></i>
                  Tools Used:
                </div>
                <div class="tools-showcase">
                  <div class="language-badge"><span>UltraMsg</span></div>
                </div>
              </div>

              <div class="dev-social">
                <a href="https://www.instagram.com/maahiiiii.ii?igsh=dXRsNWVhM2NmdHFx" target="_blank" class="social-icon instagram" title="Instagram">
                  <i class="bi bi-instagram"></i>
                </a>
                <a href="https://github.com/cs230840131032-bit" target="_blank" class="social-icon github" title="GitHub">
                  <i class="bi bi-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/mansi-jethwani-87a276361?utm_source=share_via&utm_content=profile&utm_medium=member_android" target="_blank" class="social-icon linkedin" title="LinkedIn">
                  <i class="bi bi-linkedin"></i>
                </a>
                <a href="mailto:cs.230840131032@gmail.com" target="_blank" class="social-icon gmail" title="Gmail">
                  <i class="bi bi-envelope-fill"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Developer 4 -->
        <div class="developer-card">
          <div class="card-layout">
            <div class="developer-photo">
              <img src="https://res.cloudinary.com/dcfxlhblb/image/upload/v1778131826/Krupa_njy4ov.jpg" alt="Developer Photo" id="krupa-img" />
            </div>

            <div class="developer-details">
              <div class="dev-basic-info">
                <div class="dev-info-item">
                  <div class="dev-info-label">Name:</div>
                  <div class="dev-info-value">Krupa Sureshbhai Ahir</div>
                </div>
                <div class="dev-info-item">
                  <div class="dev-info-label">PEN:</div>
                  <div class="dev-info-value">230840131003</div>
                </div>
                <div class="dev-info-item">
                  <div class="dev-info-label">Class:</div>
                  <div class="dev-info-value">CSE 23A</div>
                </div>
              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="bi bi-code-square"></i>
                  Key Contributions:
                </div>

                <div class="contributions-grid">
                  <div class="contribution-item">
                    View participants live cards
                  </div>
                  <div class="contribution-item">
                    Event wise live details
                  </div>

                  <div class="contribution-item">
                    View event
                  </div>

                  <div class="contribution-item">
                    Edit event
                  </div>

                  <div class="contribution-item">
                    PDF generation
                  </div>

                  <div class="contribution-item">
                    Excel sheet generation
                  </div>
                </div>

              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="bi bi-stars"></i>
                  Language Proficiency:
                </div>
                <div class="languages-showcase">
                  <div class="language-badge"><span>HTML5</span></div>
                  <div class="language-badge"><span>CSS3</span></div>
                  <div class="language-badge"><span>PHP</span></div>
                  <div class="language-badge"><span>SQL</span></div>
                </div>
              </div>

              <div class="dev-section">
                <div class="dev-section-title">
                  <i class="fa-solid fa-screwdriver-wrench"></i>
                  Tools Used:
                </div>
                <div class="tools-showcase">
                  <div class="language-badge"><span>Phpspreadsheet</span></div>
                  <div class="language-badge"><span>Dompdf</span></div>
                </div>
              </div>

              <div class="dev-social">
                <a href="https://www.instagram.com/krupa_8136?igsh=MWFucmN6YWs2YnE1ag==" target="_blank" class="social-icon instagram" title="Instagram">
                  <i class="bi bi-instagram"></i>
                </a>
                <a href="https://github.com/krupa026" target="_blank" class="social-icon github" title="GitHub">
                  <i class="bi bi-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/krupa-ahir-b72086255/" target="_blank" class="social-icon linkedin" title="LinkedIn">
                  <i class="bi bi-linkedin"></i>
                </a>
                <a href="mailto:engineeroverhere@gmail.com" target="_blank" class="social-icon gmail" title="Gmail">
                  <i class="bi bi-envelope-fill"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- 🔹 Footer -->
  <?php include "footer.php"; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Scroll Reveal Animation -->
  <script>
    const reveals = document.querySelectorAll(".reveal");
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("active");
          }
        });
      }, {
        threshold: 0.1
      },
    );

    reveals.forEach((reveal) => observer.observe(reveal));
  </script>
</body>

</html>