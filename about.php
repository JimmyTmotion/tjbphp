<?php
$pageTitle = 'Tom J Butler | About';
$currentPage = 'about';
include __DIR__ . '/includes/page-start.php';
?>

<section id="about" class="about-single-area innerpage-single-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 order-2">
                <div class="about-image-part">
                    <img src="assets/images/tjbanddani.jpg" alt="Tom J Butler">
                </div>
            </div>
            <div class="col-lg-8 order-1">
                <div class="about-content-part">
                    <h2>Motion Designer and Animator</h2>
                    <p>I'm a digital media specialist based in Horsham, England, focused on motion design and animation.</p>
                    <p>With a background in video production, web development, and social media strategy, plus years of hands-on experience in animation and motion design, I bring a full-stack understanding to every project. This allows me to translate client briefs into technically sound, strategically aligned, high-quality results.</p>
                    <p>I've also grown and managed successful YouTube channels, X accounts, and TikTok profiles, combining creative direction with data-led strategy to build engaged audiences. Outside of work, I'm dedicated to training and compete at county level in pickleball, applying the same focus and competitive drive to everything I do.</p>
                    <div class="hero-btns">
                        <a href="mailto:hello@tomjbutler.com" class="about-contact-link" aria-label="Get in touch by email">
                            <svg class="about-contact-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm0 2v.4l9 6 9-6V7l-9 6-9-6Z" fill="currentColor"/>
                            </svg>
                            <span>Get In Touch</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="resume" class="resume-area">
    <div class="container">
        <div class="resume-items">
            <div class="row">
                <div class="col-xl-6 col-md-6">
                    <div class="single-resume">
                        <h2>Experience</h2>
                        <div class="experience-list">
                            <?php foreach ($experience as $item): ?>
                                <div class="resume-item">
                                    <div class="content">
                                        <span class="years"><?php echo htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <h4><?php echo htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <span class="company"><?php echo htmlspecialchars($item[2], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-md-6">
                    <div class="experience-list">
                        <div class="single-resume">
                            <h2>Education</h2>
                            <?php foreach ($education as $item): ?>
                                <div class="resume-item">
                                    <div class="content">
                                        <span class="years"><?php echo htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <h4><?php echo htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <span class="company"><?php echo htmlspecialchars($item[2], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="testimonials-area">
    <div class="container">
        <div class="container-inner">
            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="section-title text-center">
                        <h2>What clients and colleagues say!</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="testimonial-slider" data-slider>
                        <?php foreach ($testimonials as $index => $testimonial): ?>
                            <article class="testimonial-slide <?php echo $index === 0 ? 'is-active' : ''; ?>" data-slide>
                                <div class="testimonial-item">
                                    <div class="text" style="white-space: pre-line;"><?php echo htmlspecialchars($testimonial['review'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="testi-meta">
                                        <div class="author">
                                            <img src="<?php echo htmlspecialchars($testimonial['src'], ENT_QUOTES, 'UTF-8'); ?>" width="60" height="60" alt="<?php echo htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="testi-des">
                                            <h5><?php echo htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                            <span><?php echo htmlspecialchars($testimonial['position'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="slider-arrows text-center pt-40">
                        <button class="testimonial-prev arrow" type="button" data-testimonial-prev aria-label="Previous testimonial">&lt;</button>
                        <button class="testimonial-next arrow" type="button" data-testimonial-next aria-label="Next testimonial">&gt;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/page-end.php'; ?>
