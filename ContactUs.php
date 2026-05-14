<?php

include "connection.php";

if (isset($_POST["sendMessage"])) {
  $firstname = $_POST["firstname"];
  $lastname = $_POST["lastname"];
  $email = $_POST["email"];
  $message = $_POST["message"];

  $contact_us_query = $connection->prepare("INSERT INTO contact_us(firstname,lastname,email,message) VALUES(?, ?, ?, ?)");
  $contact_us_query->bind_param("ssss", $firstname, $lastname, $email, $message);

  if ($contact_us_query->execute()) {
    header("location:ContactUs.php?message=Data Submitted Successfully.");
    exit();
  }
}

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us - CEBS</title>

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
  <link rel="stylesheet" href="ContactAboutStyle.css">
</head>

<body>
  <?php
  $activePage = "contactUs";
  $showNavbar = true;
  include "header.php";
  ?>

  <!-- 🔹 Contact Hero -->
  <section class="contact-hero">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
      <div class="contact-header reveal">
        <h1>Get in Touch</h1>
        <div class="contact-divider"></div>
        <p>Have questions about an event? We are here to help.</p>
      </div>
    </div>
  </section>

  <!-- 🔹 Contact Form Section -->
  <section class="contact-section reveal">
    <div class="container">
      <div class="contact-container">
        <!-- Left Side - Contact Information -->
        <div class="contact-info">
          <div>
            <h3>Contact Information</h3>
            <p>
              Fill out the form and our team will get back to you within 24
              hours.
            </p>

            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi bi-telephone"></i>
              </div>
              <div class="contact-details">
                <a href="tel:+919876543210">+91 98765 43210</a>
              </div>
            </div>

            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi bi-envelope"></i>
              </div>
              <div class="contact-details">
                <a href="mailto:support@rngpit.ac.in">cebs.tech.team@gmail.com</a>
              </div>
            </div>

            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi bi-geo-alt"></i>
              </div>
              <div class="contact-details">
                <span> Bardoli – Navsari Road, </span> <br />
                <span> Isroli, Afwa, Bardoli, Gujarat 394350</span>
              </div>
            </div>

            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi bi-clock"></i>
              </div>
              <div class="contact-details">
                <span>Mon – Sat: 10:00 AM – 5:00 PM</span><br />
                <span>Sunday: Closed</span>
              </div>
            </div>
          </div>

          <div>
            <div class="social-links">
              <a href="#" class="social-link whatsapp" title="WhatsApp">
                <i class="bi bi-whatsapp"></i>
              </a>
              <a href="#" class="social-link facebook" title="Facebook">
                <i class="bi bi-facebook"></i>
              </a>
              <a href="#" class="social-link instagram" title="Instagram">
                <i class="bi bi-instagram"></i>
              </a>
              <a href="#" class="social-link twitter" title="Twitter">
                <i class="bi bi-twitter-x"></i>
              </a>
              <a href="#" class="social-link youtube" title="YouTube">
                <i class="bi bi-youtube"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- Right Side - Contact Form -->
        <div class="contact-form-wrapper">
          <form id="eventForm" class="needs-validation" method="post">
            <div class="form-row-custom">
              <div class="form-group">
                <label class="form-label-custom" for="firstName">First Name</label>
                <div class="input-icon-wrapper">
                  <i class="bi bi-person"></i>
                  <input
                    type="text"
                    class="form-control form-control-custom"
                    id="firstName"
                    name="firstname"
                    placeholder="First Name"
                    required />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label-custom" for="lastName">Last Name</label>
                <div class="input-icon-wrapper">
                  <i class="bi bi-person"></i>
                  <input
                    type="text"
                    class="form-control-custom"
                    id="lastName"
                    name="lastname"
                    placeholder="Last Name"
                    required />
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label-custom" for="email">Email Address</label>
              <div class="input-icon-wrapper">
                <i class="bi bi-envelope"></i>
                <input
                  type="email"
                  class="form-control-custom"
                  id="email"
                  name="email"
                  placeholder="Email Address"
                  required />
              </div>
            </div>

            <div class="form-group" style="margin-top: 35px">
              <label class="form-label-custom" for="message">Message</label>
              <textarea
                class="form-control-custom"
                id="message"
                name="message"
                placeholder="Write your message..."
                required></textarea>
            </div>

            <button type="submit" name="sendMessage" class="btn-submit-contact">
              Send Message
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- 🔹 Google Maps Section -->
  <section class="map-section reveal">
    <div class="container">
      <div class="map-header">
        <h2>Find Us Here</h2>
        <div class="contact-divider"></div>
        <p>Visit our campus and explore our state-of-the-art facilities</p>
      </div>

      <div class="map-container">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!!1d22000.5747892547867!2d73.1020333!3d21.0915624!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be0674c90ed97ef%3A0x525fd0e16a7025f3!2sR.%20N.%20G.%20Patel%20Institute%20of%20Technology!5e0!3m2!1sen!2sin!4v1713705570000!5m2!1sen!2sin"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>

        <div class="map-info">
          <h4>R.N.G Patel Institute of Technology</h4>
          <p>
            <i class="bi bi-geo-alt-fill me-2" style="color: #0ea5e9"></i>
            Bardoli-Navsari Road, Bardoli, Surat - 394601, Gujarat, India
          </p>
          <a
            href="https://www.google.com/maps/place/R.+N.+G.+Patel+Institute+of+Technology/@21.0915624,73.1020333,17z/data=!3m1!4b1!4m6!3m5!1s0x3be0674c90ed97ef:0x525fd0e16a7025f3!8m2!3d21.0915624!4d73.1046082!16s%2Fg%2F11b8083d2l?entry=ttu&g_ep=EgoyMDI2MDQyMC4wIKXMDSoASAFQAw%3D%3D"
            target="_blank"
            class="directions-btn">
            <i class="bi bi-arrow-right-circle"></i>
            Get Directions
          </a>
        </div>
      </div>
    </div>
  </section>

  <?php include "footer.php"; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Scripts -->
  <script>
    <?php if (isset($_GET["message"])): ?>
      window.onload = function() {
        setTimeout(function() {
          alert("<?php echo $_GET["message"]; ?>");
          window.history.replaceState(null, '', window.location.pathname);
        }, 100);
      };
    <?php endif; ?>

    // Scroll Reveal
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