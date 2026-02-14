<?php
$pageTitle = 'Tom J Butler | Home';
$currentPage = 'home';
include __DIR__ . '/includes/page-start.php';
?>

<section id="about" class="about-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="about-image-part">
                    <img src="assets/images/tjb2.png" alt="Tom J Butler" style="margin-bottom: 15px;">
                    <p>Motion Designer and Digital Media Specialist based in Horsham, England</p>
                    <div class="about-social text-center">
                        <ul>
                            <li><a href="https://www.linkedin.com/in/tom-butler-290b3237/" target="_blank" rel="noopener noreferrer">LinkedIn</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="about-content-part">
                    <p>Hello There!</p>
                    <h3>
                        I'm Tom, a motion designer, animator, video editor, web developer and digital media specialist with a passion for creating <span class="TJBAccent"><b>visually stunning</b></span> and engaging content.
                    </h3>
                    <div class="adress-field">
                        <ul>
                            <li class="d-flex align-items-center"><i>&#9679;</i> Available for Freelancing</li>
                        </ul>
                    </div>
                    <div class="hero-btns">
                        <a href="assets/cv/CV%20-%20Tom%20Butler%20-%20Feb%202026.pdf" class="about-contact-link" target="_blank" rel="noopener noreferrer" aria-label="Download CV">
                            <svg class="about-contact-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M12 3a1 1 0 0 1 1 1v8.59l2.3-2.3a1 1 0 1 1 1.4 1.42l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.42l2.3 2.3V4a1 1 0 0 1 1-1Zm-7 14a1 1 0 0 1 1 1v1h12v-1a1 1 0 1 1 2 0v2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-2a1 1 0 0 1 1-1Z" fill="currentColor"/>
                            </svg>
                            <span>Download CV</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="showreel" class="projects-area">
    <div class="container">
        <div class="container-inner">
            <div class="row">
                <div class="col-12">
                    <div class="section-title text-center">
                        <h2>Showreel</h2>
                        <p>Check out some of my work for clients around the globe, full videos and case studies available on request</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <video controls preload="metadata" style="width: 100%; height: auto; border-radius: 12px;">
                        <source src="https://pub-f56582404f814e389416da6105f21ddb.r2.dev/tjb-showreel-04.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="skills" class="projects-area">
    <div class="container">
        <div class="container-inner">
            <div class="row">
                <div class="col-12">
                    <div>
                        <h2>Software and frameworks I specialise in:</h2>
                    </div>
                    <div class="company-list">
                        <div class="skills-marquee">
                            <div class="skills-marquee-track">
                                <?php foreach ($skillsLogos as $logo): ?>
                                    <img src="assets/images/client-logos/<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="Skill logo">
                                <?php endforeach; ?>
                                <?php foreach ($skillsLogos as $logo): ?>
                                    <img src="assets/images/client-logos/<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="Skill logo">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/page-end.php'; ?>
