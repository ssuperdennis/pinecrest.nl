<?php
// Start session for CSRF protection
session_start();
// Generate CSRF token for forms
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
// Form timestamp for bot detection
$formTimestamp = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PineCrest - Expert Project Management Consultancy based on Critical Chain Project Management. We step in when your project management is stuck.">
    <meta name="keywords" content="project management, critical chain, consultancy, PM advice, project recovery">
    <title>PineCrest | Critical Chain Project Management Consultancy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="assets/images/logo.png" alt="PineCrest - Critical Chain Project Management" class="logo-img">
            </a>
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="#home">Home</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#products">Products</a></li>
                <li><a href="#approach">Approach</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact" class="btn-primary">Contact Us</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-bg">
            <div class="gradient-overlay"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="slogan-container" id="sloganContainer">
                    <span class="slogan-text active">Momentum, Restored.</span>
                    <span class="slogan-text">Execution Without Excuses.</span>
                    <span class="slogan-text">Turning Complexity into Completion.</span>
                    <span class="slogan-text">From Uncertainty to Delivery.</span>
                    <span class="slogan-text">Projects Back on Track. Leaders Back in Control.</span>
                </div>
                <p class="hero-subtitle">Expert Critical Chain Project Management consultancy. We step in when projects stall, departments struggle, or deadlines loom.</p>
                <div class="hero-cta">
                    <a href="#contact" class="btn btn-large">Get Your Project Moving</a>
                    <a href="#services" class="btn btn-outline btn-large">Explore Services</a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-number">93%</div>
                        <div class="stat-label">On-Time Delivery Rate</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">165+</div>
                        <div class="stat-label">Projects Recovered</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Years Experience</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problem Statement -->
    <section class="problem-section">
        <div class="container">
            <div class="section-header">
                <h2>Sound Familiar?</h2>
                <p>These are the challenges our clients face before they reach out to us.</p>
            </div>
            <div class="problem-grid">
                <div class="problem-card">
                    <div class="problem-icon">⚠️</div>
                    <h3>Stalled Projects</h3>
                    <p>Critical projects that have lost momentum and seem impossible to get back on track.</p>
                </div>
                <div class="problem-card">
                    <div class="problem-icon">🔄</div>
                    <h3>Broken PM Departments</h3>
                    <p>Project management offices that have lost their way, with PMs working at cross-purposes.</p>
                </div>
                <div class="problem-card">
                    <div class="problem-icon">⏰</div>
                    <h3>Missed Deadlines</h3>
                    <p>Committed dates are slipping, stakeholders are losing confidence, and pressure is mounting.</p>
                </div>
                <div class="problem-card">
                    <div class="problem-icon">📊</div>
                    <h3>Resource Conflicts</h3>
                    <p>Teams overloaded, competing priorities, and no clear path to resolve bottlenecks.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-header">
                <h2>How We Help</h2>
                <p>Focused, practical intervention to get your projects and PM capability back on track.</p>
                <p class="pricing-note"><strong>Professional services at €165 per hour</strong> — transparent pricing for expert Critical Chain Project Management consultancy.</p>
            </div>
            <div class="services-grid">
                <div class="service-card featured">
                    <div class="service-badge">Most Common</div>
                    <div class="service-icon">🎯</div>
                    <h3>Project Recovery</h3>
                    <p>Immediate intervention for stuck projects. We assess, diagnose, and implement a recovery plan within days, not weeks.</p>
                    <ul class="service-features">
                        <li>Rapid assessment (2-5 days)</li>
                        <li>Critical path analysis</li>
                        <li>Buffer management setup</li>
                        <li>Stakeholder alignment</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-icon">🏢</div>
                    <h3>PM Department Transformation</h3>
                    <p>Rebuild your project management capability from the ground up using Critical Chain principles.</p>
                    <ul class="service-features">
                        <li>Current state assessment</li>
                        <li>Process redesign</li>
                        <li>Team coaching & mentoring</li>
                        <li>Performance metrics</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-icon">📋</div>
                    <h3>Critical Chain Implementation</h3>
                    <p>Implement Critical Chain Project Management in your organization for sustainable improvements.</p>
                    <ul class="service-features">
                        <li>CCPM methodology design</li>
                        <li>Buffer sizing strategies</li>
                        <li>Resource leveling</li>
                        <li>Dashboard setup</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-icon">👥</div>
                    <h3>Team Coaching</h3>
                    <p>Practical, hands-on coaching for project teams and PMs to build lasting capability.</p>
                    <ul class="service-features">
                        <li>One-on-one PM coaching</li>
                        <li>Team workshops</li>
                        <li>Real-time project guidance</li>
                        <li>Skills gap analysis</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-icon">🔍</div>
                    <h3>Project Audits</h3>
                    <p>Independent health checks on critical projects before they become problems.</p>
                    <ul class="service-features">
                        <li>Risk assessment</li>
                        <li>Schedule validation</li>
                        <li>Resource analysis</li>
                        <li>Recommendations report</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-icon">⚡</div>
                    <h3>Interim PM Leadership</h3>
                    <p>Stepping in as interim project or program manager while you find the right permanent leader.</p>
                    <ul class="service-features">
                        <li>Immediate coverage</li>
                        <li>Stakeholder management</li>
                        <li>Team stabilization</li>
                        <li>Transition support</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2>What Our Clients Say</h2>
                <p>Results from organizations that trusted PineCrest with their critical projects.</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"PineCrest transformed our struggling PMO. Within 8 weeks, we had clear visibility across all projects and a system that actually worked. Our delivery rate improved from 40% to over 90%."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">MV</div>
                        <div class="author-info">
                            <strong>Marco V.</strong>
                            <span>PMO Director, Government Agency</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card featured">
                    <div class="testimonial-badge">Project Recovery</div>
                    <div class="testimonial-content">
                        <p>"Our €2M infrastructure project was 6 months behind schedule. PineCrest diagnosed the issues in 3 days, implemented a recovery plan, and we delivered within the original timeline. Absolutely remarkable."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">SK</div>
                        <div class="author-info">
                            <strong>Sandra K.</strong>
                            <span>Program Manager, Transport Sector</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"The Critical Chain methodology was new to us, but PineCrest made it practical and actionable. Our team now delivers consistently, and the buffer management approach has been a game-changer."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">RJ</div>
                        <div class="author-info">
                            <strong>Robert J.</strong>
                            <span>Operations Director, Mid-sized Tech Company</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="products-section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Coming May 2026</span>
                <h2>ProjectFlow</h2>
                <p>Modern project management built on Critical Chain principles. Simplify how your team manages Projects, Programs & Portfolios.</p>
            </div>

            <!-- ProjectFlow Overview -->
            <div class="product-intro">
                <h3>Intelligent Project Management</h3>
                <p>ProjectFlow brings the power of Critical Chain Project Management (CCPM) to teams who need to deliver on time, every time. Built with modern technology — Django 5 and Python 3.12 — and available as open source.</p>

                <div class="product-key-points">
                    <div class="key-point">
                        <h4>Smart Scheduling</h4>
                        <p>Automatic critical chain identification with intelligent buffer management and penetration alerts that keep your projects on track.</p>
                    </div>
                    <div class="key-point">
                        <h4>Flow Analytics</h4>
                        <p>Real-time bottleneck detection and WIP optimization help you identify constraints before they impact delivery.</p>
                    </div>
                    <div class="key-point">
                        <h4>Resource Intelligence</h4>
                        <p>Workload visualization and capacity planning ensure your team operates at optimal efficiency without burnout.</p>
                    </div>
                </div>

                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">Release</span>
                        <span class="meta-value">May 2026</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Pricing</span>
                        <span class="meta-value">From €9 per user per month</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Technology</span>
                        <span class="meta-value">Django 5 • Python 3.12 • Open Source</span>
                    </div>
                </div>

                <div class="product-cta">
                    <a href="#beta-signup" class="btn btn-primary">Join Beta Waitlist</a>
                </div>
            </div>

            <!-- Capabilities -->
            <div class="product-capabilities">
                <h3>Capabilities</h3>
                <div class="capability-grid">
                    <div class="capability">
                        <h4>Critical Chain Scheduling</h4>
                        <p>Automatically identify and protect your critical path. Manage project, feeding, and capacity buffers with trend tracking and penetration alerts.</p>
                    </div>
                    <div class="capability">
                        <h4>Flow Analytics</h4>
                        <p>Monitor throughput, lead times, and flow efficiency. Detect bottlenecks early. Calculate optimal WIP limits using dosage control principles.</p>
                    </div>
                    <div class="capability">
                        <h4>Resource Management</h4>
                        <p>Visualize resource assignments across all projects. Monitor utilization and identify overload. Understand focus scores to reduce multitasking penalties.</p>
                    </div>
                    <div class="capability">
                        <h4>Portfolio Coordination</h4>
                        <p>Group projects into portfolios and programs. Standardize with templates. Track risks, issues, and rework separately. Guided onboarding included.</p>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="product-use-cases">
                <h3>Built For Real Teams</h3>
                <div class="use-case-list">
                    <div class="use-case-item">
                        <h4>Software Development Teams</h4>
                        <p>Sprint planning, bug tracking, and release coordination with flow-based metrics that actually reflect your reality.</p>
                    </div>
                    <div class="use-case-item">
                        <h4>Consulting Firms</h4>
                        <p>Track client engagements, resources, and billing across multiple concurrent projects with clear visibility.</p>
                    </div>
                    <div class="use-case-item">
                        <h4>Product Organizations</h4>
                        <p>Coordinate roadmaps across cross-functional teams with shared visibility and aligned priorities.</p>
                    </div>
                    <div class="use-case-item">
                        <h4>Agencies & Consultancies</h4>
                        <p>Manage client deadlines, capacity, and deliverables with confidence and predictability.</p>
                    </div>
                </div>
            </div>

            <!-- Beta Signup Form -->
            <div id="beta-signup" class="beta-signup-section">
                <div class="beta-content">
                    <h3>Join the Beta Waitlist</h3>
                    <p>Be among the first to experience ProjectFlow. You'll get early access, help shape product development, receive exclusive updates, and special launch pricing.</p>

                    <form id="betaSignupForm" class="beta-form" method="POST" action="beta-signup.php">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <!-- Form timestamp for bot detection -->
                        <input type="hidden" name="form_time" value="<?php echo $formTimestamp; ?>">
                        <!-- Honeypot fields for spam protection -->
                        <div style="position:absolute;left:-9999px;top:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
                            <label for="betaWebsite">Website (leave blank)</label>
                            <input type="text" id="betaWebsite" name="website" tabindex="-1" autocomplete="off"/>
                            <label for="betaUrl">URL</label>
                            <input type="text" id="betaUrl" name="url" tabindex="-1" autocomplete="off"/>
                            <label for="betaConfirmEmail">Confirm Email</label>
                            <input type="email" id="betaConfirmEmail" name="confirm_email" tabindex="-1" autocomplete="off"/>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="betaName">Name *</label>
                                <input type="text" id="betaName" name="name" required placeholder="Your name" autocomplete="name">
                            </div>
                            <div class="form-group">
                                <label for="betaEmail">Email *</label>
                                <input type="email" id="betaEmail" name="email" required placeholder="your.email@company.com" autocomplete="email">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="betaCompany">Company</label>
                                <input type="text" id="betaCompany" name="company" placeholder="Company name" autocomplete="organization">
                            </div>
                            <div class="form-group">
                                <label for="betaTeamSize">Team Size</label>
                                <select id="betaTeamSize" name="team_size">
                                    <option value="">Select team size</option>
                                    <option value="1-10">1-10 people</option>
                                    <option value="11-50">11-50 people</option>
                                    <option value="51-200">51-200 people</option>
                                    <option value="200+">200+ people</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="betaUseCase">Primary Use Case</label>
                            <select id="betaUseCase" name="use_case">
                                <option value="">Select your use case</option>
                                <option value="software">Software Development</option>
                                <option value="consulting">Consulting Firm</option>
                                <option value="product">Product Organization</option>
                                <option value="agency">Agency/Marketing</option>
                                <option value="government">Government/Public Sector</option>
                                <option value="education">Education/Research</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="privacy" required>
                                <span>I agree to receive updates about ProjectFlow and accept the <a href="privacy.php" target="_blank">Privacy Policy</a></span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-large btn-full">Join Beta Waitlist</button>
                    </form>
                    <div id="betaFormSuccess" class="form-success beta-success" style="display: none;">
                        <div class="success-icon">✓</div>
                        <h3>You're on the List!</h3>
                        <p>Thank you for your interest in ProjectFlow. We'll keep you updated on our progress and let you know when beta access is available.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Approach Section -->
    <section id="approach" class="approach-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Approach</h2>
                <p>Rooted in Critical Chain Project Management. Proven in real-world crises.</p>
            </div>
            <div class="approach-content">
                <div class="approach-text">
                    <h3>Why Critical Chain?</h3>
                    <p>Traditional project management fails because it doesn't account for reality: people aren't resources, estimates are optimistic, and everything takes longer than planned.</p>
                    <p>Critical Chain Project Management (CCPM) tackles these issues head-on by focusing on constraints, managing buffers intelligently, and protecting what really matters—project completion.</p>

                    <h4>The PineCrest Difference:</h4>
                    <ul class="approach-list">
                        <li><strong>Speed:</strong> We mobilize within 48 hours of engagement</li>
                        <li><strong>Clarity:</strong> No jargon, no fluff—just actionable insights</li>
                        <li><strong>Results:</strong> Measurable improvements in weeks, not months</li>
                        <li><strong>Transfer:</strong> We build capability, not dependency</li>
                        <li><strong>Flexibility:</strong> On-site or remote, tailored to your needs</li>
                    </ul>
                </div>
                <div class="approach-visual">
                    <div class="approach-diagram">
                        <div class="diagram-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Diagnose</h4>
                                <p>Understand the root cause</p>
                            </div>
                        </div>
                        <div class="diagram-arrow">↓</div>
                        <div class="diagram-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Plan</h4>
                                <p>Design the intervention</p>
                            </div>
                        </div>
                        <div class="diagram-arrow">↓</div>
                        <div class="diagram-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Execute</h4>
                                <p>Implement with your team</p>
                            </div>
                        </div>
                        <div class="diagram-arrow">↓</div>
                        <div class="diagram-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>Sustain</h4>
                                <p>Build lasting capability</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>Who's Behind PineCrest</h2>
                <p>Meet the expert driving your project recovery.</p>
            </div>
            <div class="about-content">
                <div class="about-image">
                    <div class="about-avatar">
                        <span>JP</span>
                    </div>
                </div>
                <div class="about-text">
                    <h3>Jeroen Pinas</h3>
                    <p class="about-title">Founder & Principal Consultant</p>
                    <p>With over 15 years of hands-on experience in project management across government, energy, transport, and technology sectors, I founded PineCrest to address a gap I saw repeatedly: organizations struggling not because they lacked talent, but because their project management approach was fundamentally misaligned with reality.</p>
                    <p>My approach combines deep expertise in Critical Chain Project Management with practical, no-nonsense intervention. I don't deliver reports that gather dust—I work alongside your team to diagnose problems, implement solutions, and build lasting capability.</p>
                    <div class="about-credentials">
                        <div class="credential">
                            <span class="credential-icon">🎓</span>
                            <span>Certified CCPM Practitioner</span>
                        </div>
                        <div class="credential">
                            <span class="credential-icon">🏛️</span>
                            <span>Government & Enterprise Experience</span>
                        </div>
                        <div class="credential">
                            <span class="credential-icon">🌍</span>
                            <span>Projects Across Europe</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Experience Section -->
    <section id="experience" class="experience-section">
        <div class="container">
            <div class="section-header">
                <h2>Experience Across Sectors</h2>
                <p>We've delivered results in diverse, complex environments.</p>
            </div>
            <div class="sectors-grid">
                <div class="sector-card">
                    <div class="sector-icon">🏛️</div>
                    <h3>Government</h3>
                    <p>Large international government organizations, navigating complex stakeholder landscapes and regulatory requirements.</p>
                </div>
                <div class="sector-card">
                    <div class="sector-icon">🏭</div>
                    <h3>Private Sector</h3>
                    <p>Small to mid-sized companies facing growth challenges and operational constraints.</p>
                </div>
                <div class="sector-card">
                    <div class="sector-icon">🎓</div>
                    <h3>Education</h3>
                    <p>Universities and research institutions balancing academic priorities with project delivery.</p>
                </div>
                <div class="sector-card">
                    <div class="sector-icon">🚆</div>
                    <h3>Transport</h3>
                    <p>Infrastructure and logistics projects where timing and coordination are critical.</p>
                </div>
                <div class="sector-card">
                    <div class="sector-icon">⚡</div>
                    <h3>Energy</h3>
                    <p>Power generation and renewable energy projects with complex technical dependencies.</p>
                </div>
            </div>
            <div class="experience-cta">
                <p><strong>Based in Wenum Wiesel, Netherlands. Operating across Europe and remotely worldwide.</strong></p>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="faq-section">
        <div class="container">
            <div class="section-header">
                <h2>Frequently Asked Questions</h2>
                <p>Answers to common questions about working with PineCrest.</p>
            </div>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>How quickly can you start?</h3>
                    <p>We typically mobilize within 48 hours of engagement. For urgent project recovery situations, same-day assessment calls can be arranged.</p>
                </div>
                <div class="faq-item">
                    <h3>What's the typical engagement length?</h3>
                    <p>Project recovery engagements usually run 4-12 weeks. PMO transformations and CCPM implementations typically take 3-6 months. We also offer ongoing advisory arrangements.</p>
                </div>
                <div class="faq-item">
                    <h3>Do you work remotely or on-site?</h3>
                    <p>Both. For European clients, on-site work is often most effective for initial assessments and team workshops. Remote support works well for ongoing advisory and monitoring. We tailor the approach to your needs.</p>
                </div>
                <div class="faq-item">
                    <h3>What makes CCPM different from traditional PM?</h3>
                    <p>Critical Chain focuses on managing uncertainty through buffers rather than padding individual tasks. It addresses the real causes of project delays: multitasking, student syndrome, and Parkinson's Law. The result: more realistic plans and better on-time delivery.</p>
                </div>
                <div class="faq-item">
                    <h3>What industries do you serve?</h3>
                    <p>We've delivered results in government, energy, transport, education, and private sector. The principles of CCPM apply across industries—the methodology adapts to your context.</p>
                </div>
                <div class="faq-item">
                    <h3>Is there a minimum engagement?</h3>
                    <p>Project audits can be completed in as little as 2-5 days. For recovery and transformation work, we recommend a minimum of 4 weeks to ensure sustainable results. No long-term contracts required.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-header">
                <h2>Let's Get Your Project Moving</h2>
                <p>Reach out today. We respond within 24 hours.</p>
            </div>
            <div class="contact-wrapper">
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <strong>Location</strong>
                            <p>Wenum Wiesel, The Netherlands</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">🌐</div>
                        <div>
                            <strong>Service Area</strong>
                            <p>Europe (on-site) | Worldwide (remote)</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">💶</div>
                        <div>
                            <strong>Rate</strong>
                            <p>€165 per hour</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">⏰</div>
                        <div>
                            <strong>Response Time</strong>
                            <p>Within 24 hours</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form-wrapper">
                    <form id="contactForm" class="contact-form" method="POST" action="send-mail.php">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <!-- Form timestamp for bot detection -->
                        <input type="hidden" name="form_time" value="<?php echo $formTimestamp; ?>">
                        <!-- Honeypot fields for spam protection -->
                        <div style="position:absolute;left:-9999px;top:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
                            <label for="website">Website (leave blank)</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off"/>
                            <label for="url">URL</label>
                            <input type="text" id="url" name="url" tabindex="-1" autocomplete="off"/>
                            <label for="confirm_email">Confirm Email</label>
                            <input type="email" id="confirm_email" name="confirm_email" tabindex="-1" autocomplete="off"/>
                        </div>

                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" id="name" name="name" required placeholder="Your name" autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required placeholder="your.email@company.com" autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="company">Company</label>
                            <input type="text" id="company" name="company" placeholder="Company name" autocomplete="organization">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" placeholder="+31 6 12345678" autocomplete="tel">
                        </div>
                        <div class="form-group">
                            <label for="service">Service of Interest *</label>
                            <select id="service" name="service" required>
                                <option value="">Select a service</option>
                                <option value="project-recovery">Project Recovery</option>
                                <option value="pm-transformation">PM Department Transformation</option>
                                <option value="ccpm-implementation">Critical Chain Implementation</option>
                                <option value="team-coaching">Team Coaching</option>
                                <option value="project-audit">Project Audit</option>
                                <option value="interim-pm">Interim PM Leadership</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="5" required placeholder="Tell us about your situation..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="privacy" required>
                                <span>I agree to the <a href="privacy.php" target="_blank">privacy policy</a> and processing of my personal data</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-full">Send Message</button>
                    </form>
                    <div id="formSuccess" class="form-success" style="display: none;">
                        <div class="success-icon">✓</div>
                        <h3>Message Sent!</h3>
                        <p>Thank you for reaching out. We'll get back to you within 24 hours.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="assets/images/logo-high-res.png" alt="PineCrest" class="footer-logo-img">
                    <p>Critical Chain Project Management Consultancy</p>
                </div>
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Services</h4>
                        <ul>
                            <li><a href="#services">Project Recovery</a></li>
                            <li><a href="#services">PM Transformation</a></li>
                            <li><a href="#services">CCPM Implementation</a></li>
                            <li><a href="#services">Team Coaching</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Products</h4>
                        <ul>
                            <li><a href="#products">ProjectFlow</a></li>
                            <li><a href="#beta-signup">Beta Waitlist</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Company</h4>
                        <ul>
                            <li><a href="#about">About</a></li>
                            <li><a href="#approach">Our Approach</a></li>
                            <li><a href="#experience">Experience</a></li>
                            <li><a href="#faq">FAQ</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h4>Contact</h4>
                        <ul>
                            <li>Wenum Wiesel, Netherlands</li>
                            <li><a href="mailto:info@pinecrest.nl">info@pinecrest.nl</a></li>
                            <li>Europe & Remote Worldwide</li>
                        </ul>
                        <div class="footer-social">
                            <a href="https://linkedin.com/company/pinecrest" target="_blank" rel="noopener" aria-label="LinkedIn" class="social-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 PineCrest. All rights reserved. | <a href="privacy.php">Privacy Policy</a></p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>

    <!-- Floating CTA -->
    <div class="floating-cta" id="floatingCta">
        <a href="#contact" class="btn btn-primary">
            <span>Free Consultation</span>
        </a>
    </div>
</body>
</html>
