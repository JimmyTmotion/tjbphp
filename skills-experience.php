<?php
$pageTitle = 'Tom J Butler | Skills & Experience';
$currentPage = 'skills-experience';
include __DIR__ . '/includes/page-start.php';
?>

<section id="about" class="about-single-area innerpage-single-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 order-2">
                <div class="about-image-part">
                    <img src="assets/images/tjbanddani.jpg" alt="Tom J Butler">
                    <p class="about-image-caption">Tom &amp; Danielle, celebrating Pickleball tournament success</p>
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

<section class="skills-categories-area">
    <div class="container">
        <div class="container-inner">
            <div class="row">
                <div class="col-12">
                    <div class="section-title text-center">
                        <h2>Software and Skills</h2>
                    </div>
                </div>
            </div>
            <div class="skills-categories-grid">
                <article class="skills-category-card">
                    <h3>Creative Software</h3>
                     <hr />
                    <ul class="skills-category-list">
                        
                        <li>
                            <img src="assets/images/skills/skills5.png" alt="Adobe Creative Cloud icon" width="44" height="44" loading="lazy">
                            <span>Adobe Creative Cloud</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/skills3.png" alt="Adobe After Effects icon" width="44" height="44" loading="lazy">
                            <span>Adobe After Effects</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/skills4.png" alt="Adobe Premiere icon" width="44" height="44" loading="lazy">
                            <span>Adobe Premiere</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/skills7.png" alt="Adobe Character Animator icon" width="44" height="44" loading="lazy">
                            <span>Adobe Character Animator</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/skills1.png" alt="Adobe Photoshop icon" width="44" height="44" loading="lazy">
                            <span>Adobe Photoshop</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/skills2.png" alt="Adobe Illustrator icon" width="44" height="44" loading="lazy">
                            <span>Adobe Illustrator</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/skills8.png" alt="Adobe Media Encoder icon" width="44" height="44" loading="lazy">
                            <span>Adobe Media Encoder</span>
                        </li>
                        
                        <li>
                            <img src="assets/images/skills/skills9.png" alt="Blender icon" width="44" height="44" loading="lazy">
                            <span>Blender</span>
                        </li>
                    </ul>
                </article>
                <article class="skills-category-card">
                    <h3>AI Skills &amp; Toolkits</h3>
                     <hr />
                    <ul class="skills-category-list">
                        <li>
                            <img src="assets/images/skills/AI1.png" alt="Chat GPT and Codex icon" width="44" height="44" loading="lazy">
                            <span>Chat GPT &amp; Codex</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/AI2.png" alt="Claude Code icon" width="44" height="44" loading="lazy">
                            <span>Claude Code</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/AI3.png" alt="Google Gemini and Veo icon" width="44" height="44" loading="lazy">
                            <span>Google Gemini &amp; Veo</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/AI4.png" alt="Lovable icon" width="44" height="44" loading="lazy">
                            <span>Lovable</span>
                        </li>
                    </ul>
                </article>

                <article class="skills-category-card">
                    <h3>Coding Languages &amp; Frameworks</h3>
                    <hr />
                    <ul class="skills-category-list">
                        <li>
                            <img src="assets/images/skills/code1.png" alt="PHP icon" width="44" height="44" loading="lazy">
                            <span>Php</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/code2.png" alt="CSS icon" width="44" height="44" loading="lazy">
                            <span>CSS</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/code3.png" alt="HTML5 icon" width="44" height="44" loading="lazy">
                            <span>HTML5</span>
                        </li>
                        <li>
                            <img src="assets/images/skills/code4.png" alt="JavaScript icon" width="44" height="44" loading="lazy">
                            <span>Javascript</span>
                        </li>
                    </ul>
                </article>

                
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/page-end.php'; ?>
