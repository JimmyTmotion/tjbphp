<header class="main-header" id="mainHeader">
    <div class="header-upper">
        <div class="container">
            <div class="header-inner d-flex align-items-center">
                <div class="logo-outer">
                    <div class="logo">
                        <a href="index.php"><span class="TJBAccent">Tom J Butler</span></a>
                    </div>
                </div>

                <div class="nav-outer clearfix mx-auto">
                    <nav class="main-menu navbar-expand-lg">
                        <div class="navbar-header">
                            <div class="mobile-logo">
                                <a href="index.php"><span class="TJBAccent">Tom J Butler</span></a>
                            </div>
                            <button type="button" class="navbar-toggle" data-bs-toggle="collapse" data-bs-target=".navbar-collapse" aria-label="Toggle navigation">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                        </div>
                        <div class="navbar-collapse collapse">
                            <ul class="navigation onepage clearfix">
                                <li class="<?php echo $currentPage === 'home' ? 'active' : ''; ?>"><a href="index.php" class="nav-link-click">Home</a></li>
                                <li class="<?php echo $currentPage === 'skills-experience' ? 'active' : ''; ?>"><a href="skills-experience.php" class="nav-link-click">Skills &amp; Experience</a></li>
                                <li><a href="mailto:hello@tomjbutler.com" class="nav-link-click">Contact</a></li>
                            </ul>
                        </div>
                    </nav>
                </div>

                <div class="menu-btns">
                    <a href="mailto:hello@tomjbutler.com" class="theme-btn menu-btn-with-icon" aria-label="Hire me by email">
                        <svg class="menu-btn-icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm0 2v.4l9 6 9-6V7l-9 6-9-6Z" fill="currentColor"/>
                        </svg>
                        <span>Hire Me</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
